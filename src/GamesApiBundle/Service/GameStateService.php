<?php

namespace GamesApiBundle\Service;

use Acme\Redis\RedisInterface;
use CoreBundle\Service\CacheService;

/**
 * Class GameStateService
 */
class GameStateService
{
    public const CACHE_KEY_GAME_STATE = 'game_state_';

    private const ALLOWED_STATES = [
        self::STATE_LIVE,
        self::STATE_VIDEO,
        self::STATE_MAINTENANCE
    ];

    private const TTL = CacheService::DEFAULT_TTL;

    public const STATE_LIVE = 'live';
    public const STATE_VIDEO = 'video';
    public const STATE_MAINTENANCE = 'maintenance';

    private RedisInterface $redisClient;

    /**
     * GameStateService constructor.
     *
     * @param RedisInterface $redisClient
     */
    public function __construct(RedisInterface $redisClient)
    {
        $this->redisClient = $redisClient;
    }

    /**
     * @param int $gameId
     *
     * @return string
     */
    public function getGameState(int $gameId): string
    {
        $stateFromCache = $this->redisClient->get(self::CACHE_KEY_GAME_STATE . $gameId);
        if (!in_array($stateFromCache, self::ALLOWED_STATES)) {
            return self::STATE_LIVE;
        }

        return $stateFromCache;
    }

    /**
     * @param int $gameId
     * @param string $state
     *
     * @return bool
     */
    public function setGameState(int $gameId, string $state): bool
    {
        $key = self::CACHE_KEY_GAME_STATE . $gameId;
        $current = $this->getGameState($gameId);
        $current = !$current ? self::STATE_LIVE : $current;
        if ($current != $state) {
            $this->redisClient->setex($key, self::TTL, $state);
            return true;
        }

        return false;
    }
}
