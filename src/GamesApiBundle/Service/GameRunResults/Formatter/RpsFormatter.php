<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Formatter;

use Acme\SymfonyDb\Entity\GameRun;
use GamesApiBundle\Service\RPSCardFormatter;

/**
 * Class RpsFormatter
 */
final class RpsFormatter implements FormatterInterface
{
    /**
     * @param GameRun $gameRun
     *
     * @return array
     */
    public function format(GameRun $gameRun): array
    {
        $results = [];
        foreach ($gameRun->getRpsRunRoundCard() as $runCard) {
            $formattedCard = RPSCardFormatter::formatCard($runCard->getGameItem()->getUniqueValue());
            $results[$runCard->getDealtTo()] = $formattedCard;
        }

        return $results;
    }
}
