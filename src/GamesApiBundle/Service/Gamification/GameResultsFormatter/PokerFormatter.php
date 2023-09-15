<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\Gamification\GameResultsFormatter;

use Acme\BetOnPokerCalculator\Exception\InputException;
use Acme\BetOnPokerCalculator\Game\GameBetOnPoker;
use GamesApiBundle\Service\GameRunResults\Formatter\CommonPokerFormatter as BaseResultFormatter;

/**
 * Class PokerFormatter
 */
class PokerFormatter implements FormatterInterface
{
    private const FIELD_HANDS = BaseResultFormatter::FIELD_HANDS;
    private const FIELD_TABLE = BaseResultFormatter::FIELD_TABLE;
    private const FIELD_WON_HANDS = BaseResultFormatter::FIELD_WON;
    private const FIELD_WON_COMBINATION = BaseResultFormatter::FIELD_COMBINATION;

    private const FIELD_HAND = 'hand';

    private const COMBINATION_CLASS_MAP = [
        GameBetOnPoker::HAND_HIGH_CARD => 'HIGH_CARD',
        GameBetOnPoker::HAND_ONE_PAIR => 'ONE_PAIR',
        GameBetOnPoker::HAND_TWO_PAIRS => 'TWO_PAIRS',
        GameBetOnPoker::HAND_THREE_OF_A_KIND => 'THREE_OF_A_KIND',
        GameBetOnPoker::HAND_STRAIGHT => 'STRAIGHT',
        GameBetOnPoker::HAND_FLUSH => 'FLUSH',
        GameBetOnPoker::HAND_FULLHOUSE => 'FULL_HOUSE',
        GameBetOnPoker::HAND_FOUR_OF_A_KIND => 'FOUR_OF_A_KIND',
        GameBetOnPoker::HAND_STRAIGHT_FLUSH => 'STRAIGHT_FLUSH',
        GameBetOnPoker::HAND_ROYAL_FLUSH => 'ROYAL_FLUSH',
    ];

    /**
     * @param array $results
     *
     * @return array
     *
     * @throws InputException
     */
    public function format(array $results): array
    {
        $formattedResults = [];
        foreach ($results[self::FIELD_HANDS] as $hand => $cards) {
            foreach ($cards as $card) {
                $formattedResults[] = self::FIELD_HAND . $hand . self::SEPARATOR . $card;
            }
        }

        foreach ($results[self::FIELD_TABLE] as $card) {
            $formattedResults[] = self::FIELD_TABLE . self::SEPARATOR . $card;
        }

        foreach ($results[self::FIELD_WON_HANDS] as $wonHand) {
            $formattedResults[] = self::FIELD_WON_HANDS . self::SEPARATOR . $wonHand;
        }

        $formattedResults[] = sprintf(
            '%s%s%s',
            self::FIELD_WON_COMBINATION,
            self::SEPARATOR,
            self::COMBINATION_CLASS_MAP[$results[self::FIELD_WON_COMBINATION]],
        );

        return $formattedResults;
    }
}
