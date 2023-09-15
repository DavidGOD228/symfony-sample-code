<?php

namespace GamesApiBundle\Service\GameRunResults\Formatter;

use Acme\SymfonyDb\Entity\GameRun;

/**
 * Interface FormatterInterface
 */
interface FormatterInterface
{
    /**
     * @param GameRun $gameRun
     *
     * @return array - any game-specific result structure.
     */
    public function format(GameRun $gameRun): array;
}
