<?php

namespace GamesApiBundle\DataObject\GameStatistics;

/**
 * Class RPSStatistics
 */
class RPSStatistics implements GameStatisticsInterface
{
    /** @var string[] */
    public $zone1;

    /** @var string[] */
    public $zone2;

    /**
     * @param string[] $zone1
     * @param string[] $zone2
     */
    public function __construct(array $zone1, array $zone2)
    {
        $this->zone1 = $zone1;
        $this->zone2 = $zone2;
    }
}