<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Formatter;

use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\HeadsUpRunCard;
use GamesApiBundle\Service\CardFormatter;
use GamesApiBundle\Service\GameRunResults\Calculator\HeadsUpCalculator;

/**
 * Class HeadsUpFormatter
 */
final class HeadsUpFormatter implements FormatterInterface
{
    public const FIELD_WINNER = 'winner';
    public const FIELD_COMBINATION = 'wonCombination';

    private HeadsUpCalculator $calculator;

    /**
     * HeadsUpFormatter constructor.
     *
     * @param HeadsUpCalculator $calculator
     */
    public function __construct(HeadsUpCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @param GameRun $gameRun
     *
     * @return array
     */
    public function format(GameRun $gameRun): array
    {
        $cards = $gameRun->getHeadsUpRunCards();
        $result = $this->calculator->calculate($cards);
        $mappedCards = $this->mapCards($cards);

        $formatted = array_merge(
            $mappedCards,
            [
                self::FIELD_WINNER => $result->getWinners()[0],
                self::FIELD_COMBINATION => $result->getCombinations()[0],
            ],
        );

        return $formatted;
    }

    /**
     * @param HeadsUpRunCard[] $cards
     */
    private function mapCards(iterable $cards): array
    {
        $mapped = [];
        foreach ($cards as $card) {
            $dealtTo = $card->getDealtTo();
            $mapped[$dealtTo][] = CardFormatter::formatCard($card->getCard());
        }

        return $mapped;
    }
}
