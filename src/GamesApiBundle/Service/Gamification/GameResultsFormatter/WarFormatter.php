<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\Gamification\GameResultsFormatter;

/**
 * Class WarFormatter
 */
class WarFormatter implements FormatterInterface
{
    /**
     * @param array $results
     *
     * @return array
     */
    public function format(array $results): array
    {
        $formattedResults = [];
        foreach ($results as $dealtTo => $card) {
            $formattedResults[] = $dealtTo . self::SEPARATOR . $card;
        }

        return $formattedResults;
    }
}