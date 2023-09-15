<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\Gamification\GameResultsFormatter;

/**
 * Class NullFormatter
 *
 * While it's not implementation, but "stub", no need to use anything.
 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterface
 */
final class NullFormatter implements FormatterInterface
{
    /**
     * @param array $results
     *
     * @return array
     */
    public function format(array $results): array
    {
        return [];
    }
}