<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\RecentBets;

use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Interfaces\PlayerBetInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class SomeNewSingleBetClass
 *
 * Supposed to be used for testing classes whitelists.
 */
class SomeNewSingleBetClass implements PlayerBetInterface
{
    private Player $player;

    /**
     * SomeNewSingleBetClass constructor.
     */
    public function __construct()
    {
        $this->player = (new Player())
            ->setTag('existing')
            ->setTaggedAt(new DateTimeImmutable())
        ;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return 1;
    }

    /** @return float */
    public function getAmount(): float
    {
        return 22;
    }

    /** @return float | null */
    public function getAmountWon(): ?float
    {
        return null;
    }

    /** @return float */
    public function getOddsValue(): float
    {
        return 1.1;
    }

    /** @return Player */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @param Player $player
     *
     * @return $this
     */
    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    /** @return Currency */
    public function getCurrency(): Currency
    {
        return new Currency();
    }

    /** @return DateTimeInterface */
    public function getCreatedAt(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    /** @return bool */
    public function isValid(): bool
    {
        return false;
    }

    /**
     * @param bool $isValid
     *
     * @return PlayerBetInterface
     */
    public function setValid(bool $isValid): PlayerBetInterface
    {
        return $this;
    }

    /**
     * @return Collection
     */
    public function getBets(): Collection
    {
        return new ArrayCollection([$this]);
    }

    /** @return bool */
    public function isReturned(): bool
    {
        return false;
    }
}
