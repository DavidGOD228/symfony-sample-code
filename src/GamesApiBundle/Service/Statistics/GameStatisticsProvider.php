<?php

namespace GamesApiBundle\Service\Statistics;

use CoreBundle\Service\GameService;
use GamesApiBundle\DataObject\GameStatistics\GameStatistics;
use InvalidArgumentException;

/**
 * Class GameStatisticsProvider
 */
class GameStatisticsProvider
{
    public const SUPPORTED_GAMES = [
        GameService::GAME_RPS,
    ];

    private RPSStatisticsCalculator $rpsStats;

    /**
     * @param RPSStatisticsCalculator $rpsStats
     */
    public function __construct(
        RPSStatisticsCalculator $rpsStats
    )
    {
        $this->rpsStats = $rpsStats;
    }

    /**
     * @param int $gameId
     *
     * @return GameStatistics
     * @throws InvalidArgumentException
     */
    public function getLatestGameStatistics(int $gameId): GameStatistics
    {
        switch ($gameId) {
            case GameService::GAME_RPS:
                $statistics = $this->rpsStats->getLatestStatistics();
                break;
            default:
                throw new InvalidArgumentException("GAME_IS_NOT_SUPPORTED:$gameId");
        }

        return new GameStatistics($statistics);
    }
}