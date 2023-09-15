<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Betting\Validation;

use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameRun;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class GameRunFixture
 */
final class GameRunFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        /** @var Game $gameWheel */
        $gameWheel = $this->getReference('game:7');
        /** @var Game $gameLucky5 */
        $gameLucky5 = $this->getReference('game:3');

        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(false)
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl(null)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2050-01-01'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setGame($gameWheel)
        ;
        $manager->persist($gameRun);
        $this->addReference('game:7:next-run', $gameRun);

        $gameRun = (new GameRun())
            ->setCode('200')
            ->setIsReturned(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl(null)
            ->setResultsEntered(false)
            ->setIsImported(false)
            ->setTime(new DateTimeImmutable('2050-01-01'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setGame($gameLucky5);
        $manager->persist($gameRun);
        $this->addReference('game:3:next-run', $gameRun);

        $manager->flush();
    }
}
