<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Formatter;

use Acme\SymfonyDb\Entity\AbRunRoundCard;
use Acme\SymfonyDb\Entity\GameRun;
use GamesApiBundle\Service\CardFormatter;
use GamesApiBundle\Service\GameRunResults\Calculator\AndarBaharCalculator;
use GamesApiBundle\Service\GameRunResults\CardsSorter;

/**
 * Class AndarBaharFormatter
 */
final class AndarBaharFormatter implements FormatterInterface
{
    public const FIELD_WINNER = 'winner';

    private AndarBaharCalculator $calculator;
    private CardsSorter $sorter;

    /**
     * AndarBaharFormatter constructor.
     *
     * @param AndarBaharCalculator $calculator
     * @param CardsSorter $sorter
     */
    public function __construct(AndarBaharCalculator $calculator, CardsSorter $sorter)
    {
        $this->calculator = $calculator;
        $this->sorter = $sorter;
    }

    /**
     * @param GameRun $gameRun
     *
     * @return array
     */
    public function format(GameRun $gameRun): array
    {
        $cards = $gameRun->getAndarBaharRunRoundCards();
        $cards = $this->sorter->sortAndarBahar($cards);
        $result = $this->calculator->calculate($cards);
        $mappedCards = $this->mapCards($cards);

        $formatted = array_merge(
            $mappedCards,
            [
                self::FIELD_WINNER => $result->getWinners()[0],
            ],
        );

        return $formatted;
    }

    /**
     * @param AbRunRoundCard[] $cards
     *
     * @return array
     */
    private function mapCards(iterable $cards): array
    {
        $mapped = [];
        foreach ($cards as $card) {
            $dealtTo = $card->getDealtTo();
            if ($dealtTo === AbRunRoundCard::DEALT_TO_JOKER) {
                $mapped[$dealtTo] = CardFormatter::formatCard($card->getCard());
            } else {
                $mapped[$dealtTo][] = CardFormatter::formatCard($card->getCard());
            }
        }

        return $mapped;
    }
}
