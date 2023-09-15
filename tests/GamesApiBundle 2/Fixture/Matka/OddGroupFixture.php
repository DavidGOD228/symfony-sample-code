<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Matka;

use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\OddGroup;
use Acme\SymfonyDb\Type\OddGroupNameType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class OddGroupFixture
 */
class OddGroupFixture extends Fixture
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
            ->setName(OddGroupNameType::SINGLE_PANA)
            ->setDescription('')
            ->setOrder(2);
        $manager->persist($oddGroup2);

        $oddGroup3 = (new OddGroup())
            ->setGame($game)
            ->setName(OddGroupNameType::DOUBLE_PANA)
            ->setDescription('')
            ->setOrder(3);
        $manager->persist($oddGroup3);

        $oddGroup4 = (new OddGroup())
            ->setGame($game)
            ->setName(OddGroupNameType::TRIPLE_PANA)
            ->setDescription('')
            ->setOrder(4);
        $manager->persist($oddGroup4);

        $oddGroup51 = (new OddGroup())
            ->setGame($game)
            ->setName(OddGroupNameType::BAZAAR_MAIN_BETS)
            ->setDescription('')
            ->setOrder(5);
        $manager->persist($oddGroup51);

        $oddGroup52 = (new OddGroup())
            ->setGame($game)
            ->setName(OddGroupNameType::BAZAAR_MAIN_BETS)
            ->setDescription('')
            ->setOrder(5);
        $manager->persist($oddGroup52);

        $oddGroup61 = (new OddGroup())
            ->setGame($game)
            ->setName(OddGroupNameType::BAZAAR_OPEN)
            ->setDescription('')
            ->setOrder(6);
        $manager->persist($oddGroup61);

        $oddGroup62 = (new OddGroup())
            ->setGame($game)
            ->setName(OddGroupNameType::BAZAAR_OPEN)
            ->setDescription('')
            ->setOrder(6);
        $manager->persist($oddGroup62);

        $oddGroup71 = (new OddGroup())
            ->setGame($game)
            ->setName(OddGroupNameType::BAZAAR_CLOSE)
            ->setDescription('')
            ->setOrder(7);
        $manager->persist($oddGroup71);

        $oddGroup72 = (new OddGroup())
            ->setGame($game)
            ->setName(OddGroupNameType::BAZAAR_CLOSE)
            ->setDescription('')
            ->setOrder(7);
        $manager->persist($oddGroup72);

        $manager->flush();

        $this->addReference('matka-odd-group:1', $oddGroup1);
        $this->addReference('matka-odd-group:2', $oddGroup2);
        $this->addReference('matka-odd-group:3', $oddGroup3);
        $this->addReference('matka-odd-group:4', $oddGroup4);
        $this->addReference('matka-odd-group:51', $oddGroup51);
        $this->addReference('matka-odd-group:52', $oddGroup52);
        $this->addReference('matka-odd-group:61', $oddGroup61);
        $this->addReference('matka-odd-group:62', $oddGroup62);
        $this->addReference('matka-odd-group:71', $oddGroup71);
        $this->addReference('matka-odd-group:72', $oddGroup72);
    }
}
