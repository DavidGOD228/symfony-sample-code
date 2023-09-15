<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Calculator;

use Acme\BetOnPokerCalculator\Game\GameBetOnPoker;
use Acme\SymfonyDb\Interfaces\RoundItemInterface;
use GamesApiBundle\DataObject\GameRunResults\WinnerDTO;

/**
 * Class PokerCalculator
 */
final class PokerCalculator implements CardGameCalculatorInterface
{
    private GameBetOnPoker $calculator;

    /**
     * @param GameBetOnPoker $calculator
     */
    public function __construct(GameBetOnPoker $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @param RoundItemInterface[] $cards
     *
     * @return WinnerDTO
     *
     * @noinspection PhpDocMissingThrowsInspection - looks like no exception possible here.
     */
    public function calculate(iterable $cards): WinnerDTO
    {
        $cardIds = [];
        foreach ($cards as $card) {
            $cardIds[] = $card->getGameItem()->getId();
        }

        $outcome = $this->calculator->getOutcome($cardIds);

        $winners = [];
        foreach ($outcome['winners'] as $playerNumber) {
            $winners[] = (string) $playerNumber;
        }

        return new WinnerDTO($winners, [$outcome['hand']], [], null);
    }
}
