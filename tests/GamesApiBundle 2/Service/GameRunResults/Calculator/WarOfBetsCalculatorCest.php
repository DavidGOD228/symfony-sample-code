<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\GameRunResults\Calculator;

use Acme\SymfonyDb\Entity\WarCard;
use Acme\SymfonyDb\Entity\WarRunCard;
use GamesApiBundle\Service\GameRunResults\Calculator\WarOfBetsCalculator;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class WarOfBetsCalculatorCest
 */
final class WarOfBetsCalculatorCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     */
    public function testWarOfBetsDealerWins(UnitTester $I): void
    {
        $cards = [
            (new WarRunCard())->setDealtTo('player')->setCard(
                (new WarCard())->setScore(5)
            ),
            (new WarRunCard())->setDealtTo('dealer')->setCard(
                (new WarCard())->setScore(10)
            ),
        ];

        $calculator = new WarOfBetsCalculator();
        $winner = $calculator->calculate($cards);

        $I->assertEquals(['dealer'], $winner->getWinners());
        $I->assertEquals([], $winner->getCombinations());
    }

    /**
     * @param UnitTester $I
     */
    public function testWarOfBetsPlayerWins(UnitTester $I): void
    {
        $cards = [
            (new WarRunCard())->setDealtTo('player')->setCard(
                (new WarCard())->setScore(14)
            ),
            (new WarRunCard())->setDealtTo('dealer')->setCard(
                (new WarCard())->setScore(10)
            ),
        ];
        $calculator = new WarOfBetsCalculator();
        $winner = $calculator->calculate($cards);

        $I->assertEquals(['player'], $winner->getWinners());
        $I->assertEquals([], $winner->getCombinations());
    }

    /**
     * @param UnitTester $I
     */
    public function testWarOfBetsTie(UnitTester $I): void
    {

        $cards = [
            (new WarRunCard())->setDealtTo('player')->setCard(
                (new WarCard())->setScore(10)
            ),
            (new WarRunCard())->setDealtTo('dealer')->setCard(
                (new WarCard())->setScore(10)
            ),
        ];
        $calculator = new WarOfBetsCalculator();
        $winner = $calculator->calculate($cards);

        $I->assertEquals(['war'], $winner->getWinners());
        $I->assertEquals([], $winner->getCombinations());
    }
}
