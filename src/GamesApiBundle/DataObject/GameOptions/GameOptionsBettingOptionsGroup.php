<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\GameOptions;

use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class GameOptionsBettingOptionsGroup
 *
 * @psalm-immutable
 */
final class GameOptionsBettingOptionsGroup
{
    /** @SerializedName("id") */
    public int $id;

    /**
     * @SerializedName("options")
     * @var array<GameOptionsBettingOption>
     */
    public array $options;

    /**
     * BettingOptionsGroup constructor.
     *
     * @param int $id
     * @param array<GameOptionsBettingOption> $bettingOptions
     */
    public function __construct(int $id, array $bettingOptions)
    {
        $this->id = $id;
        $this->options = $bettingOptions;
    }
}