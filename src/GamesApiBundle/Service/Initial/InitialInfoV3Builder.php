<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Initial;

use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\PartnerEnabledGame;
use CoreBundle\Exception\ValidationException;
use CoreBundle\Repository\CurrencyRepository;
use CoreBundle\Service\DomainParameterProvider;
use CoreBundle\Service\GameService;
use CoreBundle\Service\GeoIpService;
use CoreBundle\Service\NodeService;
use CoreBundle\Service\PartnerService;
use CoreBundle\Service\RepositoryProviderInterface;
use CoreBundle\Service\SubscriptionService;
use Doctrine\ORM\EntityRepository;
use GamesApiBundle\DataObject\CurrencyDto;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoGameV3;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoTaxes;
use GamesApiBundle\DataObject\InitialInfo\PartnerInitialInfoV3Response;
use GamesApiBundle\Repository\PartnerLogoLinkRepository;
use GamesApiBundle\Request\PartnerInitialStateRequest;
use GamesApiBundle\Service\Initial\Component\CurrencyButtonAmountService;
use GamesApiBundle\Service\Initial\Component\InitialGamesBuilderV3;
use GamesApiBundle\Service\PlayerService;
use GamesApiBundle\Service\TaxService;

/**
 * Class PartnerInitialInfoBuilder
 */
final class InitialInfoV3Builder
{
    private CurrencyButtonAmountService $currencyButtonAmountService;
    private PartnerService $partnerService;
    private EntityRepository $currencyRepository;
    private GameService $gameService;
    private PlayerService $playerService;
    private EntityRepository $partnerLogoLinkRepository;
    private TaxService $taxesService;
    private GeoIpService $geoIpService;
    private NodeService $nodeService;
    private DomainParameterProvider $parameterProvider;
    private InitialGamesBuilderV3 $initialGamesBuilder;

    /**
     * @param RepositoryProviderInterface $repoProvider
     * @param CurrencyButtonAmountService $currencyButtonAmountService
     * @param PartnerService $partnerService
     * @param GameService $gameService
     * @param PlayerService $playerService
     * @param TaxService $taxesService
     * @param GeoIpService $geoIpService
     * @param NodeService $nodeService
     * @param DomainParameterProvider $parameterProvider
     * @param InitialGamesBuilderV3 $initialGamesBuilder
     */
    public function __construct(
        RepositoryProviderInterface $repoProvider,
        CurrencyButtonAmountService $currencyButtonAmountService,
        PartnerService $partnerService,
        GameService $gameService,
        PlayerService $playerService,
        TaxService $taxesService,
        GeoIpService $geoIpService,
        NodeService $nodeService,
        DomainParameterProvider $parameterProvider,
        Component\InitialGamesBuilderV3 $initialGamesBuilder
    )
    {
        $this->currencyButtonAmountService = $currencyButtonAmountService;
        $this->partnerService = $partnerService;
        $this->gameService = $gameService;
        $this->playerService = $playerService;
        $this->taxesService = $taxesService;
        $this->geoIpService = $geoIpService;
        $this->nodeService = $nodeService;

        $this->currencyRepository = $repoProvider->getSlaveRepository(CurrencyRepository::class);
        $this->partnerLogoLinkRepository = $repoProvider->getSlaveRepository(PartnerLogoLinkRepository::class);

        $this->parameterProvider = $parameterProvider;
        $this->initialGamesBuilder = $initialGamesBuilder;
    }

    /**
     * @param PartnerInitialStateRequest $request
     * @param string $partnerCode
     *
     * @return PartnerInitialInfoV3Response
     *
     * @throws ValidationException
     */
    public function getPartnerInitialInfoV3(
        PartnerInitialStateRequest $request,
        string $partnerCode
    ): PartnerInitialInfoV3Response
    {
        $partner = $this->partnerService->getPartnerByPartnerApiCodeStrict($partnerCode);

        /** @var Currency $topWonAmountsCurrency */
        $topWonAmountsCurrency = $this->currencyRepository->getById($partner->getCurrencyLastDraws());

        $partnerLogoLink = $this->partnerLogoLinkRepository->getIframeLogoLink($partner);
        $videoLogoUrl = '';
        if ($partnerLogoLink) {
            $videoLogoUrl = $partnerLogoLink->getLogo()->getUrl();
        }

        $currency = $partner->getCurrency();
        $currencyButtonAmounts = $this->currencyButtonAmountService->getBetAmounts($currency);

        $shouldReinitSession = $this->playerService->shouldReinitSession($partner);
        $orderedGameInfos = $this->buildOrderedGames($partner, $request->getIp());
        $taxSchema = $this->taxesService->getTaxDto($partner->getTaxScheme());
        $taxes = new PartnerInitialInfoTaxes($taxSchema, $partner);
        $isGeoBlocked = $this->geoIpService->isBlocked($request->getIp(), $partner);

        $gaCode = $this->parameterProvider->getGaCode($request->getDomain());

        $initialInfo = new PartnerInitialInfoV3Response(
            $currencyButtonAmounts,
            $partner,
            $partner->getSubscriptionEnabled() ? SubscriptionService::ALLOWED_OPTIONS : [],
            $shouldReinitSession,
            $orderedGameInfos,
            $videoLogoUrl,
            new CurrencyDto($currency),
            new CurrencyDto($topWonAmountsCurrency),
            $taxes,
            $isGeoBlocked,
            $this->nodeService->getCurrentWebsocketUrl(),
            $gaCode
        );

        return $initialInfo;
    }

    /**
     * @param Partner $partner
     * @param string $playerIp
     *
     * @return array|PartnerInitialInfoGameV3[]
     */
    private function buildOrderedGames(Partner $partner, string $playerIp): array
    {
        $enabledGames = $this->gameService->getPartnerEnabledGames($partner);

        $gameInfosByGameId = $this->initialGamesBuilder->build($enabledGames, $playerIp);

        $orderedGameInfos = [];
        $gameOrderMap = $this->getGameOrderMap($partner, $enabledGames);
        foreach ($gameOrderMap as $gameId => $sortOrder) {
            $orderedGameInfos[$sortOrder] = $gameInfosByGameId[$gameId];
        }

        ksort($orderedGameInfos);

        return array_values($orderedGameInfos);
    }

    /**
     * @param Partner $partner
     * @param PartnerEnabledGame[] $enabledGames
     *
     * @return array<int,int> gameId => order
     */
    private function getGameOrderMap(Partner $partner, iterable $enabledGames): array
    {
        $orderMap = [];

        if ($partner->isCustomGameOrderEnabled()) {
            foreach ($enabledGames as $enabledGame) {
                $orderMap[$enabledGame->getGame()->getId()] = $enabledGame->getOrder();
            }

            return $orderMap;
        }

        foreach ($enabledGames as $enabledGame) {
            $game = $enabledGame->getGame();
            $orderMap[$game->getId()] = $game->getOrder();
        }

        return $orderMap;
    }
}
