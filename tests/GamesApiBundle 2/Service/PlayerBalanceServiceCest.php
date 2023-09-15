<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Service;

use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\Player;
use Doctrine\ORM\Tools\ToolsException;
use GamesApiBundle\DataObject\PlayerBalance;
use GamesApiBundle\Service\PlayerBalanceService;
use SymfonyTests\_support\Redis\RedisMockAdapter;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class PlayerBalanceServiceCest
 */
final class PlayerBalanceServiceCest extends AbstractUnitTest
{
    /**
     * @var PlayerBalanceService
     */
    private $balanceService;

    /**
     * @var RedisMockAdapter
     */
    private $redis;

    /** @inheritDoc */
    protected function setUpFixtures(): void
    {
        $this->fixtureBoostrapper->addPartners(1, true);
        $this->fixtureBoostrapper->addPlayers(1);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $container = $I->getContainer();
        $this->redis = $I->getCacheRedis();

        $this->balanceService = $container->get(PlayerBalanceService::class);
    }

    /**
     * @param UnitTester $I
     */
    public function testGetPlayerBalance(UnitTester $I): void
    {
        /** @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        /** @var Player $player */
        $player = $this->getEntityByReference('player:1');

        $response = $this->balanceService->getPlayerBalance($partner, $player);
        $I->assertEquals(new PlayerBalance(false, 'eur100.00'), $response);

        $partner->setApiShowBalance(true);
        $response = $this->balanceService->getPlayerBalance($partner, $player);
        $I->assertEquals(new PlayerBalance(true, 'eur100.00'), $response);

        $this->redis->set('directapi_balance_1', '0.00 руб.');
        $partner->setApiShowBalance(true);
        $response = $this->balanceService->getPlayerBalance($partner, $player);
        $I->assertEquals(new PlayerBalance(true, '0.00 руб.'), $response);

        $this->redis->del('directapi_balance_1');
        $partner->setApiShowBalance(true);
        $response = $this->balanceService->getPlayerBalance($partner, $player);
        $I->assertEquals(new PlayerBalance(true, 'eur100.00'), $response);
    }

    /**
     * @param UnitTester $I
     */
    public function getPlayerBalanceStateTest(UnitTester $I): void
    {
        /* @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        /* @var Player $player */
        $player = $this->getEntityByReference('player:1');

        $partner->setApiShowBalance(true);
        $balance = $this->balanceService->getPlayerBalanceState($player);
        $I->assertEquals(new PlayerBalance(true, 'eur100.00'), $balance);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \CoreBundle\Exception\CoreException
     */
    public function refreshPlayerBalanceTest(UnitTester $I): void
    {
        /* @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        /* @var Player $player */
        $player = $this->getEntityByReference('player:1');

        $partner->setApiShowBalance(true);

        $isTaskCreated = $this->balanceService->refreshPlayerBalance($player);
        $I->assertTrue($isTaskCreated);

        $isTaskCreated = $this->balanceService->refreshPlayerBalance($player);
        $I->assertFalse($isTaskCreated);
    }
}
