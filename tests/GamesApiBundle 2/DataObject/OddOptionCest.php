<?php
namespace SymfonyTests\Unit\GamesApiBundle\DataObject;

use GamesApiBundle\DataObject\OddOption;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class OddOptionCest
 */
class OddOptionCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     */
    public function testOddOption(UnitTester $I): void
    {
        $oddOption = new OddOption(1, 2, ['suits'], false);
        $I->assertEquals(1, $oddOption->getId());
        $I->assertEquals(2, $oddOption->getItemsCount());
        $I->assertEquals(['suits'], $oddOption->getSuits());
        $I->assertEquals(false, $oddOption->getExpectedBlueDice());
    }
}