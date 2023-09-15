<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\GameOptions;

use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class GameOptions
 *
 * @psalm-immutable
 */
final class GameOptions
{
    /** @SerializedName("gameId") */
    public int $gameId;
    /** @SerializedName("presetId") */
    public int $presetId;
    /**
     * @SerializedName("items")
     * @var array<GameOptionsGameItem>
     */
    public array $items;
    /**
     * @SerializedName("groups")
     * @var array<GameOptionsBettingOptionsGroup>
     */
    public array $groups;
    /**
     * Structure different for each game
     *
     * @SerializedName("data")
     */
    public ?object $data;

    /**
     * GameOptionsResponse constructor.
     *
     * @param int $gameId
     * @param int $oddsPresetId
     * @param GameOptionsBettingOptionsGroup[] $bettingOptionsGroups
     * @param GameOptionsGameItem[] $gameItems
     * @param object|null $data
     */
    public function __construct(
        int $gameId,
        int $oddsPresetId,
        array $bettingOptionsGroups,
        array $gameItems,
        ?object $data
    )
    {
        $this->gameId = $gameId;
        $this->presetId = $oddsPresetId;
        $this->groups = $bettingOptionsGroups;
        $this->items = $gameItems;
        $this->data = $data;
    }
}