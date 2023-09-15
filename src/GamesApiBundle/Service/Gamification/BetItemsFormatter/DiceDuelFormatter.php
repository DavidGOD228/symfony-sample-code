<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification\BetItemsFormatter;

use Acme\SymfonyDb\Entity\BetItem;

/**
 * Class DiceDuelFormatter
 */
final class DiceDuelFormatter implements FormatterInterface
{
    private const RED_DICE = 'red';
    private const BLUE_DICE = 'blue';

    /**
     * @param BetItem[] $betItems
     *
     * @return array
     */
    public function format(iterable $betItems): array
    {
        $formattedItems = [];

        foreach ($betItems as $item) {
            $gameId = $item->getGameItem()->getGame()->getId();
            $itemNumber = $item->getGameItem()->getNumber();

            $formattedItems[] = sprintf(
                '%s%s%s',
                $gameId,
                self::SEPARATOR,
                $itemNumber,
            );
        }

        // We're adding color only when have exactly 2 dices selected
        if (count($formattedItems) == 2) {
            // We're not storing dice color in DB, color is known by item position
            // Reference: \GamesApiBundle\Service\Betting\BetItemsPresenter::getStringPresentation
            $formattedItems[0] .= self::SEPARATOR . self::RED_DICE;
            $formattedItems[1] .= self::SEPARATOR . self::BLUE_DICE;
        }

        return $formattedItems;
    }
}
