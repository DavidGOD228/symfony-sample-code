<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Calculator;

use Acme\SymfonyDb\Entity\WarRunCard;
use GamesApiBundle\DataObject\GameRunResults\WinnerDTO;

/**
 * Class WarOfBetsCalculator
 */
final class WarOfBetsCalculator implements CardGameCalculatorInterface
{
    /**
     * Linked to translations, manage it carefully.
     */
    private const WAR_TIE = 'war';

    /**
     * @param WarRunCard[] $cards
     *
     * @return WinnerDTO
     */
    public function calculate(iterable $cards): WinnerDTO
    {
        if ($cards[0]->getCard()->getScore() > $cards[1]->getCard()->getScore()) {
            $winner = $cards[0]->getDealtTo();
        } elseif ($cards[0]->getCard()->getScore() < $cards[1]->getCard()->getScore()) {
            $winner = $cards[1]->getDealtTo();
        } else {
            $winner = self::WAR_TIE;
        }

        return new WinnerDTO([$winner], [], [], null);
    }
}
