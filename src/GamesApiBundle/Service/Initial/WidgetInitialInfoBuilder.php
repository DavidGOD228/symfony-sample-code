<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Initial;

use CoreBundle\Exception\ValidationException;
use CoreBundle\Service\GeoIpService;
use CoreBundle\Service\NodeService;
use CoreBundle\Service\PartnerService;
use GamesApiBundle\DataObject\CurrencyDto;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoTaxes;
use GamesApiBundle\DataObject\InitialInfo\PartnerInitialWidgetInfoResponse;
use GamesApiBundle\Request\PartnerInitialStateRequest;
use GamesApiBundle\Service\Initial\Component\CurrencyButtonAmountService;
use GamesApiBundle\Service\Initial\Component\FilteredEnabledGamesProvider;
use GamesApiBundle\Service\Initial\Component\InitialGamesBuilder;
use GamesApiBundle\Service\PlayerService;
use GamesApiBundle\Service\TaxService;

/**
 * Class WidgetInitialInfoBuilder
 */
final class WidgetInitialInfoBuilder
{
    private CurrencyButtonAmountService $currencyButtonAmountService;
    private PartnerService $partnerService;
    private FilteredEnabledGamesProvider $enabledGamesProvider;
    private PlayerService $playerService;
    private TaxService $taxesService;
    private GeoIpService $geoIpService;
    private NodeService $nodeService;

    private InitialGamesBuilder $initialGamesBuilder;

    /**
     * @param CurrencyButtonAmountService $currencyButtonAmountService
     * @param PartnerService $partnerService
     * @param FilteredEnabledGamesProvider $enabledGamesProvider
     * @param PlayerService $playerService
     * @param TaxService $taxesService
     * @param GeoIpService $geoIpService
     * @param NodeService $nodeService
     * @param InitialGamesBuilder $initialGamesBuilder
     */
    public function __construct(
        CurrencyButtonAmountService $currencyButtonAmountService,
        PartnerService $partnerService,
        FilteredEnabledGamesProvider $enabledGamesProvider,
        PlayerService $playerService,
        TaxService $taxesService,
        GeoIpService $geoIpService,
        NodeService $nodeService,
        InitialGamesBuilder $initialGamesBuilder
    )
    {
        $this->currencyButtonAmountService = $currencyButtonAmountService;
        $this->partnerService = $partnerService;
        $this->enabledGamesProvider = $enabledGamesProvider;
        $this->playerService = $playerService;
        $this->taxesService = $taxesService;
        $this->geoIpService = $geoIpService;
        $this->nodeService = $nodeService;

        $this->initialGamesBuilder = $initialGamesBuilder;
    }

    /**
     * @param PartnerInitialStateRequest $request
     * @param string $partnerCode
     *
     * @return PartnerInitialWidgetInfoResponse
     *
     * @throws ValidationException
     */
    public function getPartnerInitialWidgetInfo(
        PartnerInitialStateRequest $request,
        string $partnerCode
    ): PartnerInitialWidgetInfoResponse
    {
        $partner = $this->partnerService->getPartnerByPartnerApiCodeStrict($partnerCode);

        $player = $this->playerService->getOptionalPlayerFromSession();
        if ($player) {
            $playerId = $player->getId();
            $currency = $player->getCurrency();
        } else {
            $currency = $partner->getCurrency();
            $playerId = null;
        }

        $currencyButtonAmounts = $this->currencyButtonAmountService->getBetAmounts($currency);

        $enabledGames = $this->enabledGamesProvider->getFilteredEnabledGames(
            $partner,
            $request->getIosAppVersion()
        );
        $taxSchema = $this->taxesService->getTaxDto($partner->getTaxScheme());
        $taxes = new PartnerInitialInfoTaxes($taxSchema, $partner);
        $games = $this->initialGamesBuilder->build(
            $partner,
            $enabledGames,
            $player,
            $request->getIp()
        );

        $isGeoBlocked = $this->geoIpService->isBlocked($request->getIp(), $partner);

        $sockets = [
            'url' => $this->nodeService->getCurrentWebsocketUrl(),
            // The player ID field in token is not nullable, but an ID of 0 works just fine
            'token' => $this->nodeService->getNodeSocketToken($playerId ?? 0, $partner)
        ];

        $initialInfo = new PartnerInitialWidgetInfoResponse(
            $currencyButtonAmounts,
            $partner,
            $playerId,
            new CurrencyDto($currency),
            $taxes,
            $games,
            $isGeoBlocked,
            $sockets
        );

        return $initialInfo;
    }
}