<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Calculator;

use Acme\SymfonyDb\Interfaces\RoundItemInterface;
use GamesApiBundle\DataObject\GameRunResults\WinnerDTO;

/**
 * Interface CardGameCalculatorInterface
 */
interface CardGameCalculatorInterface
{
    /**
     * @param RoundItemInterface[] $cards
     *
     * @return WinnerDTO
     */
    public function calculate(iterable $cards): WinnerDTO;
}
