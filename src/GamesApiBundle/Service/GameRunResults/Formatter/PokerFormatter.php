<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Formatter;

use Acme\SymfonyDb\Entity\GameRun;

/**
 * Class PokerFormatter
 */
final class PokerFormatter implements FormatterInterface
{
    private CommonPokerFormatter $formatter;

    /**
     * StsPokerFormatter constructor.
     *
     * @param CommonPokerFormatter $formatter
     */
    public function __construct(CommonPokerFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * @param GameRun $gameRun
     *
     * @return array
     */
    public function format(GameRun $gameRun): array
    {
        $cards = $gameRun->getPokerRunCards();

        $formatted = $this->formatter->format($cards);

        return $formatted;
    }
}
