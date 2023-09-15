<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Formatter;

use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\WarRunCard;
use GamesApiBundle\Service\CardFormatter;
use GamesApiBundle\Service\GameRunResults\Calculator\WarOfBetsCalculator;

/**
 * Class WarOfBetsFormatter
 */
final class WarOfBetsFormatter implements FormatterInterface
{
    private WarOfBetsCalculator $calculator;

    /**
     * WarOfBetsFormatter constructor.
     *
     * @param WarOfBetsCalculator $calculator
     */
    public function __construct(WarOfBetsCalculator $calculator)
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
        $cards = $gameRun->getWarRunCards();
        $result = $this->calculator->calculate($cards);
        $mappedCards = $this->mapCards($cards);

        $formatted = array_merge(
            $mappedCards,
            [
                'winner' => $result->getWinners()[0]
            ]
        );

        return $formatted;
    }

    /**
     * @param WarRunCard[] $cards
     *
     * @return array
     */
    private function mapCards(iterable $cards): array
    {
        $mapped = [];
        foreach ($cards as $card) {
            $dealtTo = $card->getDealtTo();
            $mapped[$dealtTo] = CardFormatter::formatCard($card->getCard());
        }

        return $mapped;
    }
}
