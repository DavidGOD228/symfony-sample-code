<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\BetItem;
use Acme\SymfonyDb\Entity\GameItem;
use Acme\SymfonyDb\Entity\GameRunResultItem;
use Acme\SymfonyDb\Entity\Odd;

/**
 * Class GameItemService
 */
final class GameItemService
{
    private const GAMES_WITH_BALLS = [
        GameDefinition::LUCKY_7,
        GameDefinition::LUCKY_7_BETWAY,
        GameDefinition::LUCKY_5,
        GameDefinition::LUCKY_6,
    ];

    private const GAMES_WITH_DICES = [
        GameDefinition::DICE_DUEL,
        GameDefinition::RNG_DICE_DUEL,
    ];

    private const GAMES_WITH_WHEEL_SECTORS = [
        GameDefinition::WHEEL,
        GameDefinition::RNG_WHEEL,
    ];

    private const BLUE_DICE_ODDS_CLASS = 'NUMBER_ROLLED_BLUE';
    private const RED_DICE_ODDS_CLASS = 'NUMBER_ROLLED_RED';
    private const BLUE_AND_RED_DICE_ODDS_CLASS = 'NUMBER_ROLLED_RED_BLUE';

    private const DICE_BLUE_COLOR = 'blue';
    private const DICE_RED_COLOR = 'red';

    /**
     * @param GameItem $item
     *
     * @return int
     * @throws \InvalidArgumentException
     */
    public function getType(GameItem $item): int
    {
        $gameId = $item->getGame()->getId();
        if (in_array($gameId, self::GAMES_WITH_BALLS, true)) {
            return GameItem::TYPE_BALL;
        }
        if (in_array($gameId, self::GAMES_WITH_DICES, true)) {
            return GameItem::TYPE_DICE;
        }
        if (in_array($gameId, self::GAMES_WITH_WHEEL_SECTORS, true)) {
            return GameItem::TYPE_WHEEL_SECTOR;
        }
        if ($gameId === GameDefinition::MATKA) {
            return GameItem::TYPE_MATKA;
        }

        throw new \InvalidArgumentException('UNKNOWN_GAME_TYPE_FOR_GAME_ITEM:' . $item->getId());
    }

    /**
     * @param Odd $odd
     * @param BetItem $item
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getColorByOdd(Odd $odd, BetItem $item): ?string
    {
        $gameItem = $item->getGameItem();

        if ($this->getType($gameItem) !== GameItem::TYPE_DICE) {
            return $gameItem->getColor();
        }

        if ($odd->getClass() === self::BLUE_DICE_ODDS_CLASS) {
            return self::DICE_BLUE_COLOR;
        }

        if ($odd->getClass() === self::RED_DICE_ODDS_CLASS) {
            return self::DICE_RED_COLOR;
        }

        if ($odd->getClass() === self::BLUE_AND_RED_DICE_ODDS_CLASS) {
            if ($item->getOrder() === 1) {
                return self::DICE_RED_COLOR;
            }
            if ($item->getOrder() === 2) {
                return self::DICE_BLUE_COLOR;
            }
            throw new \InvalidArgumentException('NO_ORDER_ON_BET_ITEM:' . $item->getId());
        }

        throw new \InvalidArgumentException('CANT_GET_DICE_COLOR_FOR_ODDS:' . $odd->getId());
    }

    /**
     * @param GameRunResultItem $item
     *
     * @return null|string
     * @throws \InvalidArgumentException
     */
    public function getColorByResult(GameRunResultItem $item): ?string
    {
        $gameItem = $item->getGameItem();

        // todo return actual color from game item once Dice Duel is migrated to RDSv2
        if ($this->getType($gameItem) !== GameItem::TYPE_DICE) {
            return $gameItem->getColor();
        }

        if ($item->getOrder() === 1) {
            return self::DICE_RED_COLOR;
        }
        if ($item->getOrder() === 2) {
            return self::DICE_BLUE_COLOR;
        }

        throw new \InvalidArgumentException('NO_ORDER_ON_ITEM: ' . $item->getId());
    }
}
