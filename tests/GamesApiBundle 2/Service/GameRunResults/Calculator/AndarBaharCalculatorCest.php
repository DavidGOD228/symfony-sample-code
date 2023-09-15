<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\GameRunResults\Calculator;

use Acme\SymfonyDb\Entity\AbCard;
use Acme\SymfonyDb\Entity\AbRunRoundCard;
use GamesApiBundle\Service\GameRunResults\Calculator\AndarBaharCalculator;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class AndarBaharCalculatorCest
 */
final class AndarBaharCalculatorCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     *
     * @throws \ReflectionException
     */
    public function testCalculate(UnitTester $I): void
    {
        $cards = [
            (new AbRunRoundCard())->setDealtTo('joker')->setCard((new AbCard())->setValue('a')->setSuit('c')),
            (new AbRunRoundCard())->setDealtTo('andar')->setCard((new AbCard())->setValue('j')->setSuit('s')),
            (new AbRunRoundCard())->setDealtTo('bahar')->setCard((new AbCard())->setValue('2')->setSuit('h')),
            (new AbRunRoundCard())->setDealtTo('bahar')->setCard((new AbCard())->setValue('a')->setSuit('s')),
        ];

        $calculator = new AndarBaharCalculator();
        $winner = $calculator->calculate($cards);
        $I->assertEquals(['bahar'], $winner->getWinners());
        $I->assertEquals([], $winner->getCombinations());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \ReflectionException
     */
    public function testCalculateDifferentSituation(UnitTester $I): void
    {
        $cards = [
            (new AbRunRoundCard())->setDealtTo('joker')->setCard((new AbCard())->setValue('a')->setSuit('c')),
            (new AbRunRoundCard())->setDealtTo('andar')->setCard((new AbCard())->setValue('j')->setSuit('s')),
            (new AbRunRoundCard())->setDealtTo('bahar')->setCard((new AbCard())->setValue('2')->setSuit('h')),
            (new AbRunRoundCard())->setDealtTo('andar')->setCard((new AbCard())->setValue('a')->setSuit('s')),
        ];

        $calculator = new AndarBaharCalculator();
        $winner = $calculator->calculate($cards);
        $I->assertEquals(['andar'], $winner->getWinners());
        $I->assertEquals([], $winner->getCombinations());
    }
}
