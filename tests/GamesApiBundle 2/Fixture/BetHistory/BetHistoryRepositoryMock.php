<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\BetHistory;

use DateTimeImmutable;
use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Player;
use GamesApiBundle\Service\BetHistory\BetHistoryRepository;

/**
 * Class BetHistoryRepositoryMock
 *
 * For mock classes no need to use all arguments.
 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassAfterLastUsed
 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassBeforeLastUsed
 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClass
 */
final class BetHistoryRepositoryMock extends BetHistoryRepository
{
    /**
     * @var Bet[]
     */
    private array $bets = [];

    /**
     * BetHistoryRepositoryMock constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param Player $player
     * @param DateTimeImmutable $from
     * @param DateTimeImmutable $till
     * @param string|null $type
     * @param array|null $gamesIds
     *
     * @return Bet[]
     */
    public function getBets(
        Player $player,
        DateTimeImmutable $from,
        DateTimeImmutable $till,
        ?string $type,
        ?array $gamesIds
    ): array
    {
        $foundBets = [];

        foreach ($this->bets as $bet) {
            if (!$type) {
                $foundBets[] = $bet;
            } elseif ($type === 'subscription' && $bet->getSubscription()) {
                $foundBets[] = $bet;
            } elseif ($type === 'combination' && $bet->getCombination()) {
                $foundBets[] = $bet;
            } elseif ($type === 'single' && !$bet->getCombination() && !$bet->getSubscription()) {
                $foundBets[] = $bet;
            } elseif ($type === 'bazaar' && $bet->getBazaarBet()) {
                $foundBets[] = $bet;
            }
        }

        if ($gamesIds) {
            $foundBets = array_filter($foundBets, function ($bet) use ($gamesIds) {
                return in_array($bet->getOdd()->getGame()->getId(), $gamesIds);
            });
        }

        return $foundBets;
    }

    /**
     * @param Player $player
     * @param string $type
     * @param int $id
     *
     * @return Bet|null
     */
    public function getSingle(Player $player, string $type, int $id): ?Bet
    {
        foreach ($this->bets as $bet) {
            if ($type === 'subscription' && $bet->getSubscription()->getId() === $id) {
                return $bet;
            } elseif ($type === 'combination' && $bet->getCombination()->getId() === $id) {
                return $bet;
            } elseif ($type === 'single' && $bet->getId() === $id) {
                return $bet;
            }
        }

        return null;
    }

    /**
     * @param array $bets
     */
    public function setBets(array $bets): void
    {
        $this->bets = $bets;
    }

    /**
     * @param array $bets
     */
    public function addBets(array $bets): void
    {
        $this->bets = array_merge($this->bets, $bets);
    }
}
