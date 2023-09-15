<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Formatter;

use Acme\SymfonyDb\Entity\PokerRunCard;
use Acme\SymfonyDb\Entity\StsPokerRunCard;
use Acme\SymfonyDb\Interfaces\RoundItemInterface;
use Acme\SymfonyDb\Type\PokerDealtToType;
use GamesApiBundle\Service\CardFormatter;
use GamesApiBundle\Service\GameRunResults\Calculator\PokerCalculator;
use GamesApiBundle\Service\GameRunResults\CardsSorter;

/**
 * Class CommonPokerFormatter
 */
final class CommonPokerFormatter
{
    public const FIELD_HANDS =  'hands';
    public const FIELD_TABLE = 'table';
    public const FIELD_WON = 'wonHands';
    public const FIELD_COMBINATION = 'wonCombination';

    private CardsSorter $sorter;
    private PokerCalculator $calculator;

    /**
     * PokerFormatter constructor.
     *
     * @param CardsSorter $sorter
     * @param PokerCalculator $calculator
     */
    public function __construct(
        CardsSorter $sorter,
        PokerCalculator $calculator
    )
    {
        $this->sorter = $sorter;
        $this->calculator = $calculator;
    }

    /**
     * @param RoundItemInterface[] $cards
     *
     * @return array
     */
    public function format($cards): array
    {
        $cards = $this->sorter->sortPoker($cards);
        $winner = $this->calculator->calculate($cards);

        [$hands, $table] = $this->mapCards($cards);

        return [
            self::FIELD_HANDS => $hands,
            self::FIELD_TABLE => $table,
            self::FIELD_WON => $winner->getWinners(),
            self::FIELD_COMBINATION => $winner->getCombinations()[0],
        ];
    }

    /**
     * @param PokerRunCard[]|StsPokerRunCard[] $cards
     *
     * @return RoundItemInterface[]
     */
    private function mapCards(array $cards): array
    {
        $hands = [];
        $table = [];

        foreach ($cards as $card) {
            $dealtTo = str_replace('player_', '', $card->getDealtTo());
            $formatted = CardFormatter::formatCard($card->getCard());
            if ($dealtTo === PokerDealtToType::BOARD) {
                $table[] = $formatted;
            } else {
                $hands[$dealtTo][] = $formatted;
            }
        }

        return [$hands, $table];
    }
}
