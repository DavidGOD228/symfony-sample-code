<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification;

use Acme\SymfonyDb\Entity\Odd;

/**
 * Need to format oddClass because of Combinations feature
 * There are oddClasses which are the same between different games
 *
 * GameId prefix before oddClass helps to distinguish, when we have same classes from diff games
 */
final class OddClassFormatter
{
    private const SEPARATOR = '-';

    /**
     * @param Odd $odd
     *
     * @return string
     */
    public static function format(Odd $odd): string
    {
        return $odd->getGame()->getId() . self::SEPARATOR . $odd->getClass();
    }
}
