<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service;

use Acme\SymfonyDb\Interfaces\BasicCardInterface;

/**
 * Class CardFormatter
 */
final class CardFormatter
{
    /**
     * Formats to two characters card's presentation.
     *
     * A clubs => Ac
     * t diamonds Td
     *
     * @param BasicCardInterface $card
     *
     * @return string
     */
    public static function formatCard(BasicCardInterface $card): string
    {
        $value = $card->getValue() === '10' ? 't' : substr($card->getValue(), 0, 1);
        return strtoupper($value) . substr($card->getSuit(), 0, 1);
    }
}
