<?php

namespace GamesApiBundle\Service;

use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\Player;
use Acme\WebApi\Enum\WebApiType;
use Acme\WebApi\Feature\FeatureChecker;
use Acme\WebApi\Feature\ReinitSessionApiInterface;
use Acme\WebApi\WebApiInterface;
use CoreBundle\Exception\MissingSessionKeyException;
use CoreBundle\Repository\PlayerRepository;
use CoreBundle\Service\CacheServiceInterface;
use CoreBundle\Service\GeoIpService;
use CoreBundle\Service\RepositoryProviderInterface;
use CoreBundle\Session\UserSessionInterface;
use PartnerApiBundle\Service\PartnerWebApiProvider;

/**
 * Class PlayerService
 */
class PlayerService
{
    private const CACHE_KEY_PLAYER_TOKEN_VALIDATED = 'player:token_validated:';
    /**
     * Preventing often calls to partner API.
     * 15 sec should be enough, no one API have token TTL < 5 minutes.
     */
    private const CACHE_TTL_TOKEN_VALIDATED = 15;

    private PlayerRepository $masterPlayerRepository;
    private CacheServiceInterface $cacheService;
    private PartnerWebApiProvider $partnerWebApiProvider;
    private UserSessionInterface $session;
    private GeoIpService $geoIpService;

    /**
     * PlayerService constructor.
     *
     * @param RepositoryProviderInterface $repositoryProvider
     * @param PartnerWebApiProvider $partnerWebApiProvider
     * @param CacheServiceInterface $cacheService
     * @param UserSessionInterface $session
     * @param GeoIpService $geoIpService
     */
    public function __construct(
        RepositoryProviderInterface $repositoryProvider,
        PartnerWebApiProvider $partnerWebApiProvider,
        CacheServiceInterface $cacheService,
        UserSessionInterface $session,
        GeoIpService $geoIpService
    )
    {
        $this->masterPlayerRepository = $repositoryProvider->getMasterRepository(PlayerRepository::class);
        $this->partnerWebApiProvider = $partnerWebApiProvider;
        $this->cacheService = $cacheService;
        $this->session = $session;
        $this->geoIpService = $geoIpService;
    }

    /**
     * @param Player $player
     * @param string $ip
     *
     * @return bool
     *
     * Invalid cache key exception will no be thrown because it don't have external input.
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function refreshToken(Player $player, string $ip): bool
    {
        $partner = $player->getPartner();

        if ($this->geoIpService->isBlocked($ip, $partner)) {
            return false;
        }

        $token = $player->getExternalToken();
        $partnerApi = $this->partnerWebApiProvider->getPartnerApi($partner, $player->isFreePlay());
        $cacheKey = self::CACHE_KEY_PLAYER_TOKEN_VALIDATED . $player->getId();
        $isLoggedId = $this->cacheService->get($cacheKey);

        if (!$isLoggedId) {
            $isLoggedId = $partnerApi->refreshToken($token);

            $this->cacheService->set(
                $cacheKey,
                $isLoggedId,
                self::CACHE_TTL_TOKEN_VALIDATED
            );
        }

        return $isLoggedId;
    }

    /**
     * Return true if player session should be reinitialized on navigation
     *
     * @param Partner $partner
     *
     * @return bool
     */
    public function shouldReinitSession(Partner $partner): bool
    {
        return FeatureChecker::requiresSessionReinit(new WebApiType($partner->getApiId()));
    }

    /**
     * @param Partner $partner
     * @param string $token
     * @param int $gameId
     * @param bool $isMobile
     * @param bool $isFreePlay
     *
     * @return bool
     */
    public function reinitSession(Partner $partner, string $token, int $gameId, bool $isMobile, bool $isFreePlay): bool
    {
        /** @var ReinitSessionApiInterface|WebApiInterface $partnerApi */
        $partnerApi = $this->partnerWebApiProvider->getPartnerApi($partner, $isFreePlay);

        if (!FeatureChecker::requiresSessionReinit($partnerApi->getType())) {
            return false;
        }

        return $partnerApi->reinitializeGameSession($token, $gameId, $isMobile);
    }

    /**
     * @return Player
     *
     * @throws \CoreBundle\Exception\MissingSessionKeyException
     */
    public function getPlayerFromSession(): Player
    {
        $playerId = $this->session->getPlayerId();
        /** @var Player $player - we have in session 100% existing user. */
        $player = $this->masterPlayerRepository->find($playerId);

        return $player;
    }

    /**
     * @return Player|null
     */
    public function getOptionalPlayerFromSession(): ?Player
    {
        try {
            $playerId = $this->session->getPlayerId();
            /** @var Player $player - we have in session 100% existing user. */
            $player = $this->masterPlayerRepository->find($playerId);
        } catch (MissingSessionKeyException $e) {
            $player = null;
        }

        return $player;
    }

    /**
     * @param int $id
     *
     * @return Player|null
     */
    public function findById(int $id): ?Player
    {
        /** @var Player $player */
        $player = $this->masterPlayerRepository->find($id);

        return $player;
    }
}
