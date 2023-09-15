<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Formatter;

use Acme\SymfonyDb\Entity\GameRun;

/**
 * Class NullFormatter
 *
 * While it's not implementation, but "stub", no need to use anything.
 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterface
 */
final class NullFormatter implements FormatterInterface
{
    /**
     * @param GameRun $gameRun
     *
     * @return array
     */
    public function format(GameRun $gameRun): array
    {
        return [];
    }
}
