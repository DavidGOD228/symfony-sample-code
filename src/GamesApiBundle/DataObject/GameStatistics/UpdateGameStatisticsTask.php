<?php

namespace GamesApiBundle\DataObject\GameStatistics;

/**
 * Class UpdateGameStatisticsTask
 */
class UpdateGameStatisticsTask
{
    /** @var int */
    private $gameId;

    /**
     * @param int $gameId
     */
    public function __construct(int $gameId)
    {
        $this->gameId = $gameId;
    }

    /**
     * @return int
     */
    public function getGameId(): int
    {
        return $this->gameId;
    }
}