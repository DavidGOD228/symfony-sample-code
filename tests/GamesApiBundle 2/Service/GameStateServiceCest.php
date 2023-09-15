<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service;

use GamesApiBundle\Service\GameStateService;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class GameStateServiceCest
 */
final class GameStateServiceCest extends AbstractUnitTest
{
    private GameStateService $service;

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     * @throws \Exception
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->service = $I->getContainer()->get(GameStateService::class);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testGetGameState(UnitTester $I): void
    {
        $allowed_states = [
            1 => 'live',
            2 => 'video',
            3 => 'maintenance',
        ];

        foreach ($allowed_states as $key => $value) {
            $I->getWsRedis()->set('game_state_1', $value);
            $response = $this->service->getGameState(1);
            $I->assertEquals($allowed_states[$key], $response);
        }

        $I->getWsRedis()->del('game_state_1');
        $response = $this->service->getGameState(1);
        $I->assertEquals($allowed_states[1], $response);
    }
}