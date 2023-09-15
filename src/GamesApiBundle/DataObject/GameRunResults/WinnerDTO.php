<?php

declare(strict_types = 1);

namespace GamesApiBundle\DataObject\GameRunResults;

/**
 * Class WinnerDto
 *
 * @psalm-immutable
 */
final class WinnerDTO
{
    private array $winners;
    private array $combinations;
    private array $combinationWonCards;
    private ?string $wonHand;

    /**
     * WinnerDto constructor.
     *
     * @param string[] $winners - list of values from dealtTo
     * @param int[] $combinations
     * @param int[] $combinationCards - specific stuff for HeadsUp right now.
     * @param string|null $wonHand
     */
    public function __construct(
        array $winners,
        array $combinations,
        array $combinationCards,
        ?string $wonHand
    )
    {
        $this->winners = $winners;
        $this->combinations = $combinations;
        $this->combinationWonCards = $combinationCards;
        $this->wonHand = $wonHand;
    }

    /**
     * @return string[]
     */
    public function getWinners(): array
    {
        return $this->winners;
    }

    /**
     * @return int[]
     */
    public function getCombinations(): array
    {
        return $this->combinations;
    }

    /**
     * @return array
     */
    public function getCombinationWonCards(): array
    {
        return $this->combinationWonCards;
    }

    /**
     * @return string|null
     */
    public function getWonHand(): ?string
    {
        return $this->wonHand;
    }
}