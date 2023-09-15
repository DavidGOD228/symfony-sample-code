<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification\BetItemsFormatter;

use Acme\SymfonyDb\Entity\BetItem;

/**
 * Class NullFormatter
 *
 * While it's not implementation, but "stub", no need to use anything.
 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInImplementedInterface
 */
final class NullFormatter implements FormatterInterface
{
    /**
     * @param BetItem[] $betItems
     *
     * @return array
     */
    public function format(iterable $betItems): array
    {
        return [];
    }
}
