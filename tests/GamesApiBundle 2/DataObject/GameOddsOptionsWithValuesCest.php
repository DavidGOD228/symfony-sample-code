<?php
namespace SymfonyTests\Unit\GamesApiBundle\DataObject;

use GamesApiBundle\DataObject\GameOddsOptions;
use GamesApiBundle\DataObject\GameOddsOptionsWithValues;
use GamesApiBundle\DataObject\OddInfo;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class GameOddsOptionsWithValuesCest
 */
class GameOddsOptionsWithValuesCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     */
    public function testCreateFromOptionsAndInfo(UnitTester $I): void
    {
        $gameOddsOptionsWithValues = new GameOddsOptionsWithValues(1, [], [], [], []);
        $gameOddsOptions = new GameOddsOptions(1, [], [], []);
        $oddInfo = new OddInfo(0, 1.1, 'active', 'BALLX1_YES');
        $response = GameOddsOptionsWithValues::createFromOptionsAndInfo($gameOddsOptions, [$oddInfo]);
        $I->assertEquals($gameOddsOptionsWithValues, $response);
    }
}