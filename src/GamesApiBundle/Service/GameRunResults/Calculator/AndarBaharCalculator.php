<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Calculator;

use Acme\SymfonyDb\Entity\AbRunRoundCard;
use GamesApiBundle\DataObject\GameRunResults\WinnerDTO;

/**
 * Class AndarBaharCalculator
 */
final class AndarBaharCalculator implements CardGameCalculatorInterface
{
    /**
     * @param AbRunRoundCard[] $cards
     *
     * @return WinnerDTO
     */
    public function calculate(iterable $cards): WinnerDTO
    {
        $joker = null;
        $andar = [];
        $bahar = [];

        foreach ($cards as $card) {
            if ($card->getDealtTo() === AbRunRoundCard::DEALT_TO_JOKER) {
                $joker = $card;
            } elseif ($card->getDealtTo() === AbRunRoundCard::DEALT_TO_ANDAR) {
                $andar[] = $card;
            } else {
                $bahar[] = $card;
            }
        }

        $winner = null;
        $jokerValue = $joker->getCard()->getValue();
        if ($andar) {
            $lastAndar = end($andar);
            if ($lastAndar->getCard()->getValue() === $jokerValue) {
                $winner = AbRunRoundCard::DEALT_TO_ANDAR;
            }
        }

        if (!$winner && $bahar) {
            $lastBahar = end($bahar);
            if ($lastBahar->getCard()->getValue() === $jokerValue) {
                $winner = AbRunRoundCard::DEALT_TO_BAHAR;
            }
        }

        return new WinnerDTO([$winner], [], [], null);
    }
}
