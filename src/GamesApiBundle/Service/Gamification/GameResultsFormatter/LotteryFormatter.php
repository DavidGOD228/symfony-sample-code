<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\Gamification\GameResultsFormatter;

use Acme\SymfonyDb\Entity\GameItem;
use GamesApiBundle\DataObject\RecentBet\Component\RecentBetDataItem;

/**
 * Class LotteryFormatter
 */
class LotteryFormatter implements FormatterInterface
{
    private const TYPES = [
        GameItem::TYPE_BALL => 'ball',
        GameItem::TYPE_DICE => 'dice',
        GameItem::TYPE_WHEEL_SECTOR => 'wheel-sector',
    ];

    /**
     * @param RecentBetDataItem[] $results
     *
     * @return array
     */
    public function format(array $results): array
    {
        $formattedResults = [];
        foreach ($results as $result) {
            $typeId = $result->type;
            $type = self::TYPES[$typeId];
            $number = $result->number;
            $color = $result->color;

            $formattedResults[] = sprintf(
                '%s%s%s%s%s',
                $type,
                self::SEPARATOR,
                $number,
                self::SEPARATOR,
                $color
            );
        }

        return $formattedResults;
    }
}
