<?php

namespace GamesApiBundle\DataObject;

/**
 * Class TopWonAmount
 */
class TopWonAmount
{
    /**
     * @var int
     */
    private $gameId;
    /**
     * @var array
     */
    private $amounts = [];

    /**
     * TopWonAmount constructor.
     *
     * @param int $gameId
     * @param array $amounts
     */
    public function __construct(
        int $gameId,
        array $amounts
    )
    {
        $this->gameId = $gameId;
        $this->amounts = $amounts;
    }

    /**
     * @return int
     */
    public function getGameId(): int
    {
        return $this->gameId;
    }

    /**
     * @return array
     */
    public function getAmounts(): array
    {
        return $this->amounts;
    }
}