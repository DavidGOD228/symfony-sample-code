<?php

namespace GamesApiBundle\Worker;

use Acme\Delay\DelayInterface;
use Acme\Redis\RedisInterface;
use Acme\Semaphore\SemaphoreInterface;
use CoreBundle\Command\Worker\AbstractSemaphoreQueueWorker;
use CoreBundle\Service\Event\EventBroadcaster;
use CoreBundle\Service\RepositoryProviderInterface;
use CoreBundle\Service\SerializerService;
use GamesApiBundle\DataObject\GameStatistics\UpdateGameStatisticsTask;
use GamesApiBundle\IframeEvent\GameStatisticsWebsocketEventV1;
use GamesApiBundle\IframeEvent\GameStatisticsWebsocketEventV3;
use GamesApiBundle\Service\Statistics\GameStatisticsProvider;
use ImportBundle\Worker\Import\Game\ImportGameFactory;
use JMS\Serializer\SerializationContext;
use Psr\Log\LoggerInterface;

/**
 * Class GameStatisticsWorker
 */
class GameStatisticsWorker extends AbstractSemaphoreQueueWorker
{
    public const KEY_PRODUCER = 'game_statistics_producer';
    private const CHANNEL = 'game.statistics.v1';
    private const SEMAPHORE_PREFIX = 'game_statistics:';
    protected const MAX_WORKER_TTL = 120; // Seconds.

    private EventBroadcaster $broadcaster;
    private GameStatisticsProvider $statisticsService;
    private SerializerService $serializer;
    private RedisInterface $redis;

    /**
     * @param LoggerInterface $logger
     * @param SemaphoreInterface $semaphoreService
     * @param DelayInterface $delay
     * @param RepositoryProviderInterface $repositoryProvider
     * @param EventBroadcaster $broadcaster
     * @param GameStatisticsProvider $statisticsService
     * @param SerializerService $serializer
     * @param RedisInterface $redis
     */
    public function __construct(
        LoggerInterface $logger,
        SemaphoreInterface $semaphoreService,
        DelayInterface $delay,
        RepositoryProviderInterface $repositoryProvider,
        EventBroadcaster $broadcaster,
        GameStatisticsProvider $statisticsService,
        SerializerService $serializer,
        RedisInterface $redis
    )
    {
        parent::__construct($semaphoreService, $logger, $delay, $repositoryProvider->getMasterEntityManager());
        $this->broadcaster = $broadcaster;
        $this->statisticsService = $statisticsService;
        $this->serializer = $serializer;
        $this->redis = $redis;
        $this->ensureStatistics();
    }

    /**
     * @param UpdateGameStatisticsTask $packet
     */
    protected function processPacket($packet): void
    {
        try {
            $gameId = $packet->getGameId();
            $this->updateGameStatistics($gameId);
            $this->logger->info("Game statistics was updated for game: $gameId");
        } catch (\Throwable $e) {
            $this->logger->warning(
                'Error generating game statistics: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }
    }

    /**
     * @return int
     */
    protected function getDoctrineCacheStrategy(): int
    {
        return self::CACHE_STRATEGY_CLEAR_EM_BETWEEN_RUNS;
    }

    /**
     * @param UpdateGameStatisticsTask $packet
     *
     * @return string
     */
    protected function getLockKey($packet): string
    {
        return self::SEMAPHORE_PREFIX . $packet->getGameId();
    }

    /**
     * @return string
     */
    protected function getTopic(): string
    {
        return self::CHANNEL;
    }

    /**
     * @param int $gameId
     *
     * @todo: refactor in one release cycle
     * needed in order to broadcast events in V1 format
     */
    protected function updateGameStatistics(int $gameId): void
    {
        $statistics = $this->statisticsService->getLatestGameStatistics($gameId);

        $payload = $this->serializer->serialize(
            $statistics,
            SerializerService::JSON,
            (new SerializationContext())->setSerializeNull(false)
        );
        $isV3 = !in_array($gameId, ImportGameFactory::NON_V3_GAMES, true);

        if ($isV3) {
            // all v3 Games should broadcast V3
            $event = new GameStatisticsWebsocketEventV3(
                $gameId,
                $payload
            );
            $this->broadcaster->broadcastAndStore($event);
        }

        // @todo: remove in one release cycle
        // needed in order to broadcast events in V1 format
        // so that V3 games will broadcast V1 as well
        $eventV1 = new GameStatisticsWebsocketEventV1(
            $gameId,
            $payload
        );
        $this->broadcaster->broadcastAndStore($eventV1);
    }

    /**
     * The statistics is being updated on game run switch.
     * In case if pause between runs greater than cache's entry TTL (maintenance) - cache entry may expire.
     * Let's ensure that we'll always have statistics populated in cache.
     */
    private function ensureStatistics(): void
    {
        foreach (GameStatisticsProvider::SUPPORTED_GAMES as $gameId) {
            $isV3 = !in_array($gameId, ImportGameFactory::NON_V3_GAMES, true);
            $event = $isV3 ? new GameStatisticsWebsocketEventV3($gameId, '')
                : new GameStatisticsWebsocketEventV1($gameId, '');
            $cacheKey = $event->getPersistKey();
            if (!$this->redis->exists($cacheKey) || $this->redis->ttl($cacheKey) < self::MAX_WORKER_TTL) {
                $this->updateGameStatistics($gameId);
            }
        }
    }
}
