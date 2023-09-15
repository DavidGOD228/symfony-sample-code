<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Calculator;

use Acme\SymfonyDb\Entity\BaccaratRunCard;
use Acme\SymfonyDb\Type\DealedToType;
use GamesApiBundle\DataObject\GameRunResults\WinnerDTO;
use GamesApiBundle\Service\GameRunResults\BaccaratSideOddsProvider;

/**
 * Class BaccaratCalculator
 *
 * ToDo replace with lib after https://jira.Acme.tv/browse/STU-2205
 */
final class BaccaratCalculator implements CardGameCalculatorInterface
{
    /**
     * Linked to translations, manage it carefully.
     */
    private const BACCARAT_TIE = 'tie';

    private BaccaratSideOddsProvider $sideOddsProvider;

    /**
     * BaccaratCalculator constructor.
     *
     * @param BaccaratSideOddsProvider $sideOddsProvider
     */
    public function __construct(BaccaratSideOddsProvider $sideOddsProvider)
    {
        $this->sideOddsProvider = $sideOddsProvider;
    }

    /**
     * @param BaccaratRunCard[] $cards
     *
     * @return WinnerDTO
     *
     * @see https://en.wikipedia.org/wiki/Baccarat_(card_game)
     */
    public function calculate(iterable $cards): WinnerDTO
    {
        $sides = [];
        foreach ($cards as $card) {
            $sides[] = $card->getDealtTo();
        }
        $sides = array_unique($sides);
        // array_unique not resetting keys: [0]=> "dealer", [3]=> "player", resetting.
        $sides = array_values($sides);

        [$side1, $side2] = $sides;

        $scores = [
            $side1 => 0,
            $side2 => 0,
        ];

        foreach ($cards as $card) {
            $side = $card->getDealtTo();
            $scores[$side] += $card->getCard()->getScore();
        }
        foreach ($scores as $side => $score) {
            // According to rules, "The highest possible hand value in baccarat is therefore nine."
            $scores[$side] %= 10;
        }

        if ($scores[$side1] > $scores[$side2]) {
            $winner = $side1;
        } elseif ($scores[$side1] < $scores[$side2]) {
            $winner = $side2;
        } else {
            $winner = self::BACCARAT_TIE;
        }

        $combinations = $this->calculateCombinations($cards);
        $sideOddsIdsMap = $this->sideOddsProvider->getSideOddsIdsMap();
        $sideOddsIds = [];
        foreach ($combinations as $combination) {
            $sideOddsIds[] = $sideOddsIdsMap[$combination];
        }

        return new WinnerDTO([$winner], $sideOddsIds, [], null);
    }

    /**
     * @param BaccaratRunCard[] $cards
     *
     * @return string[]
     */
    public function calculateCombinations(iterable $cards): array
    {
        $playerCards = [];
        $bankerCards = [];
        foreach ($cards as $card) {
            if ($card->getDealtTo() === DealedToType::PLAYER) {
                $playerCards[] = $card->getCard();
            } else {
                $bankerCards[] = $card->getCard();
            }
        }

        // player pair
        $anyPair = false;
        $perfectPair = false;
        if ($playerCards[0]->getValue() === $playerCards[1]->getValue()) {
            $combinations[] = BaccaratSideOddsProvider::CLASS_PLAYER_PAIR;
            $anyPair = true;
            if ($playerCards[0]->getSuit() === $playerCards[1]->getSuit()) {
                $perfectPair = true;
            }
        }
        // banker pair
        if ($bankerCards[0]->getValue() === $bankerCards[1]->getValue()) {
            $combinations[] = BaccaratSideOddsProvider::CLASS_BANKER_PAIR;
            $anyPair = true;
            if ($bankerCards[0]->getSuit() === $bankerCards[1]->getSuit()) {
                $perfectPair = true;
            }
        }
        if ($anyPair) {
            $combinations[] = BaccaratSideOddsProvider::CLASS_ANY_PAIR;
        }
        if ($perfectPair) {
            $combinations[] = BaccaratSideOddsProvider::CLASS_PERFECT_PAIR;
        }

        if (count($playerCards) === 2 && count($bankerCards) === 2) {
            $combinations[] = BaccaratSideOddsProvider::CLASS_SMALL_HAND;
        } else {
            $combinations[] = BaccaratSideOddsProvider::CLASS_BIG_HAND;
        }

        return $combinations;
    }
}
