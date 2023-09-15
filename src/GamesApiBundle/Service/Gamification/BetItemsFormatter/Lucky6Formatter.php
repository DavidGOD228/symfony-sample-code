<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification\BetItemsFormatter;

use Acme\SymfonyDb\Entity\BetItem;

/**
 * Class Lucky6Formatter
 */
final class Lucky6Formatter implements FormatterInterface
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
            $order = $item->getOrder();
            $itemNumber = $item->getGameItem()->getNumber();

            $formattedItems[$order] = sprintf(
                '%s%s%s',
                $gameId,
                self::SEPARATOR,
                $itemNumber
            );
        }

        ksort($formattedItems);

        return $formattedItems;
    }
}
