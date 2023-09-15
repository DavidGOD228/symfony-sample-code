<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service;

use Acme\RmqProducer\ProducerInterface;
use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\Player;
use Acme\WebApi\Api\Acme\WebApiAcme1;
use Acme\WebApi\Api\Bti\WebApiBti;
use Acme\WebApi\Api\Microgaming\WebApiMicrogaming;
use Acme\WebApi\Api\WebApiNone;
use Acme\WebApi\Enum\WebApiType;
use Acme\WebApi\WebApiInterface;
use Codeception\Stub;
use CodeigniterSymfonyBridge\UpdateBalanceTask;
use CoreBundle\Service\CacheServiceInterface;
use CoreBundle\Service\Event\EventBroadcaster;
use CoreBundle\Service\PartnerService;
use CoreBundle\Service\SerializerService;
use GamesApiBundle\Service\BalanceService;
use PartnerApiBundle\Service\PartnerWebApiProvider;
use Symfony\Component\HttpFoundation\Session\Session;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class BalanceServiceCest
 */
final class BalanceServiceCest extends AbstractUnitTest
{
    private const CACHE_KEY_PREFIX = 'directapi_balance_';

    /** @var BalanceService $service */
    private $service;

    /** @var Player */
    private $player;

    /** @var Currency */
    private $currency;


    /** @inheritDoc */
    protected function setUpFixtures(): void
    {
        $this->fixtureBoostrapper->addPlayers();
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);
        $container = $I->getContainer();

        $apiProvider = Stub::make(PartnerWebApiProvider::class, [
            'getPartnerApi' => function (Partner $partner) {
                if ($partner->getApiUrl() == 'failed') {
                    return Stub::make(WebApiNone::class, [
                        'getType' => WebApiType::getNone(),
                    ]);
                }
                if ($partner->getApiUrl() == 'bti') {
                    return Stub::make(WebApiBti::class, [
                        'getType' => WebApiType::getBti(),
                        'getBalance' => $partner->getAmountBetMax(),
                        'getRequiredSessionDataKeys' => ['testField']
                    ]);
                }

                return Stub::make(WebApiAcme1::class, [
                    'getType' => WebApiType::getAcme1(),
                    'getBalance' => $partner->getAmountBetMax()
                ]);
            }
        ]);

        $session = Stub::make(Session::class, [
            'get' => function ($key) {
                return $key . ':value';
            }
        ]);

        $this->service = new BalanceService(
            $container->get(CacheServiceInterface::class),
            $apiProvider,
            $container->get(ProducerInterface::class),
            $container->get(PartnerService::class),
            $container->get(EventBroadcaster::class),
            $session,
            $container->get(SerializerService::class)
        );

        $this->player = $this->getEntityByReference('player:1');
        $this->currency = $this->player->getCurrency();
        $this->currency->setTemplate('X CUR');
        $this->player->getPartner()->setAmountBetMax(222);
    }

    /**
     * @param int $playerId
     *
     * @return string
     */
    private function getCacheKey(int $playerId): string
    {
        return self::CACHE_KEY_PREFIX . $playerId;
    }

    /**
     * @param UnitTester $I
     */
    public function setUpdatedBalanceTest(UnitTester $I): void
    {
        $key = $this->getCacheKey($this->player->getId());
        $cachedBalance = $I->getCacheRedis()->get($key);
        $I->assertEmpty($cachedBalance);

        $this->service->setUpdatedBalance($this->player, 22.5, $this->currency);
        $cachedBalance = $I->getCacheRedis()->get($key);
        $I->assertEquals('22.50 CUR', $cachedBalance);
    }

    /**
     * @param UnitTester $I
     */
    public function setUpdatedBalanceByPlayerIdTest(UnitTester $I): void
    {
        $key = $this->getCacheKey($this->player->getId());
        $cachedBalance = $I->getCacheRedis()->get($key);
        $I->assertEmpty($cachedBalance);

        $I->assertEmpty($I->getProducer()->messages);
        $this->service->setUpdatedBalanceByPlayerId($this->player->getId(), 22.5, $this->currency);
        $cachedBalance = $I->getCacheRedis()->get($key);
        $I->assertEquals('22.50 CUR', $cachedBalance);

        $I->assertCount(1, $I->getWsRedis()->getPublishedEvents());
        $I->assertCount(1, $I->getWsRedis()->getPublishedEvents()['player_balance']);
        $producedEvent = $I->getWsRedis()->getPublishedEvents()['player_balance'][0];
        $I->assertEquals('{"playerId":1,"balance":"22.50 CUR"}', $producedEvent);
    }

    /**
     * @param UnitTester $I
     */
    public function getCachedBalanceByPlayerIdTest(UnitTester $I): void
    {
        $key = $this->getCacheKey($this->player->getId());
        $cachedBalance = $I->getCacheRedis()->get($key);
        $I->assertEmpty($cachedBalance);

        $balance = $this->service->getCachedBalanceByPlayerId($this->player->getId());
        $I->assertEquals('0', $balance);

        $newValue = 'zzz TEST';
        $I->getCacheRedis()->set($key, $newValue);
        $balance = $this->service->getCachedBalanceByPlayerId($this->player->getId());
        $I->assertEquals($newValue, $balance);
    }

    /**
     * @param UnitTester $I
     */
    public function updateCachedBalanceByPartnerIdTest(UnitTester $I): void
    {
        $balance = $this->service->updateCachedBalanceByPartnerId(
            22,
            $this->player->getId(),
            '22',
            $this->currency,
            false
        );
        $I->assertEquals('0.00 CUR', $balance);
        $balance = $this->service->updateCachedBalanceByPartnerId(
            $this->player->getPartner()->getId(),
            $this->player->getId(),
            '22',
            $this->currency,
            false
        );
        $I->assertEquals('222.00 CUR', $balance);
    }

    /**
     * @param UnitTester $I
     */
    public function updateCachedBalanceTest(UnitTester $I): void
    {
        $this->player->getPartner()->setAmountBetMax(0);
        $balance = $this->service->updateCachedBalance(
            $this->player->getPartner(),
            $this->player->getId(),
            $this->player->getExternalToken(),
            $this->player->getCurrency(),
            false
        );
        $I->assertEquals('0.00 CUR', $balance);
        $this->player->getPartner()->setAmountBetMax(10);
        $balance = $this->service->updateCachedBalance(
            $this->player->getPartner(),
            $this->player->getId(),
            $this->player->getExternalToken(),
            $this->player->getCurrency(),
            false
        );

        $I->assertEquals('10.00 CUR', $balance);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \CoreBundle\Exception\CoreException
     */
    public function publishUpdateBalanceTaskTest(UnitTester $I): void
    {
        $I->assertEmpty($I->getProducer()->messages);
        /** @var WebApiInterface $webApi */
        $webApi = Stub::makeEmpty(WebApiBti::class, [
            'getType' => WebApiType::getBti(),
            'getRequiredSessionDataKeys' => ['testField']
        ]);
        $this->service->publishUpdateBalanceTask(
            1,
            1,
            'externalToken1',
            false,
            1,
            $webApi
        );
        $this->assertGeneratedTask($I);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \CoreBundle\Exception\CoreException
     */
    public function publishUpdateBalanceTaskForFreePlayTest(UnitTester $I): void
    {
        $I->assertEmpty($I->getProducer()->messages);
        /** @var WebApiInterface $webApi */
        $webApi = Stub::makeEmpty(WebApiMicrogaming::class, [
            'getType' => WebApiType::getMicrogaming(),
            'getRequiredSessionDataKeys' => ['testField']
        ]);
        $this->player->setIsFreePlay(true);
        $this->service->publishUpdateBalanceTask(
            1,
            1,
            'externalToken1',
            true,
            1,
            $webApi
        );
        $this->assertGeneratedTask($I);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \CoreBundle\Exception\CoreException
     */
    public function publishRefreshBalanceTaskTest(UnitTester $I): void
    {
        $I->assertEmpty($I->getProducer()->messages);

        $this->player->getPartner()->setApiUrl('failed');
        $this->service->publishRefreshBalanceTask($this->player);
        $I->assertEmpty($I->getProducer()->messages);

        $this->player->getPartner()->setApiUrl('bti');
        $I->assertEmpty($I->getProducer()->messages);
        $this->service->publishRefreshBalanceTask($this->player);
        $this->assertGeneratedTask($I);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \CoreBundle\Exception\CoreException
     */
    public function publishRefreshBalanceTaskForFreePlayTest(UnitTester $I): void
    {
        $I->assertEmpty($I->getProducer()->messages);

        $this->player->setIsFreePlay(true);
        $this->player->getPartner()->setApiUrl('bti');

        $this->service->publishRefreshBalanceTask($this->player);
        $this->assertGeneratedTask($I);
    }

    /**
     * @param UnitTester $I
     */
    private function assertGeneratedTask(UnitTester $I): void
    {
        $I->assertCount(1, $I->getProducer()->messages);
        $I->assertEquals('update_balance_producer', $I->getProducer()->messages[0]['channel']);

        $rawTask = $I->getProducer()->messages[0]['message'];
        /** @var UpdateBalanceTask $task */
        $task = unserialize($rawTask);
        $I->assertInstanceOf(UpdateBalanceTask::class, $task);
        $I->assertEquals(1, $task->getPartnerId());
        $I->assertEquals(1, $task->getPlayerId());
        $I->assertEquals('externalToken1', $task->getPlayersToken());
        $I->assertEquals(1, $task->getCurrencyId());
        $I->assertCount(1, $task->getSessionData());
        $I->assertContains('testField:value', $task->getSessionData());
        $I->assertSame($this->player->isFreePlay(), $task->isFreePlay());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \ReflectionException
     */
    public function getCacheKeyTest(UnitTester $I): void
    {
        $expected = $this->getCacheKey(22);
        $got = $this->callProtected($this->service, 'getCacheKey', [22]);
        $I->assertEquals($expected, $got);
    }
}
