<?php

namespace GamesApiBundle\Service\Gamification\BetItemsFormatter;

use Acme\SymfonyDb\Entity\BetItem;

/**
 * Interface FormatterInterface
 */
interface FormatterInterface
{
    public const SEPARATOR = '-';

    /**
     * @param BetItem[] $betItems
     *
     * @return array
     */
    public function format(iterable $betItems): array;
}
