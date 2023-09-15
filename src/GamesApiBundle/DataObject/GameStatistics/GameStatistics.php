<?php

namespace GamesApiBundle\DataObject\GameStatistics;

use Carbon\Carbon;

/**
 * Class GameStatistics
 */
class GameStatistics
{
    /** @var GameStatisticsInterface */
    public $statistics;

    /** @var int */
    public $infoTime;

    /**
     * @param GameStatisticsInterface $statistics
     */
    public function __construct(GameStatisticsInterface $statistics)
    {
        $this->infoTime = Carbon::now()->getTimestamp();
        $this->statistics = $statistics;
    }
}