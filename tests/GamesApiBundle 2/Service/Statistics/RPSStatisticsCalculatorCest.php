<?php

namespace SymfonyTests\Unit\GamesApiBundle\Service\Statistics;

use Codeception\Stub;
use GamesApiBundle\Service\Statistics\Repository\RPSStatisticsRepository;
use GamesApiBundle\Service\Statistics\RPSStatisticsCalculator;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class RPSStatisticsCalculatorCest
 */
class RPSStatisticsCalculatorCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testGetLatestStatistics(UnitTester $I): void
    {
        $repository = Stub::makeEmpty(RPSStatisticsRepository::class, [
            'getLastZone1Cards' => Stub\Expected::once(function () {
                return ['rock', 'paper', 'scissors', 'scissors'];
            }),
            'getLastZone2Cards' => Stub\Expected::once(function () {
                return ['paper', 'scissors', 'scissors', 'rock'];
            }),
        ]);
        $this->stubsToVerify[] = $repository;

        /** @var RPSStatisticsCalculator $service */
        $service = Stub::make(RPSStatisticsCalculator::class, [
            'rpsRepository' => $repository,
        ]);

        $result = $service->getLatestStatistics();
        $I->assertEquals(['R', 'P', 'S', 'S'], $result->zone1, 'Wrong zone 1 cards');
        $I->assertEquals(['P', 'S', 'S', 'R'], $result->zone2, 'Wrong zone 2 cards.');
    }
}