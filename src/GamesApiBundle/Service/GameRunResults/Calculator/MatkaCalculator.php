<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Calculator;

use Acme\MatkaOutcomeCalculator\MatkaOutcomeCalculator;
use Acme\SymfonyDb\Entity\MatkaRunCard;
use GamesApiBundle\DataObject\GameRunResults\WinnerDTO;

/**
 * Class MatkaCalculator
 */
final class MatkaCalculator implements CardGameCalculatorInterface
{
    private MatkaOutcomeCalculator $calculator;

    /**
     * @param MatkaOutcomeCalculator $calculator
     */
    public function __construct(MatkaOutcomeCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @param MatkaRunCard[] $cards
     *
     * @return WinnerDTO
     */
    public function calculate(iterable $cards): WinnerDTO
    {
        $values = [];

        foreach ($cards as $card) {
            $values[] = $card->getCard()->getValue();
        }

        $winner = $this->calculator->calculateOutcome($values);

        return new WinnerDTO($winner, [], [], null);
    }
}
