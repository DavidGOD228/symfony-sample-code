<?php

declare(strict_types = 1);

namespace GamesApiBundle\Event\Gamification;

use Acme\SymfonyDb\Entity\PlayerProfile;
use DateTimeImmutable;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class PostPlayerProfileCreationEvent
 */
final class PostPlayerProfileCreationEvent extends Event
{
    private int $id;
    private DateTimeImmutable $dateTime;

    /**
     * Create an instance from PlayerProfile
     *
     * @param PlayerProfile $profile
     * @param DateTimeImmutable $dateTime
     *
     * @return static
     */
    public static function fromPlayerProfile(PlayerProfile $profile, DateTimeImmutable $dateTime): self
    {
        return new self(
            $profile->getPlayer()->getId(),
            $dateTime
        );
    }

    /**
     * PostPlayerProfileCreationEvent constructor.
     *
     * @param int $playerId
     * @param DateTimeImmutable $dateTime
     */
    public function __construct(int $playerId, DateTimeImmutable $dateTime)
    {
        $this->id = $playerId;
        $this->dateTime = $dateTime;
    }

    /**
     * @return int
     */
    public function getPlayerId(): int
    {
        return $this->id;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDateTime(): DateTimeImmutable
    {
        return $this->dateTime;
    }
}
