<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults;

use Acme\SymfonyDb\Entity\GameRun;
use CoreBundle\Service\CacheServiceInterface;
use GamesApiBundle\DataObject\GameRunResults\ResultDTO;

/**
 * Class ResultsFormatter
 */
final class ResultsBuilder
{
    private const CACHE_PREFIX = 'game-run-results:v2:';

    private CacheServiceInterface $cache;
    private FormatterFactory $formatterFactory;
    private ResultsVideoUrlProvider $videoUrlProvider;

    /**
     * ResultsBuilder constructor.
     *
     * @param CacheServiceInterface $cacheService
     * @param FormatterFactory $formatterFactory
     * @param ResultsVideoUrlProvider $videoUrlProvider
     */
    public function __construct(
        CacheServiceInterface $cacheService,
        FormatterFactory $formatterFactory,
        ResultsVideoUrlProvider $videoUrlProvider
    )
    {
        $this->cache = $cacheService;
        $this->formatterFactory = $formatterFactory;
        $this->videoUrlProvider = $videoUrlProvider;
    }

    /**
     * @param GameRun[] $gameRuns
     * @param string $rootDomain
     *
     * @return ResultDTO[]
     */
    public function build(iterable $gameRuns, string $rootDomain): array
    {
        $formattedResults = [];

        foreach ($gameRuns as $gameRun) {
            if ($gameRun->getIsReturned()) {
                $results = null;
            } else {
                $results = $this->getResultsCached($gameRun);
            }

            $videoUrl = $this->videoUrlProvider->getVideoUrl($gameRun, $rootDomain);

            $formattedResults[] = new ResultDTO($gameRun, $results, $videoUrl);
        }

        return $formattedResults;
    }

    /**
     * Expected cache flow: player gets game runs list from DB.
     * Hard to cache possible filters, timezone, etc.
     * For each game run caching results, so in case of different requests for same game run
     * we'll provide it from cache.
     *
     * @param GameRun $gameRun
     *
     * @return array
     */
    public function getResultsCached(GameRun $gameRun): array
    {
        $cacheKey = self::CACHE_PREFIX . $gameRun->getCode();
        $results = $this->cache->getUnserialized($cacheKey);
        if ($results !== null) {
            return $results;
        }

        $results = $this->getResults($gameRun);
        $this->cache->set($cacheKey, $results);

        return $results;
    }

    /**
     * @param GameRun $gameRun
     *
     * @return array
     */
    private function getResults(GameRun $gameRun): array
    {
        $gameId = $gameRun->getGame()->getId();

        $formatter = $this->formatterFactory->getFormatter($gameId);

        $results = $formatter->format($gameRun);

        return $results;
    }
}
