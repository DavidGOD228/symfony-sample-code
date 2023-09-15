<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\BetItem;
use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameItem;
use Acme\SymfonyDb\Entity\GameRunResultItem;
use Acme\SymfonyDb\Entity\Odd;
use Codeception\Stub;
use CoreBundle\Service\GameService;
use GamesApiBundle\Service\GameItemService;
use SymfonyTests\_support\Doctrine\EntityHelper;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class GameItemServiceCest
 */
final class GameItemServiceCest extends AbstractUnitTest
{
    private const KNOWN_GAMES = [
        GameService::GAME_LUCKY_7,
        GameService::GAME_LUCKY_5,
        GameService::GAME_POKER,
        GameService::GAME_BACCARAT,
        GameService::GAME_FORTUNE,
        GameService::GAME_WAR,
        GameService::GAME_LUCKY_6,
        GameService::GAME_DICE_DUEL,
        GameService::GAME_HEADSUP,
        GameService::GAME_SPEEDY7,
        GameService::GAME_ANDAR_BAHAR,
        GameService::GAME_MATKA,
    ];

    private const GAMES_WITH_BALLS = [
        GameService::GAME_LUCKY_7,
        GameService::GAME_LUCKY_5,
        GameService::GAME_LUCKY_6,
    ];

    private const GAMES_WITH_DICES = [
        GameService::GAME_DICE_DUEL,
    ];

    private const GAMES_WITH_WHEEL_SECTORS = [
        GameService::GAME_FORTUNE,
    ];

    /**
     * @param UnitTester $I
     *
     * @throws \ReflectionException
     */
    public function testGetGameItemType(UnitTester $I): void
    {
        $service = new GameItemService();
        /** @var Game $game */
        $game = EntityHelper::getEntityWithId(Game::class, 1);
        /** @var GameItem $gameItem */
        $gameItem = Stub::makeEmpty(GameItem::class, ['getGame' => $game]);

        foreach (self::KNOWN_GAMES as $gameId) {
            $this->setPrivateProperty($game, 'id', $gameId);
            $expectedResult = null;
            if (in_array($gameId, self::GAMES_WITH_BALLS, true)) {
                $expectedResult = GameItem::TYPE_BALL;
            } elseif (in_array($gameId, self::GAMES_WITH_DICES, true)) {
                $expectedResult = GameItem::TYPE_DICE;
            } elseif (in_array($gameId, self::GAMES_WITH_WHEEL_SECTORS, true)) {
                $expectedResult = GameItem::TYPE_WHEEL_SECTOR;
            } elseif ($gameId === GameDefinition::MATKA) {
                $expectedResult = GameItem::TYPE_MATKA;
            }

            if ($expectedResult === null) {
                $I->expectThrowable(\InvalidArgumentException::class, function () use ($service, $gameItem) {
                    $service->getType($gameItem);
                }, "No expected exception thrown for game:$gameId");
            } else {
                $result = $service->getType($gameItem);
                $I->assertEquals($expectedResult, $result, "Wrong result for game:$gameId");
            }
        }
    }

    /**
     * @param UnitTester $I
     *
     * @throws \ReflectionException
     */
    public function testGetDiceColorForGameItem(UnitTester $I): void
    {
        $defaultGame = GameService::GAME_DICE_DUEL;
        $defaultOrder = 1;

        $cases = [
            ['expected' => 'error', 'oddsClass' => 'NUMBER_ROLLED_BLUE', 'game' => GameService::GAME_SPEEDY7],
            ['expected' => 'blue', 'oddsClass' => 'NUMBER_ROLLED_BLUE'],
            ['expected' => 'blue', 'oddsClass' => 'NUMBER_ROLLED_BLUE', 'order' => 2],
            ['expected' => 'red', 'oddsClass' => 'NUMBER_ROLLED_RED'],
            ['expected' => 'red', 'oddsClass' => 'NUMBER_ROLLED_RED', 'order' => 2],
            ['expected' => 'red', 'oddsClass' => 'NUMBER_ROLLED_RED_BLUE', 'order' => 1],
            ['expected' => 'blue', 'oddsClass' => 'NUMBER_ROLLED_RED_BLUE', 'order' => 2],
            ['expected' => 'error', 'oddsClass' => 'NUMBER_ROLLED_RED_BLUE', 'order' => null],
            ['expected' => 'error', 'oddsClass' => 'NON_DICE_ODDS_CLASS'],
        ];
        foreach ($cases as $k => $case) {
            if (!isset($case['game'])) {
                $case['game'] = $defaultGame;
            }
            if (!array_key_exists('order', $case)) {
                $case['order'] = $defaultOrder;
            }
            $cases[$k] = $case;
        }

        $service = new GameItemService();
        /** @var Odd $odds */
        $odds = EntityHelper::getEntityWithId(Odd::class, 1);

        $game = new Game();

        foreach ($cases as $case) {
            $odds->setClass($case['oddsClass']);
            $this->setPrivateProperty($game, 'id', $case['game']);

            /** @var GameItem $gameItem */
            $gameItem = Stub::makeEmpty(GameItem::class, ['getGame' => $game]);

            $betItem = (EntityHelper::getEntityWithId(BetItem::class, 1))
                ->setGameItem($gameItem);
            $this->setPrivateProperty($betItem, 'order', $case['order']);

            if ($case['expected'] === 'error') {
                $I->expectThrowable(\InvalidArgumentException::class, function () use ($service, $odds, $betItem) {
                    $service->getColorByOdd($odds, $betItem);
                }, "Error was no thrown for oddsClass: $case[oddsClass]");
                continue;
            }
            $result = $service->getColorByOdd($odds, $betItem);
            $I->assertSame($case['expected'], $result, "Wrong result oddsClass: $case[oddsClass]");
        }
    }

    /**
     * @param UnitTester $I
     */
    public function testGetDiceColorForGameItemForNonDiceGame(UnitTester $I): void
    {
        $service = new GameItemService();
        $gameItem = (new GameItem())
            ->setGame(Game::createFromId(GameService::GAME_LUCKY_7))
            ->setColor('blue');
        $betItem = (new BetItem())
            ->setGameItem($gameItem);

        $color = $service->getColorByOdd(new Odd(), $betItem);
        $I->assertEquals('blue', $color);
    }

    /**
     * @param UnitTester $I
     */
    public function testGetNullColorForGameItemForNonDiceGame(UnitTester $I): void
    {
        $service = new GameItemService();
        $gameItem = (new GameItem())
            ->setGame(Game::createFromId(GameService::GAME_MATKA))
            ->setColor(null);
        $betItem = (new BetItem())
            ->setGameItem($gameItem);

        $color = $service->getColorByOdd(new Odd(), $betItem);
        $I->assertNull($color);
    }

    /**
     * @param UnitTester $I
     */
    public function testDiceDuelGetColorByResultShouldProvideColorByOrder(UnitTester $I): void
    {
        $service = new GameItemService();
        $gameItem = (new GameItem())
            ->setGame(Game::createFromId(GameDefinition::DICE_DUEL));

        $result1 = (new GameRunResultItem())
            ->setGameItem($gameItem)
            ->setOrder(1);

        $result2 = (new GameRunResultItem())
            ->setGameItem($gameItem)
            ->setOrder(2);

        $I->assertEquals('red', $service->getColorByResult($result1));
        $I->assertEquals('blue', $service->getColorByResult($result2));
    }

    /**
     * @param UnitTester $I
     */
    public function testDiceDuelThirdDiceColorShouldThrowException(UnitTester $I): void
    {
        $service = new GameItemService();
        $gameItem = (new GameItem())
            ->setGame(Game::createFromId(GameDefinition::DICE_DUEL));

        $result3 = (new GameRunResultItem())
            ->setGameItem($gameItem)
            ->setOrder(3);
        EntityHelper::setId($result3, 123);

        $I->expectThrowable(
            new \InvalidArgumentException('NO_ORDER_ON_ITEM: ' . 123),
            static function () use ($service, $result3): void {
                $service->getColorByResult($result3);
            }
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testNonDiceDuelGameColorGetsForItem(UnitTester $I): void
    {
        $service = new GameItemService();
        $gameItem = (new GameItem())
            ->setGame(Game::createFromId(GameDefinition::LUCKY_7))
            ->setColor('white');

        $result1 = (new GameRunResultItem())
            ->setGameItem($gameItem)
            ->setOrder(3);

        $I->assertEquals('white', $service->getColorByResult($result1));
    }
}
