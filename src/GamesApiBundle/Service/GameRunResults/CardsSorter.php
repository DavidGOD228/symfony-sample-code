<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults;

use Acme\SymfonyDb\Entity\AbRunRoundCard;
use Acme\SymfonyDb\Interfaces\RoundItemInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Class CardsSorter
 */
final class CardsSorter
{
    /**
     * While Poker don't have dealtTo property, to calculate it, it's important to have original cards order.
     * For other games sorting is optional, applying it for order consistency.
     *
     * @param RoundItemInterface[]|Collection $cards
     *
     * @return RoundItemInterface[]
     */
    public function sortPoker(iterable $cards): array
    {
        if (!is_array($cards)) {
            $cards = iterator_to_array($cards);
        }

        usort(
            $cards,
            static fn(RoundItemInterface $item1, RoundItemInterface $item2) => $item1->getId() <=> $item2->getId()
        );

        return $cards;
    }

    /**
     * While Poker don't have dealtTo property, to calculate it, it's important to have original cards order.
     * For other games sorting is optional, applying it for order consistency.
     *
     * @param RoundItemInterface[]|Collection $cards
     *
     * @return RoundItemInterface[]
     */
    public function sortAndarBahar(iterable $cards): array
    {
        if (!is_array($cards)) {
            $cards = iterator_to_array($cards);
        }

        usort(
            $cards,
            static fn(AbRunRoundCard $item1, AbRunRoundCard $item2) => $item1->getNumber() <=> $item2->getNumber()
        );

        return $cards;
    }
}
