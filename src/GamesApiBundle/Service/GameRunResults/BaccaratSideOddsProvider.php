<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\Odd;
use CoreBundle\Service\CacheService;
use CoreBundle\Service\RepositoryProviderInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Class BaccaratSideOddsProvider
 */
final class BaccaratSideOddsProvider
{
    private const CACHE_KEY = 'baccarat:side-odds';

    // Hardcoded odds classes - for usage in calculation.
    public const CLASS_PLAYER_PAIR = 'PAIR_PLAYER';
    public const CLASS_BANKER_PAIR = 'PAIR_BANKER';
    public const CLASS_ANY_PAIR = 'PAIR_ANY';
    public const CLASS_PERFECT_PAIR = 'PAIR_PERFECT';
    public const CLASS_SMALL_HAND = 'HAND_SMALL';
    public const CLASS_BIG_HAND = 'HAND_BIG';

    private CacheService $cacheService;
    private EntityRepository $oddsRepository;

    /**
     * BaccaratSideOddsProvider constructor.
     *
     * @param CacheService $cacheService
     * @param RepositoryProviderInterface $repositoryProvider
     */
    public function __construct(
        CacheService $cacheService,
        RepositoryProviderInterface $repositoryProvider
    )
    {
        $this->cacheService = $cacheService;
        $this->oddsRepository = $repositoryProvider->getSlaveRepository(Odd::class);
    }

    /**
     * @return array<string, int>
     */
    public function getSideOddsIdsMap(): array
    {
        $cached = $this->cacheService->getUnserialized(self::CACHE_KEY);
        if ($cached) {
            return $cached;
        }

        /** @var Odd[] $odds */
        $odds = $this->oddsRepository->findBy(
            [
                'game' => GameDefinition::BACCARAT,
                'class' => [
                    self::CLASS_PLAYER_PAIR,
                    self::CLASS_BANKER_PAIR,
                    self::CLASS_ANY_PAIR,
                    self::CLASS_PERFECT_PAIR,
                    self::CLASS_SMALL_HAND,
                    self::CLASS_BIG_HAND,
                ],
            ]
        );

        $sideOddsIdsMap = [];
        foreach ($odds as $odd) {
            $sideOddsIdsMap[$odd->getClass()] = $odd->getId();
        }

        $this->cacheService->set(self::CACHE_KEY, $sideOddsIdsMap);

        return $sideOddsIdsMap;
    }
}
