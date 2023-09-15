<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification;

use Acme\SymfonyDb\Entity\RpsRunRoundCard;
use Acme\SymfonyDb\Type\RpsCardType;
use Acme\SymfonyDb\Type\RpsDealtToType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;

/**
 * Class RpsRunRoundCardFixture
 */
class RpsRunRoundCardFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $run = $this->getReference('game:15:run');
        $runRound = $this->getReference('rps-run-round:first');

        $item1 = (new RpsRunRoundCard())
            ->setGameRun($run)
            ->setRunRound($runRound)
            ->setCard(RpsCardType::ROCK)
            ->setDealtTo(RpsDealtToType::ZONE_1)
            ->setIsChanged(false);

        $manager->persist($item1);

        $item2 = (new RpsRunRoundCard())
            ->setGameRun($run)
            ->setRunRound($runRound)
            ->setCard(RpsCardType::SCISSORS)
            ->setDealtTo(RpsDealtToType::ZONE_2)
            ->setIsChanged(false);

        $manager->persist($item2);

        $manager->flush();
    }
}
