<?php

declare(strict_types = 1);

namespace GamesApiBundle\DataObject\GameRunResults;

use Acme\SymfonyDb\Entity\GameRun;
use Acme\Time\Time;

/**
 * Class ResultDTO
 */
final class ResultDTO implements \JsonSerializable
{
    private GameRun $gameRun;
    private ?array $results;
    private ?string $videoUrl;

    /**
     * ResultDTO constructor.
     *
     * @param GameRun $gameRun
     * @param array|null $results
     * @param string|null $videoUrl
     */
    public function __construct(
        GameRun $gameRun,
        ?array $results,
        ?string $videoUrl
    )
    {
        $this->gameRun = $gameRun;
        $this->results = $results;
        $this->videoUrl = $videoUrl;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $toSerialize = [
            'code' => $this->gameRun->getCode(),
            'time' => $this->gameRun->getTime()->getTimestamp() * Time::MILLISECONDS_IN_SECOND,
            'gameId' => $this->gameRun->getGame()->getId(),
            'isReturned' => $this->gameRun->getIsReturned(),
            'videoUrl' => $this->videoUrl,
        ];

        if ($this->results) {
            $toSerialize['results'] = $this->results;
        }

        return $toSerialize;
    }
}
