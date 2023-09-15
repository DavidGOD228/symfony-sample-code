<?php

namespace GamesApiBundle\Service;

use Acme\ApiEmulation\Provider\Acme\FreePlayPlayerProvider;
use Acme\SymfonyDb\Entity\Language;
use Acme\WebApi\Feature\AnonymousPlayersApiInterface;
use Acme\WebApi\Feature\FeatureChecker;
use Acme\WebApi\WebApiInterface;
use Carbon\CarbonImmutable;
use CoreBundle\Service\GeoIpService;
use CoreBundle\Service\RepositoryProviderInterface;
use GamesApiBundle\Exception\AnonymousAuthenticationException;
use GamesApiBundle\Request\AuthParams;
use Acme\SymfonyDb\Entity\Player;
use CoreBundle\Repository\CurrencyRepository;
use CoreBundle\Service\PartnerService;
use CoreBundle\Service\LanguageService;
use CoreBundle\Repository\PlayerRepository;
use CoreBundle\Session\UserSessionInterface;
use PartnerApiBundle\Service\PartnerWebApiProvider;
use GamesApiBundle\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

/**
 * Class AuthService
 */
class AuthService
{
    private UserSessionInterface $session;
    private PartnerWebApiProvider $partnerWebApiProvider;
    private PartnerService $partnerService;
    private LanguageService $languageService;
    private GeoIpService $geoIpService;
    private CurrencyRepository $currencyRepository;
    private PlayerRepository $playerRepository;
    private LoggerInterface $logger;

    /**
     * AuthService constructor.
     *
     * @param UserSessionInterface $session
     * @param PartnerService $partnerService
     * @param LanguageService $languageService
     * @param PartnerWebApiProvider $partnerWebApiProvider
     * @param GeoIpService $geoIpService
     * @param RepositoryProviderInterface $repositoryProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        UserSessionInterface $session,
        PartnerService $partnerService,
        LanguageService $languageService,
        PartnerWebApiProvider $partnerWebApiProvider,
        GeoIpService $geoIpService,
        RepositoryProviderInterface $repositoryProvider,
        LoggerInterface $logger
    )
    {
        $this->session = $session;
        $this->partnerWebApiProvider = $partnerWebApiProvider;
        $this->partnerService = $partnerService;
        $this->geoIpService = $geoIpService;
        $this->languageService = $languageService;
        $this->currencyRepository = $repositoryProvider->getMasterRepository(CurrencyRepository::class);
        $this->playerRepository = $repositoryProvider->getMasterRepository(PlayerRepository::class);
        $this->logger = $logger;
    }

    /**
     * @param AuthParams $params
     * @param string $ip
     *
     * @return Player
     *
     * @throws AuthenticationException
     * @throws AnonymousAuthenticationException
     */
    public function login(AuthParams $params, string $ip): Player
    {
        if ($params->getSid()) {
            $this->session->setSessionId($params->getSid());
        }
        $language = $this->languageService->getLanguageByCode($params->getLanguage());
        if (!$language) {
            $language = $this->languageService->getDefaultLanguage();
        }

        $this->setProvidedUserData($language, $params);

        $partner = $this->partnerService->getPartnerByPartnerApiCode($params->getPartnerCode());
        if (!$partner) {
            throw new AuthenticationException('game_is_turned_off_contact_administrator', Response::HTTP_NOT_FOUND);
        }

        $isGeoBlocked = $this->geoIpService->isBlocked($ip, $partner);
        if ($isGeoBlocked) {
            throw new AuthenticationException('GEO_BLOCKING', Response::HTTP_FORBIDDEN);
        }

        $isFreePlay = $this->session->isFreePlay();

        if ($isFreePlay) {
            $rawToken =  FreePlayPlayerProvider::FREE_PLAY_INITIAL_TOKEN;
        } else {
            $rawToken = $params->getToken() ?: '';
        }

        /** @var AnonymousPlayersApiInterface|WebApiInterface $api */
        $api = $this->partnerWebApiProvider->getPartnerApi($partner, $isFreePlay);

        if (FeatureChecker::supportsAnonymousPlayers($api->getType()) && $api->isAnonymousToken($rawToken)) {
            throw new AnonymousAuthenticationException('ANONYMOUS_TOKEN', Response::HTTP_FORBIDDEN);
        }

        $token = $api->requestNewToken($rawToken);

        if (!$token) {
            throw new AuthenticationException('please_login', Response::HTTP_NOT_FOUND);
        }

        $accountDetails = $api->getAccountDetails($token);
        if (!$accountDetails) {
            throw new AuthenticationException('please_login', Response::HTTP_UNAUTHORIZED);
        }

        if (!$accountDetails->getCurrency()) {
            throw new AuthenticationException('cant_login', Response::HTTP_NOT_FOUND);
        }

        $currency = $this->currencyRepository->getByCode($accountDetails->getCurrency());
        if (!$currency) {
            throw new AuthenticationException(
                'cant_login',
                Response::HTTP_NOT_FOUND
            );
        }

        if (!$currency->isEnabled()) {
            throw new AuthenticationException(
                'cant_login',
                Response::HTTP_NOT_FOUND
            );
        }

        $externalCode = $accountDetails->getUserId();

        /** @var Player $player **/
        $player = $this->playerRepository->findByExternalCode($partner, $externalCode);

        if (!$player) {
            $player = (new Player())
                ->setPartner($partner)
                ->setIsShop(false)
                ->setIsTest(false)
                ->setIsFreePlay($isFreePlay)
                ->setExternalCode($externalCode)
                ->setCreatedAt(CarbonImmutable::now())
                ->setTag(Player::PLAYER_TAG_NEW)
                ->setTaggedAt(CarbonImmutable::now());
        } elseif ($player->getCurrency() !== $currency) {
            $this->logger->warning(
                sprintf(
                    'Currency was changed from=%s to=%s for player=%s',
                    $player->getCurrency()->getCode(),
                    $currency->getCode(),
                    $player->getId(),
                )
            );
        }

        // Updating token for player, so we could find it in PlayerService.
        $player->setExternalToken($token);
        // Updating currency because player could change currency on partner side.
        $player->setCurrency($currency);

        $player = $this->playerRepository->save($player);

        $this->setApiUserData($token, $player);

        return $player;
    }

    /**
     * @param string $token
     * @param Player $player
     */
    private function setApiUserData(string $token, Player $player): void
    {
        $this->session->setApiUserData(
            $token,
            $player->getPartner()->getId(),
            $player->getId(),
            $player->getCurrency()->getId()
        );
    }

    /**
     * @param Language $language
     * @param AuthParams $params
     */
    private function setProvidedUserData(Language $language, AuthParams $params): void
    {
        $this->session->setProvidedUserData(
            $language->getId(),
            $params->getTimezone(),
            $params->getIsMobile(),
            $params->getOddsFormat()
        );
    }

    /**
     * Clears api user data from session.
     */
    public function clearApiUserData(): void
    {
        $this->session->clearApiUserData();
    }
}
