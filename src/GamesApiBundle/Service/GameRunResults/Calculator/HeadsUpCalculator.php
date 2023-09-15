<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Calculator;

use Acme\PokerCalculator\Game\Card;
use Acme\PokerCalculator\Game\Cards;
use Acme\PokerCalculator\Game\GamePokerHeadsup;
use Acme\SymfonyDb\Entity\HeadsUpRunCard;
use Acme\SymfonyDb\Type\DealedToType;
use GamesApiBundle\DataObject\GameRunResults\WinnerDTO;

/**
 * Class HeadsUpCalculator
 */
final class HeadsUpCalculator implements CardGameCalculatorInterface
{
    private GamePokerHeadsup $calculator;

    /**
     * @param GamePokerHeadsup $calculator
     */
    public function __construct(GamePokerHeadsup $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @param HeadsUpRunCard[] $cards
     *
     * @return WinnerDTO
     *
     * @noinspection PhpDocMissingThrowsInspection - looks like no exception possible here.
     */
    public function calculate(iterable $cards): WinnerDTO
    {
        $playerCards = new Cards();
        $dealerCards = new Cards();
        $boardCards = new Cards();

        foreach ($cards as $card) {
            $emsCard = new Card($card->getCard()->getId());
            switch ($card->getDealtTo()) {
                case DealedToType::PLAYER:
                    $playerCards->addCard($emsCard);
                    break;
                case DealedToType::DEALER:
                    $dealerCards->addCard($emsCard);
                    break;
                default:
                    $boardCards->addCard($emsCard);
                    break;
            }
        }

        $outcome = $this->calculator->getOutcome($playerCards, $dealerCards, $boardCards);
        $combinationCards = $outcome->getCombinationCards();

        $winner = strtolower($outcome->getOutcome());
        $wonHand = strtolower($this->calculator->getCombinationName($outcome->getCombinationId()));

        return new WinnerDTO([$winner], [$outcome->getCombinationId()], $combinationCards, $wonHand);
    }
}
