<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\Gamification\GameResultsFormatter;

/**
 * Class RpsFormatter
 */
class RpsFormatter implements FormatterInterface
{
    /**
     * @param array $results
     *
     * @return array
     */
    public function format(array $results): array
    {
        $formattedResults = [];
        foreach ($results as $zone => $card) {
            $formattedResults[] = $zone . self::SEPARATOR . $card;
        }

        return $formattedResults;
    }
}