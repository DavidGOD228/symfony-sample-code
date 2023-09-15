<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Formatter;

use Acme\SymfonyDb\Entity\BaccaratRunCard;
use Acme\SymfonyDb\Entity\GameRun;
use GamesApiBundle\Service\CardFormatter;
use GamesApiBundle\Service\GameRunResults\Calculator\BaccaratCalculator;

/**
 * Class BaccaratFormatter
 */
final class BaccaratFormatter implements FormatterInterface
{
    public const FIELD_SIDE_ODDS = 'wonSideOdds';
    public const FIELD_CARD = 'card';
    public const FIELD_WINNER = 'winner';

    private BaccaratCalculator $calculator;

    /**
     * BaccaratFormatter constructor.
     *
     * @param BaccaratCalculator $calculator
     */
    public function __construct(BaccaratCalculator $calculator)
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
        $cards = $gameRun->getBaccaratRunCards();
        $results = $this->calculator->calculate($cards);

        $mappedCards = $this->mapCards($cards);

        $formatted = array_merge(
            $mappedCards,
            [
                'winner' => $results->getWinners()[0],
                self::FIELD_SIDE_ODDS => $results->getCombinations(),
            ],
        );

        return $formatted;
    }

    /**
     * @param BaccaratRunCard[] $cards
     */
    private function mapCards(iterable $cards): array
    {
        $mapped = [];
        foreach ($cards as $card) {
            $mapped[$card->getDealtTo()][] = [
                self::FIELD_CARD => CardFormatter::formatCard($card->getCard()),
                'score' => $card->getCard()->getScore(),
            ];
        }

        return $mapped;
    }
}
