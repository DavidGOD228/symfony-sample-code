<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification;

use Acme\SymfonyDb\Entity\Bet;

/**
 * Class BetItemsBuilder
 */
final class BetItemsBuilder
{
    private BetItemsFormatterFactory $formatterFactory;

    /**
     * BetItemsBuilder constructor.
     *
     * @param BetItemsFormatterFactory $formatterFactory
     */
    public function __construct(BetItemsFormatterFactory $formatterFactory)
    {
        $this->formatterFactory = $formatterFactory;
    }

    /**
     * @param Bet[] $bets
     *
     * @return array
     */
    public function build(iterable $bets): array
    {
        $items = [];

        foreach ($bets as $bet) {
            $gameId = $bet->getOdd()->getGame()->getId();
            $formatter = $this->formatterFactory->getFormatter($gameId);
            $rawItems = $bet->getItems();

            $items = array_merge(
                $items,
                $formatter->format($rawItems)
            );
        }

        return $items;
    }
}
