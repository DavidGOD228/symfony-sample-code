<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture;

use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameRun;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class LotteryRunFixture
 */
final class LotteryRunFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        /** @var Game $lucky7 */
        $lucky7 = $this->getReference('game:1');
        /** @var Game $poker */
        $poker = $this->getReference('game:5');
        /** @var Game $baccarat */
        $baccarat = $this->getReference('game:6');
        /** @var Game $warOfBets */
        $warOfBets = $this->getReference('game:8');
        /** @var Game $headsUp */
        $headsUp = $this->getReference('game:12');

        $lucky7run = (new GameRun())
            ->setCode('100')
            ->setIsReturned(false)
            ->setTime(new DateTimeImmutable())
            ->setPublishedDate(new DateTimeImmutable())
            ->setGame($lucky7)
            ->setIsImported(true)
            ->setVideoConfirmationRequired(false)
            ->setResultsEntered(false)
        ;
        $manager->persist($lucky7run);

        $lucky7activeRun = (new GameRun())
            ->setCode('101')
            ->setIsReturned(false)
            ->setTime(new DateTimeImmutable())
            ->setPublishedDate(new DateTimeImmutable())
            ->setGame($lucky7)
            ->setIsImported(true)
            ->setVideoConfirmationRequired(false)
            ->setResultsEntered(false)
        ;
        $manager->persist($lucky7activeRun);

        $pokerRun = (new GameRun())
            ->setCode('500')
            ->setIsReturned(false)
            ->setTime(new DateTimeImmutable())
            ->setPublishedDate(new DateTimeImmutable())
            ->setGame($poker)
            ->setIsImported(true)
            ->setVideoConfirmationRequired(false)
            ->setResultsEntered(false)
        ;
        $manager->persist($pokerRun);

        $baccaratRun = (new GameRun())
            ->setCode('600')
            ->setIsReturned(false)
            ->setTime(new DateTimeImmutable())
            ->setPublishedDate(new DateTimeImmutable())
            ->setGame($baccarat)
            ->setIsImported(true)
            ->setVideoConfirmationRequired(false)
            ->setResultsEntered(false)
        ;
        $manager->persist($baccaratRun);

        $warOfBetsRun = (new GameRun())
            ->setCode('800')
            ->setIsReturned(false)
            ->setTime(new DateTimeImmutable())
            ->setPublishedDate(new DateTimeImmutable())
            ->setGame($warOfBets)
            ->setIsImported(true)
            ->setVideoConfirmationRequired(false)
            ->setResultsEntered(false)
        ;
        $manager->persist($warOfBetsRun);

        $headsUpRun = (new GameRun())
            ->setCode('1200')
            ->setIsReturned(false)
            ->setTime(new DateTimeImmutable())
            ->setPublishedDate(new DateTimeImmutable())
            ->setGame($headsUp)
            ->setIsImported(true)
            ->setVideoConfirmationRequired(false)
            ->setResultsEntered(false)
        ;
        $manager->persist($headsUpRun);

        $manager->flush();

        $this->addReference('lottery_run:lucky7', $lucky7run);
        $this->addReference('lottery_run:poker', $pokerRun);
        $this->addReference('lottery_run:baccarat', $baccaratRun);
        $this->addReference('lottery_run:war-of-bets', $warOfBetsRun);
        $this->addReference('lottery_run:headsup', $headsUpRun);
    }
}
