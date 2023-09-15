<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\Gamification\GameResultsFormatter;

use Acme\SymfonyDb\Entity\AbRunRoundCard;
use GamesApiBundle\Service\GameRunResults\Formatter\AndarBaharFormatter as BaseResultFormatter;

/**
 * Class AndarBaharFormatter
 */
class AndarBaharFormatter implements FormatterInterface
{
    private const FIELD_WINNER = BaseResultFormatter::FIELD_WINNER;

    /**
     * @param array $results
     *
     * @return array
     */
    public function format(array $results): array
    {
        $formattedResults = [];

        $dealtToAndar = AbRunRoundCard::DEALT_TO_ANDAR;
        $formattedResults = array_merge(
            $formattedResults,
            $this->formatCards($dealtToAndar, $results)
        );

        $dealtToBahar = AbRunRoundCard::DEALT_TO_BAHAR;
        $formattedResults = array_merge(
            $formattedResults,
            $this->formatCards($dealtToBahar, $results)
        );

        $dealtToJoker = AbRunRoundCard::DEALT_TO_JOKER;
        $formattedResults[] = $dealtToJoker . self::SEPARATOR . $results[$dealtToJoker];

        $formattedResults[] = self::FIELD_WINNER . self::SEPARATOR . $results[self::FIELD_WINNER];

        return $formattedResults;
    }

    /**
     * @param string $dealtTo
     * @param array $results
     *
     * @return array
     */
    private function formatCards(string $dealtTo, array $results): array
    {
        $formattedCards = [];

        // There are cases when some side does not get any cards
        if (array_key_exists($dealtTo, $results)) {
            foreach ($results[$dealtTo] as $card) {
                $formattedCards[] = $dealtTo . self::SEPARATOR . $card;
            }
        }

        return $formattedCards;
    }
}
