<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Betting;

use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameRun;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTimeImmutable;

/**
 * Class GameRunSubscriptionFixture
 */
final class GameRunSubscriptionFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Game $game7 */
        $game7 = $this->getReference('game:7');

        $gameRun1 = (new GameRun())
            ->setGame($game7)
            ->setTime(new DateTimeImmutable('now'))
            ->setCode('100')
            ->setIsReturned(false)
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setResultsEntered(false)
            ->setPublishedDate(new DateTimeImmutable('now'));
        $manager->persist($gameRun1);
        $this->addReference('game:7:next-run', $gameRun1);

        $gameRun2 = (new GameRun())
            ->setGame($game7)
            ->setTime(new DateTimeImmutable('now +5 min'))
            ->setCode('101')
            ->setIsReturned(false)
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setResultsEntered(false)
            ->setPublishedDate(new DateTimeImmutable('now +5 min'));
        $manager->persist($gameRun2);

        $gameRun3 = (new GameRun())
            ->setGame($game7)
            ->setTime(new DateTimeImmutable('now +10 min'))
            ->setCode('102')
            ->setIsReturned(false)
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setResultsEntered(false)
            ->setPublishedDate(new DateTimeImmutable('now +10 min'));
        $manager->persist($gameRun3);

        $gameRun4 = (new GameRun())
            ->setGame($game7)
            ->setTime(new DateTimeImmutable('now +15 min'))
            ->setCode('103')
            ->setIsReturned(false)
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setResultsEntered(false)
            ->setPublishedDate(new DateTimeImmutable('now +15 min'));
        $manager->persist($gameRun4);

        $manager->flush();
    }
}
