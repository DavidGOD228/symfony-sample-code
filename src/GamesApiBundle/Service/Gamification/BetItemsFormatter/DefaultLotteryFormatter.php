<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification\BetItemsFormatter;

use Acme\SymfonyDb\Entity\BetItem;

/**
 * Class DefaultLotteryFormatter
 */
final class DefaultLotteryFormatter implements FormatterInterface
{
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
                $itemNumber
            );
        }

        return $formattedItems;
    }
}
