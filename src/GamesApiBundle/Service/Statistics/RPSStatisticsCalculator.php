<?php

namespace GamesApiBundle\Service\Statistics;

use CoreBundle\Service\RepositoryProviderInterface;
use GamesApiBundle\DataObject\GameStatistics\RPSStatistics;
use GamesApiBundle\Service\RPSCardFormatter;
use GamesApiBundle\Service\Statistics\Repository\RPSStatisticsRepository;

/**
 * Class RPSStatisticsCalculator
 */
class RPSStatisticsCalculator
{
    private const NUMBER_OF_LAST_RUNS = 5;

    /** @var RPSStatisticsRepository */
    private $rpsRepository;

    /**
     * @param RepositoryProviderInterface $repositoryProvider
     */
    public function __construct(RepositoryProviderInterface $repositoryProvider)
    {
        $this->rpsRepository = $repositoryProvider->getMasterRepository(RPSStatisticsRepository::class);
    }

    /**
     * @return RPSStatistics
     */
    public function getLatestStatistics(): RPSStatistics
    {
        $zone1 = $this->rpsRepository->getLastZone1Cards(self::NUMBER_OF_LAST_RUNS);
        $zone1 = RPSCardFormatter::formatCards($zone1);
        $zone2 = $this->rpsRepository->getLastZone2Cards(self::NUMBER_OF_LAST_RUNS);
        $zone2 = RPSCardFormatter::formatCards($zone2);

        return new RPSStatistics($zone1, $zone2);
    }
}