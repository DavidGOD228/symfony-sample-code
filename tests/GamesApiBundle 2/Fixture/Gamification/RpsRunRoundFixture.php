<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification;

use Acme\SymfonyDb\Entity\RpsRunRound;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;

/**
 * Class RpsRunRoundFixture
 */
final class RpsRunRoundFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $run = $this->getReference('game:15:run');

        $runRound1 = (new RpsRunRound())
            ->setGameRun($run)
            ->setIsActive(true)
            ->setIsResultsEntered(true)
            ->setTime(new DateTimeImmutable());
        $manager->persist($runRound1);

        $runRound2 = (new RpsRunRound())
            ->setGameRun($run)
            ->setIsActive(true)
            ->setIsResultsEntered(true)
            ->setTime(new DateTimeImmutable());
        $manager->persist($runRound2);

        $manager->flush();

        $this->addReference('rps-run-round:first', $runRound1);
        $this->addReference('rps-run-round:second', $runRound2);
    }
}
