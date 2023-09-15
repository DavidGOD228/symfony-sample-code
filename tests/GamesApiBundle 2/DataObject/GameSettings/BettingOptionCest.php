<?php

namespace SymfonyTests\Unit\GamesApiBundle\DataObject\GameSettings;

use Acme\SymfonyDb\Entity\Odd;
use GamesApiBundle\DataObject\GameOptions\GameOptionsBettingOption;
use ReflectionException;
use SymfonyTests\_support\Doctrine\EntityHelper;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class BettingOptionCest
 */
class BettingOptionCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     *
     * @throws ReflectionException
     */
    public function testSuiteDecoding(UnitTester $I): void
    {
        $odds = new Odd();
        EntityHelper::setId($odds, 1);
        $odds->setClass('class')
            ->setItemsCount(1);
        $bettingOption = new GameOptionsBettingOption($odds);
        $I->assertEquals([], $bettingOption->suits);

        $odds2 = new Odd();
        $suits = ['spades', 'hearts'];
        EntityHelper::setId($odds2, 2);
        $odds2->setClass('class')
            ->setItemsCount(2)
            ->setSuits(json_encode($suits));
        $bettingOption2 = new GameOptionsBettingOption($odds2);
        $I->assertEquals($suits, $bettingOption2->suits);
    }
}
