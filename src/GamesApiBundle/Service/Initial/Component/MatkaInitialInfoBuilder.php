<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Initial\Component;

use Acme\Contract\GameDefinition;
use CoreBundle\Repository\OddGroupsRepository;
use CoreBundle\Service\CacheServiceInterface;
use CoreBundle\Service\GameService;
use CoreBundle\Service\RepositoryProviderInterface;
use Doctrine\ORM\EntityRepository;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoMatka;
use GamesApiBundle\Service\OddService;

/**
 * Class PartnerInitialInfoBuilder
 */
final class MatkaInitialInfoBuilder
{
    private const CACHE_KEY = 'initial-info:matka';

    private OddService $oddService;
    private GameService $gameService;
    private EntityRepository $oddGroupsRepository;
    private CacheServiceInterface $cacheService;

    /**
     * MatkaInitialInfoBuilder constructor.
     *
     * @param RepositoryProviderInterface $repoProvider
     * @param OddService $oddService
     * @param GameService $gameService
     * @param CacheServiceInterface $cacheService
     */
    public function __construct(
        RepositoryProviderInterface $repoProvider,
        OddService $oddService,
        GameService $gameService,
        CacheServiceInterface $cacheService
    )
    {
        $this->oddService = $oddService;
        $this->gameService = $gameService;
        $this->cacheService = $cacheService;
        $this->oddGroupsRepository = $repoProvider->getSlaveRepository(OddGroupsRepository::class);
    }

    /**
     * @return PartnerInitialInfoMatka
     */
    public function getInfo(): PartnerInitialInfoMatka
    {
        $result = $this->cacheService->getUnserialized(self::CACHE_KEY, [PartnerInitialInfoMatka::class]);

        if (!$result) {
            $game = $this->gameService->getGame(GameDefinition::MATKA);
            $odds = $this->oddService->getOddsByGame($game);
            $oddGroups = $this->oddGroupsRepository->getEnabledForGames([$game]);
            $result = new PartnerInitialInfoMatka(
                $odds,
                $oddGroups
            );
            $this->cacheService->set(self::CACHE_KEY, $result);
        }

        return $result;
    }
}
