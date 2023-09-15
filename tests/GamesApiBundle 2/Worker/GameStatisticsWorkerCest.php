<?php

namespace SymfonyTests\Unit\GamesApiBundle\Worker;

use Acme\Redis\RedisInterface;
use Acme\Semaphore\SemaphoreInterface;
use Codeception\Stub;
use CoreBundle\AppEvents\WebsocketEventInterface;
use CoreBundle\Command\Worker\AbstractSemaphoreQueueWorker;
use CoreBundle\Service\Event\EventBroadcaster;
use CoreBundle\Service\SerializerService;
use GamesApiBundle\DataObject\GameStatistics\GameStatistics;
use GamesApiBundle\DataObject\GameStatistics\RPSStatistics;
use GamesApiBundle\DataObject\GameStatistics\UpdateGameStatisticsTask;
use GamesApiBundle\IframeEvent\GameStatisticsWebsocketEventV1;
use GamesApiBundle\IframeEvent\GameStatisticsWebsocketEventV3;
use GamesApiBundle\Service\Statistics\GameStatisticsProvider;
use GamesApiBundle\Worker\GameStatisticsWorker;
use Psr\Log\NullLogger;
use Psr\Log\Test\TestLogger;
use SymfonyTests\_support\CoreBundleMock\RepositoryProviderNullMock;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class GameStatisticsWorkerCest
 */
final class GameStatisticsWorkerCest extends AbstractUnitTest
{
    /** @var int */
    protected $existsCallCount;

    /** @var int */
    protected $ttlCallCount;

    /** @var array */
    protected $touchedGameIds;

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testConstruct(UnitTester $I): void
    {
        $redis = $I->getCacheRedis();
        $worker = Stub::make(
            GameStatisticsWorker::class,
            [
                'updateGameStatistics' => Stub\Expected::exactly(count(GameStatisticsProvider::SUPPORTED_GAMES))
            ]
        );
        // Dirty because logic binded to constructor :-(
        $worker->__construct(
            new NullLogger(),
            Stub::makeEmpty(SemaphoreInterface::class),
            $I->getDelay(),
            new RepositoryProviderNullMock(),
            Stub::make(EventBroadcaster::class),
            Stub::make(GameStatisticsProvider::class),
            Stub::make(SerializerService::class),
            $redis
        );
        $this->stubsToVerify[] = $worker;
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testProcessPacket(UnitTester $I): void
    {
        $cases = [
            ['gameId' => 500, 'statsClass' => 'not_supported', 'isSuccessful' => false],
            [
                'gameId' => 15,
                'stats' => new RPSStatistics(
                    ['rock', 'paper', 'scissors', 'scissors'],
                    ['paper', 'scissors', 'scissors', 'rock']
                ),
                'isSuccessful' => true,
                'dataCallCount' => 2,
                'serializeCallCount' => 1,
            ],
        ];

        foreach ($cases as $case) {
            $logger = new TestLogger();
            $dataCallCount = $case['isSuccessful'] ? $case['dataCallCount'] : 0;
            $serializeCallCount = $case['isSuccessful'] ? $case['serializeCallCount'] : 0;

            $statisticsService = Stub::makeEmpty(GameStatisticsProvider::class, [
                'getLatestGameStatistics' => Stub\Expected::once(function (int $gameId) use ($I, $case) {
                    if (!$case['isSuccessful']) {
                        throw new \InvalidArgumentException('ERROR_ON_GET_STATS');
                    }
                    $I->assertEquals($gameId, $case['gameId'], 'getStats - incorrect gameId.');
                    return new GameStatistics($case['stats']);
                })
            ]);
            $this->stubsToVerify[] = $statisticsService;

            $serializer = Stub::makeEmpty(SerializerService::class, [
                'serialize' => Stub\Expected::exactly(
                    $serializeCallCount,
                    function (GameStatistics $stats) use ($I, $case) {
                        $I->assertInstanceOf(
                            get_class($case['stats']),
                            $stats->statistics,
                            'serialize - stats class.'
                        );
                        return json_encode($stats);
                    }
                )
            ]);
            $this->stubsToVerify[] = $serializer;

            $broadcaster = Stub::makeEmpty(EventBroadcaster::class, [
                'broadcastAndStore' => Stub\Expected::exactly(
                    $dataCallCount,
                    function (WebsocketEventInterface $event) use ($I, $case) {
                        if ($event instanceof GameStatisticsWebsocketEventV3) {
                            $I->assertEquals(
                                'game-statistics:v3:' . $case['gameId'],
                                $event->getPersistKey(),
                                'Wrong event channel.'
                            );
                            return;
                        }
                        if ($event instanceof GameStatisticsWebsocketEventV1) {
                            $I->assertEquals(
                                'game-statistics:' . $case['gameId'],
                                $event->getPersistKey(),
                                'Wrong event channel.'
                            );
                        }
                    }
                )
            ]);
            $this->stubsToVerify[] = $broadcaster;

            $packet = new UpdateGameStatisticsTask($case['gameId']);
            $worker = Stub::make(GameStatisticsWorker::class, [
                'logger' => $logger,
                'statisticsService' => $statisticsService,
                'serializer' => $serializer,
                'broadcaster' => $broadcaster,
            ]);
            $this->callProtected($worker, 'processPacket', [$packet]);

            if ($case['isSuccessful']) {
                $I->assertNotTrue($logger->hasWarningRecords(), 'Unexpected warning logged.');
            } else {
                $I->assertTrue($logger->hasWarningRecords(), 'Warning was not logged.');
            }
        }
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testGetDoctrineCacheStrategy(UnitTester $I): void
    {
        // Clear stubs to verify before checking Doctrine Cache Strategy
        $this->stubsToVerify = [];
        $worker = Stub::makeEmptyExcept(GameStatisticsWorker::class, 'getDoctrineCacheStrategy');
        $strategy = $this->callProtected($worker, 'getDoctrineCacheStrategy', []);
        $I->assertEquals(
            AbstractSemaphoreQueueWorker::CACHE_STRATEGY_CLEAR_EM_BETWEEN_RUNS,
            $strategy,
            'Wrong cache invalidation strategy.'
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testGetLockKey(UnitTester $I): void
    {
        $gameId = 13;
        $worker = Stub::makeEmptyExcept(GameStatisticsWorker::class, 'getLockKey');
        $packet = new UpdateGameStatisticsTask($gameId);
        $lockKey = $this->callProtected($worker, 'getLockKey', [$packet]);
        $I->assertEquals(
            "game_statistics:$gameId",
            $lockKey,
            'Wrong semaphore lock key.'
        );
    }


    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testGetTopic(UnitTester $I): void
    {
        $worker = Stub::makeEmptyExcept(GameStatisticsWorker::class, 'getTopic');
        $topic = $this->callProtected($worker, 'getTopic', []);
        $I->assertEquals(
            'game.statistics.v1',
            $topic,
            'Wrong topic to listen.'
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \ReflectionException
     */
    public function testEnsureStatistics(UnitTester $I): void
    {
        $cases = [
            ['cacheEntryExists' => false],
            ['cacheEntryExists' => true, 'cacheEntryTtl' => 3600],
            ['cacheEntryExists' => true, 'cacheEntryTtl' => 2],
        ];

        foreach ($cases as $case) {
            $this->existsCallCount = 0;
            $this->ttlCallCount = 0;
            $this->touchedGameIds = [];

            if (!isset($case['cacheEntryTtl'])) {
                $case['cacheEntryTtl'] = null;
            }
            $gameCount = count(GameStatisticsProvider::SUPPORTED_GAMES);
            $case['gameCount'] = $gameCount;
            $expectedCalls = [
                'exists' => $gameCount,
                'ttl' => $case['cacheEntryExists'] ? $gameCount : 0,
                'updateGameStatistics' => 0
            ];

            // GameStatisticsWorker::MAX_WORKER_TTL is 120 seconds.
            if (!$case['cacheEntryExists'] || $case['cacheEntryTtl'] < 120) {
                $expectedCalls['updateGameStatistics'] = $gameCount;
            }

            $expectedCalls['redis'] = $expectedCalls['exists'] + $expectedCalls['ttl'];
            $redis = Stub::makeEmpty(RedisInterface::class, [
                'exists' => Stub\Expected::exactly(
                    $expectedCalls['exists'],
                    function ($key) use ($I, $case) {
                        $gameId = str_replace(['game-statistics:', 'v3:'], '', $key);
                        $this->touchedGameIds[] = (int) $gameId;
                        ++$this->existsCallCount;
                        return $case['cacheEntryExists'];
                    }
                ),
                'ttl' => Stub\Expected::exactly(
                    $expectedCalls['ttl'],
                    function () use ($I, $case) {
                        ++$this->ttlCallCount;
                        return $case['cacheEntryTtl'];
                    }
                ),
            ]);
            $this->stubsToVerify[] = $redis;

            $worker = Stub::makeEmptyExcept(GameStatisticsWorker::class, 'ensureStatistics', [
                'redis' => $redis,
                'updateGameStatistics' => Stub\Expected::exactly($expectedCalls['updateGameStatistics'])
            ]);
            $this->stubsToVerify[] = $worker;
            $this->callProtected($worker, 'ensureStatistics', []);
            $I->assertEquals($expectedCalls['exists'], $this->existsCallCount, "Invalid redis:exists call count.");
            $I->assertEquals($expectedCalls['ttl'], $this->ttlCallCount, "Invalid redis:ttl call count.");
            $I->assertCount($gameCount, $this->touchedGameIds, "Game count mismatch.");
            foreach (GameStatisticsProvider::SUPPORTED_GAMES as $gameId) {
                $I->assertContains($gameId, $this->touchedGameIds, "Game:$gameId was not processed.");
            }
        }
    }
}
