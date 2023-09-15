<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\RecentBets;

use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Combination;
use Acme\SymfonyDb\Entity\Subscription;
use GamesApiBundle\Service\RecentBet\Repository\RecentBetRepository;

/**
 * Class RecentBetRepositoryMock
 *
 * For mock classes no need to use all arguments.
 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassAfterLastUsed
 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassBeforeLastUsed
 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClass
 */
final class RecentBetRepositoryMock extends RecentBetRepository
{
    /** @var Bet[] */
    private array $recentBets = [];
    private array $recentBetsOfAllTypes = [];
    /** @var Bet[] */
    private array $betsForNonSingleBet = [];

    /**
     * RecentBetRepositoryMock constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param int $playerId
     * @param int $limit
     * @param array|null $gamesIds
     *
     * @return array
     */
    public function getRecentBets(int $playerId, int $limit, ?array $gamesIds): array
    {
        if ($gamesIds) {
            $this->recentBets = array_filter($this->recentBets, function ($bet) use ($gamesIds) {
                return in_array($bet->getOdd()->getGame()->getId(), $gamesIds);
            });
        }

        return $this->recentBets;
    }

    /**
     * @param array $recentBets
     */
    public function setRecentBets(array $recentBets): void
    {
        $this->recentBets = $recentBets;
    }

    /**
     * @param array $recentBets
     */
    public function addRecentBets(array $recentBets): void
    {
        $this->recentBets = array_merge($this->recentBets, $recentBets);
    }

    /**
     * @param int $playerId
     * @param int $limit
     * @param array|null $gamesIds
     *
     * @return array
     */
    public function getRecentBetsOfAllTypes(int $playerId, int $limit, ?array $gamesIds): array
    {
        return $this->recentBetsOfAllTypes;
    }

    /**
     * @param array $recentBets
     */
    public function setRecentBetsOfAllTypes(array $recentBets): void
    {
        $this->recentBetsOfAllTypes = $recentBets;
    }

    /**
     * @param string $class
     * @param array $ids
     *
     * @return array
     */
    public function fetchBetsForNonSingleBet(string $class, array $ids): array
    {
        $notSingleBets = [];
        foreach ($this->betsForNonSingleBet as $notSingleBet) {
            $subscription = $notSingleBet->getSubscription();
            $combination = $notSingleBet->getCombination();
            if ($class === Subscription::class && $subscription) {
                if (in_array($subscription->getId(), $ids, true)) {
                    $notSingleBets[] = $notSingleBet;
                }
            } elseif ($class === Combination::class && $combination) {
                if (in_array($combination->getId(), $ids, true)) {
                    $notSingleBets[] = $notSingleBet;
                }
            }
        }

        return $notSingleBets;
    }

    /**
     * @param array $recentBets
     */
    public function setFetchBetsForNonSingleBet(array $recentBets): void
    {
        $this->betsForNonSingleBet = $recentBets;
    }

    /**
     * @param int $betId
     * @param string $entityClass
     *
     * @return object|null
     */
    public function getBet(int $betId, string $entityClass = Bet::class): ?object
    {
        foreach ($this->recentBets as $bet) {
            if ($bet instanceof $entityClass && $bet->getId() === $betId) {
                return $bet;
            }
        }

        return null;
    }

    /**
     * @param int $playerId
     * @param int $gameId
     * @param int $limit
     * @param bool $isWidget
     *
     * @return array
     */
    public function getRecentBetsByGameAndPlatform(int $playerId, int $gameId, int $limit, bool $isWidget): array
    {
        $bets = [];
        foreach ($this->recentBets as $bet) {
            if ($bet->getIsWidget() === $isWidget) {
                $bets[] = $bet;
            }
        }

        return $bets;
    }
}
