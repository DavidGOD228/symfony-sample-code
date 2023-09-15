<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification;

use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\PokerRunRound;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class PokerRunRoundFixture
 */
final class PokerRunRoundFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        /** @var GameRun $run */
        $run = $this->getReference('game:5:run');

        $runRound = (new PokerRunRound())
            ->setGameRun($run)
            ->setRoundNumber(3)
            ->setIsActive(false)
            ->setResultsEntered(true)
            ->setTime(new DateTimeImmutable());
        $manager->persist($runRound);

        $manager->flush();

        $this->addReference('poker-run-round:gamification', $runRound);
    }
}
