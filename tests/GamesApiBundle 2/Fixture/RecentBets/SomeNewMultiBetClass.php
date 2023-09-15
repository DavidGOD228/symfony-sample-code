<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\RecentBets;

use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\Language;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Interfaces\PlayerBetInterface;
use Acme\SymfonyDb\Interfaces\PlayerMultiBetInterface;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class SomeNewBetClass
 *
 * Supposed to be used for testing classes whitelists.
 */
class SomeNewMultiBetClass implements PlayerBetInterface, PlayerMultiBetInterface
{
    private Player $player;

    /**
     * SomeNewMultiBetClass constructor.
     */
    public function __construct()
    {
        $language = (new Language())->setCode('en');
        $partner = (new Partner())->setApiBetInformationLanguage($language);
        $this->player = (new Player())
            ->setPartner($partner)
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

    /**
     * @return Collection
     */
    public function getBets(): Collection
    {
        return new ArrayCollection([]);
    }

    /**
     * @return Bet
     */
    public function getFirstBet(): Bet
    {
        return new Bet();
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

    /** @return Currency */
    public function getCurrency(): Currency
    {
        return new Currency();
    }

    /** @return DateTimeImmutable */
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

    /** @return bool */
    public function isReturned(): bool
    {
        return false;
    }

    /**
     * @param Bet $bet
     *
     * @return PlayerMultiBetInterface
     */
    public function addBet(Bet $bet): PlayerMultiBetInterface
    {
        return $this;
    }

    /**
     * @param Bet $bet
     *
     * @return PlayerMultiBetInterface
     */
    public function removeBet(Bet $bet): PlayerMultiBetInterface
    {
        return $this;
    }
}
