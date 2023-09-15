<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\Gamification;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\BetItem;
use Acme\SymfonyDb\Entity\GameItem;
use Doctrine\ORM\Tools\ToolsException;
use GamesApiBundle\Service\Gamification\BetItemsBuilder;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\DataProvider;
use SymfonyTests\UnitTester;

/**
 * Class BetItemsBuilderCest
 */
final class BetItemsBuilderCest extends AbstractUnitTest
{
    private BetItemsBuilder $builder;

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        /** @var BetItemsBuilder $builder */
        $builder = $I->getContainer()->get(BetItemsBuilder::class);
        $this->builder = $builder;
    }

    /**
     * @param UnitTester $I
     */
    public function testBuildItemsWithDefaultFormatter(UnitTester $I): void
    {
        $gameId = GameDefinition::LUCKY_7;
        $bet = $this->createBetWithItems($gameId, 'yellow', 'black', '1', '42');

        $actualItems = $this->builder->build([$bet]);
        $expectedItems = ['1-1', '1-42'];

        $I->assertEquals($expectedItems, $actualItems);
    }

    /**
     * @param UnitTester $I
     */
    public function testBuildItemsWithLucky6Formatter(UnitTester $I): void
    {
        $gameId = GameDefinition::LUCKY_6;
        $bet = $this->createBetWithItems($gameId, 'red', 'blue', '2', '7');

        $actualItems = $this->builder->build([$bet]);
        $expectedItems = ['9-7', '9-2'];

        $I->assertEquals($expectedItems, $actualItems);
    }

    /**
     * @param UnitTester $I
     */
    public function testBuildItemsWithDiceDuelFormatterWithColors(UnitTester $I): void
    {
        $gameId = GameDefinition::DICE_DUEL;
        $bet = $this->createBetWithItems($gameId, 'dice', 'dice', '1', '5');

        $actualItems = $this->builder->build([$bet]);
        $expectedItems = ['10-1-red', '10-5-blue'];

        $I->assertEquals($expectedItems, $actualItems);
    }

    /**
     * @param UnitTester $I
     */
    public function testBuildItemsWithDiceDuelFormatterWithoutColors(UnitTester $I): void
    {
        $gameId = GameDefinition::DICE_DUEL;
        $bet = $this->createBetWithItems($gameId, 'dice', null, '1', null);

        $actualItems = $this->builder->build([$bet]);
        $expectedItems = ['10-1'];

        $I->assertEquals($expectedItems, $actualItems);
    }

    /**
     * @param int $gameId
     * @param string|null $color1
     * @param string|null $color2
     * @param string|null $number1
     * @param string|null $number2
     *
     * @return Bet
     */
    private function createBetWithItems(
        int $gameId,
        ?string $color1,
        ?string $color2,
        ?string $number1,
        ?string $number2
    ): Bet
    {
        $bet = (new DataProvider($gameId))->getNewBet();
        $game = $bet->getOdd()->getGame();

        if ($color1) {
            $gameItem1 = (new GameItem())
                ->setGame($game)
                ->setColor($color1)
                ->setName($number1)
                ->setNumber($number1)
            ;

            $betItem1 = (new BetItem())
                ->setGameItem($gameItem1)
                ->setOrder(2)
            ;

            $bet->addItem($betItem1);
        }

        if ($color2) {
            $gameItem2 = (new GameItem())
                ->setGame($game)
                ->setColor($color2)
                ->setName($number2)
                ->setNumber($number2)
            ;

            $betItem2 = (new BetItem())
                ->setGameItem($gameItem2)
                ->setOrder(1)
            ;
            $bet->addItem($betItem2);
        }

        return $bet;
    }
}
