<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\InitialInfo\Component;

use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class PartnerInitialInfoGame
 *
 * @psalm-immutable
 */
final class PartnerInitialInfoGame
{
    /** @SerializedName("state") */
    public string $state;
    /** @SerializedName("delay") */
    public int $delay;
    /** @SerializedName("startDelay") */
    public int $startDelay;
    /** @SerializedName("order") */
    public int $order;
    /** @SerializedName("presetId") */
    public int $presetId;
    /**
     * @var PartnerInitialInfoOddGroup[]
     * @SerializedName("oddGroups")
     */
    public array $oddGroups;
    /**
     * @var int[]
     * @SerializedName("oddIds")
     */
    public array $oddIds;
    /**
     * @var int[]
     * @SerializedName("favoriteOddIds")
     */
    public array $favoriteOddIds;
    /**
     * @var PartnerInitialInfoGameItem[]
     * @SerializedName("gameItems")
     */
    public array $gameItems;
    /** @SerializedName("optionsData") */
    public ?PartnerInitialInfoMatka $optionsData;

    /**
     * @param string $state
     * @param int $delay
     * @param int $startDelay
     * @param int $order
     * @param int $presetId
     * @param PartnerInitialInfoOddGroup[] $oddGroups
     * @param int[] $oddIds
     * @param int[] $favoriteOddIds
     * @param PartnerInitialInfoGameItem[] $gameItems
     * @param PartnerInitialInfoMatka|null $optionsData @deprecated matka data is being moved to gameOptions API
     */
    public function __construct(
        string $state,
        int $delay,
        int $startDelay,
        int $order,
        int $presetId,
        array $oddGroups,
        array $oddIds,
        array $favoriteOddIds,
        array $gameItems,
        ?PartnerInitialInfoMatka $optionsData
    )
    {
        $this->state = $state;
        $this->delay = $delay;
        $this->startDelay = $startDelay;
        $this->order = $order;
        $this->presetId = $presetId;
        $this->oddGroups = $oddGroups;
        $this->oddIds = $oddIds;
        $this->favoriteOddIds = $favoriteOddIds;
        $this->gameItems = $gameItems;
        $this->optionsData = $optionsData;
    }
}