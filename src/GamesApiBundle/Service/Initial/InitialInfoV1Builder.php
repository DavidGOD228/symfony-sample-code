<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Initial;

use Acme\SymfonyDb\Entity\Currency;
use CoreBundle\Exception\ValidationException;
use CoreBundle\Repository\CurrencyRepository;
use CoreBundle\Service\DomainParameterProvider;
use CoreBundle\Service\GeoIpService;
use CoreBundle\Service\LanguageService;
use CoreBundle\Service\NodeService;
use CoreBundle\Service\PartnerService;
use CoreBundle\Service\RepositoryProviderInterface;
use CoreBundle\Service\SubscriptionService;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use GamesApiBundle\DataObject\CurrencyDto;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoTaxes;
use GamesApiBundle\DataObject\InitialInfo\PartnerInitialInfoResponse;
use GamesApiBundle\Repository\PartnerLogoLinkRepository;
use GamesApiBundle\Request\PartnerInitialStateRequest;
use GamesApiBundle\Service\Initial\Component\CurrencyButtonAmountService;
use GamesApiBundle\Service\Initial\Component\FilteredEnabledGamesProvider;
use GamesApiBundle\Service\Initial\Component\InitialGamesBuilder;
use GamesApiBundle\Service\Initial\Component\InitialPlayerBuilder;
use GamesApiBundle\Service\Initial\Component\InitialPromotionBuilder;
use GamesApiBundle\Service\PlayerService;
use GamesApiBundle\Service\TaxService;

/**
 * Class InitialInfoV1Builder
 *
 * @deprecated frontend is migrating to initial API v3 - avoid modifying any initial API v2 code if possible
 */
final class InitialInfoV1Builder
{
    private CurrencyButtonAmountService $currencyButtonAmountService;
    private PartnerService $partnerService;
    private EntityRepository $currencyRepository;
    private FilteredEnabledGamesProvider $enabledGamesProvider;
    private PlayerService $playerService;
    private EntityRepository $partnerLogoLinkRepository;
    private TaxService $taxesService;
    private LanguageService $languageService;
    private GeoIpService $geoIpService;
    private NodeService $nodeService;
    private DomainParameterProvider $parameterProvider;

    private InitialPlayerBuilder $initialPlayerBuilder;
    private InitialPromotionBuilder $initialPromotionBuilder;
    private InitialGamesBuilder $initialGamesBuilder;

    /**
     * @param RepositoryProviderInterface $repoProvider
     * @param CurrencyButtonAmountService $currencyButtonAmountService
     * @param PartnerService $partnerService
     * @param FilteredEnabledGamesProvider $enabledGamesProvider
     * @param PlayerService $playerService
     * @param TaxService $taxesService
     * @param LanguageService $languageService
     * @param InitialPromotionBuilder $initialPromotionBuilder
     * @param GeoIpService $geoIpService
     * @param NodeService $nodeService
     * @param DomainParameterProvider $parameterProvider
     * @param InitialGamesBuilder $initialGamesBuilder
     * @param InitialPlayerBuilder $initialPlayerBuilder
     */
    public function __construct(
        RepositoryProviderInterface $repoProvider,
        CurrencyButtonAmountService $currencyButtonAmountService,
        PartnerService $partnerService,
        FilteredEnabledGamesProvider $enabledGamesProvider,
        PlayerService $playerService,
        TaxService $taxesService,
        LanguageService $languageService,
        InitialPromotionBuilder $initialPromotionBuilder,
        GeoIpService $geoIpService,
        NodeService $nodeService,
        DomainParameterProvider $parameterProvider,
        InitialGamesBuilder $initialGamesBuilder,
        InitialPlayerBuilder $initialPlayerBuilder
    )
    {
        $this->currencyButtonAmountService = $currencyButtonAmountService;
        $this->partnerService = $partnerService;
        $this->enabledGamesProvider = $enabledGamesProvider;
        $this->playerService = $playerService;
        $this->taxesService = $taxesService;
        $this->languageService = $languageService;
        $this->initialPromotionBuilder = $initialPromotionBuilder;
        $this->geoIpService = $geoIpService;
        $this->nodeService = $nodeService;

        $this->currencyRepository = $repoProvider->getSlaveRepository(CurrencyRepository::class);
        $this->partnerLogoLinkRepository = $repoProvider->getSlaveRepository(PartnerLogoLinkRepository::class);

        $this->parameterProvider = $parameterProvider;

        $this->initialPlayerBuilder = $initialPlayerBuilder;
        $this->initialGamesBuilder = $initialGamesBuilder;
    }

    /**
     * @param PartnerInitialStateRequest $request
     * @param string $partnerCode
     *
     * @return PartnerInitialInfoResponse
     *
     * @throws ValidationException
     * @throws NonUniqueResultException
     */
    public function getPartnerInitialInfo(
        PartnerInitialStateRequest $request,
        string $partnerCode
    ): PartnerInitialInfoResponse
    {
        $partner = $this->partnerService->getPartnerByPartnerApiCodeStrict($partnerCode);

        /** @var Currency $topWonAmountsCurrency */
        $topWonAmountsCurrency = $this->currencyRepository->getById($partner->getCurrencyLastDraws());

        $partnerLogoLink = $this->partnerLogoLinkRepository->getIframeLogoLink($partner);
        $videoLogoUrl = '';
        if ($partnerLogoLink) {
            $videoLogoUrl = $partnerLogoLink->getLogo()->getUrl();
        }

        $player = $this->playerService->getOptionalPlayerFromSession();
        if ($player) {
            $playerId = $player->getId();
            $playerDTO = $this->initialPlayerBuilder->build($player, $request->getIosAppVersion());
            $currency = $player->getCurrency();
        } else {
            $playerId = null;
            $playerDTO = null;
            $currency = $partner->getCurrency();
        }

        $currencyButtonAmounts = $this->currencyButtonAmountService->getBetAmounts($currency);

        $shouldReinitSession = $this->playerService->shouldReinitSession($partner);

        $languageId = $request->getLanguageId();
        $language = $this->languageService->getLanguageByIdOrDefault($languageId);

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

        $promotionData = $this->initialPromotionBuilder->build($partner, $currency);

        $isGeoBlocked = $this->geoIpService->isBlocked($request->getIp(), $partner);

        $sockets = [
            'url' => $this->nodeService->getCurrentWebsocketUrl(),
            // The player ID field in token is not nullable, but an ID of 0 works just fine
            'token' => $this->nodeService->getNodeSocketToken($playerId ?? 0, $partner)
        ];

        $gaCode = $this->parameterProvider->getGaCode($request->getDomain());

        $initialInfo = new PartnerInitialInfoResponse(
            $currencyButtonAmounts,
            $partner,
            $language->isRtl(),
            $partner->getSubscriptionEnabled() ? SubscriptionService::ALLOWED_OPTIONS : [],
            $shouldReinitSession,
            $videoLogoUrl,
            $playerDTO,
            new CurrencyDto($currency),
            new CurrencyDto($topWonAmountsCurrency),
            $promotionData,
            $taxes,
            $games,
            $isGeoBlocked,
            $sockets,
            $gaCode
        );

        return $initialInfo;
    }
}
