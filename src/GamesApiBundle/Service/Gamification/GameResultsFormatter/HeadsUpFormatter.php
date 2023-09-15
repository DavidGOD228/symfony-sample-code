<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\Gamification\GameResultsFormatter;

use Acme\PokerCalculator\Exception\InputException;
use Acme\PokerCalculator\Game\GamePokerHeadsup;
use Acme\SymfonyDb\Type\DealedToType;
use \GamesApiBundle\Service\GameRunResults\Formatter\HeadsUpFormatter as BaseResultFormatter;

/**
 * Class HeadsUpFormatter
 */
class HeadsUpFormatter implements FormatterInterface
{
    private const FIELD_PLAYER = DealedToType::PLAYER;
    private const FIELD_DEALER = DealedToType::DEALER;
    private const FIELD_BOARD = DealedToType::BOARD;
    private const FIELD_WINNER = BaseResultFormatter::FIELD_WINNER;
    private const FIELD_WON_COMBINATION = BaseResultFormatter::FIELD_COMBINATION;

    /**
     * @param array $results
     *
     * @return array
     *
     * @throws InputException
     */
    public function format(array $results): array
    {
        $gamePoker = new GamePokerHeadsup();

        foreach ($results[self::FIELD_PLAYER] as $card) {
            $formattedResults[] = self::FIELD_PLAYER . self::SEPARATOR . $card;
        }

        foreach ($results[self::FIELD_DEALER] as $card) {
            $formattedResults[] = self::FIELD_DEALER . self::SEPARATOR . $card;
        }

        foreach ($results[self::FIELD_BOARD] as $card) {
            $formattedResults[] = self::FIELD_BOARD . self::SEPARATOR . $card;
        }

        $formattedResults[] = self::FIELD_WINNER . self::SEPARATOR . $results[self::FIELD_WINNER];
        $formattedResults[] = sprintf(
            '%s%s%s',
            self::FIELD_WON_COMBINATION,
            self::SEPARATOR,
            $gamePoker->getCombinationName($results[self::FIELD_WON_COMBINATION])
        );

        return $formattedResults;
    }
}
