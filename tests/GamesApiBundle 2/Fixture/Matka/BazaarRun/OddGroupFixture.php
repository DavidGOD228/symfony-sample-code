<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Matka\BazaarRun;

use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\OddGroup;
use Acme\SymfonyDb\Type\OddGroupNameType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class OddGroupFixture
 */
final class OddGroupFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        /** @var Game $game */
        $game = $this->getReference('game:18');

        $oddGroup1 = (new OddGroup())
            ->setGame($game)
            ->setName(OddGroupNameType::EXACT_PANA)
            ->setDescription('')
            ->setOrder(1);
        $manager->persist($oddGroup1);

        $oddGroup2 = (new OddGroup())
            ->setGame($game)
            ->setName(OddGroupNameType::BAZAAR_MAIN_BETS)
            ->setDescription('')
            ->setOrder(2);
        $manager->persist($oddGroup2);

        $oddGroup3 = (new OddGroup())
            ->setGame($game)
            ->setName(OddGroupNameType::BAZAAR_MAIN_BETS)
            ->setDescription('')
            ->setOrder(3);
        $manager->persist($oddGroup3);

        $oddGroup4 = (new OddGroup())
            ->setGame($game)
            ->setName(OddGroupNameType::BAZAAR_MAIN_BETS)
            ->setDescription('')
            ->setOrder(4);
        $manager->persist($oddGroup4);

        $oddGroup5 = (new OddGroup())
            ->setGame($game)
            ->setName(OddGroupNameType::BAZAAR_MAIN_BETS)
            ->setDescription('')
            ->setOrder(5);
        $manager->persist($oddGroup5);

        $manager->flush();

        $this->addReference('matka-odd-group:1', $oddGroup1);
        $this->addReference('bazaar-odd-group:2', $oddGroup2);
        $this->addReference('bazaar-odd-group:3', $oddGroup3);
        $this->addReference('bazaar-odd-group:4', $oddGroup4);
        $this->addReference('bazaar-odd-group:5', $oddGroup5);
    }
}
