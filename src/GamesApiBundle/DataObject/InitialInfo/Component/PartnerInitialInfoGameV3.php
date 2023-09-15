<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\InitialInfo\Component;

use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class PartnerInitialInfoGameV3
 *
 * @psalm-immutable
 */
final class PartnerInitialInfoGameV3
{
    /** @SerializedName("gameId") */
    public int $gameId;
    /** @SerializedName("state") */
    public string $state;
    /** @SerializedName("streamDelay") */
    public int $streamDelay;
    /** @SerializedName("startDelay") */
    public int $startDelay;

    /**
     * Constructor.
     *
     * @param int $gameId
     * @param string $state
     * @param int $streamDelay
     * @param int $startDelay
     */
    public function __construct(int $gameId, string $state, int $streamDelay, int $startDelay)
    {
        $this->gameId = $gameId;
        $this->state = $state;
        $this->streamDelay = $streamDelay;
        $this->startDelay = $startDelay;
    }
}