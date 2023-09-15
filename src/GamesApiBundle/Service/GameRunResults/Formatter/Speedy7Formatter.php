<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Formatter;

use Acme\SymfonyDb\Entity\GameRun;
use GamesApiBundle\Service\CardFormatter;

/**
 * Class Speedy7Formatter
 */
final class Speedy7Formatter implements FormatterInterface
{
    public const FIELD_ROUNDS = 'rounds';

    /**
     * @param GameRun $gameRun
     *
     * @return array[]
     */
    public function format(GameRun $gameRun): array
    {
        $formatted = [
            self::FIELD_ROUNDS => []
        ];

        foreach ($gameRun->getSpeedy7RunRound() as $runRound) {
            $card = $runRound->getCard();

            // For speedy7 could be valid result without all cards.
            // One of cases when draw was cancelled, but should be shown because it has some cards.
            // And because of pay-out specific of speedy7 it's possible to have winning bets on such game runs.
            if ($card) {
                $formattedCard = CardFormatter::formatCard($card);
            } else {
                $formattedCard = null;
            }

            $formatted[self::FIELD_ROUNDS][$runRound->getRoundNumber()] = $formattedCard;
        }

        return $formatted;
    }
}
