<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Matka\BazaarRun;

use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\Odd;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class OddFixture
 */
final class OddFixture extends Fixture
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

        $odd1 = (new Odd())
            ->setGame($game)
            ->setClass('ALL_RED')
            ->setCode('test')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(3)
            ->setStatusInPresets(true)
            ->setEnabled(true)
            ->setDescription('')
            ->setPublishedDate(new \DateTime())
            ->setPublishedBy(1)
            ->setIsPokerOdd(false)
            ->setShowInRandom(false)
            ->setIsTopOdd(false)
            ->setType('classic')
        ;
        $manager->persist($odd1);

        $odd2 = (new Odd())
            ->setGame($game)
            ->setClass('JODI')
            ->setCode('1502')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(2)
            ->setStatusInPresets(true)
            ->setEnabled(true)
            ->setDescription('')
            ->setPublishedDate(new \DateTime())
            ->setPublishedBy(1)
            ->setIsPokerOdd(false)
            ->setShowInRandom(false)
            ->setIsTopOdd(false)
            ->setType('bazaar')
        ;
        $manager->persist($odd2);

        $odd3 = (new Odd())
            ->setGame($game)
            ->setClass('HALF_SANGAM_A')
            ->setCode('1502')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(4)
            ->setStatusInPresets(true)
            ->setEnabled(true)
            ->setDescription('')
            ->setPublishedDate(new \DateTime())
            ->setPublishedBy(1)
            ->setIsPokerOdd(false)
            ->setShowInRandom(false)
            ->setIsTopOdd(false)
            ->setType('bazaar')
        ;
        $manager->persist($odd3);

        $odd4 = (new Odd())
            ->setGame($game)
            ->setClass('HALF_SANGAM_B')
            ->setCode('1502')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(4)
            ->setStatusInPresets(true)
            ->setEnabled(true)
            ->setDescription('')
            ->setPublishedDate(new \DateTime())
            ->setPublishedBy(1)
            ->setIsPokerOdd(false)
            ->setShowInRandom(false)
            ->setIsTopOdd(false)
            ->setType('bazaar')
        ;
        $manager->persist($odd4);

        $odd5 = (new Odd())
            ->setGame($game)
            ->setClass('SANGAM')
            ->setCode('1502')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(6)
            ->setStatusInPresets(true)
            ->setEnabled(true)
            ->setDescription('')
            ->setPublishedDate(new \DateTime())
            ->setPublishedBy(1)
            ->setIsPokerOdd(false)
            ->setShowInRandom(false)
            ->setIsTopOdd(false)
            ->setType('bazaar')
        ;
        $manager->persist($odd5);

        $odd6 = (new Odd())
            ->setGame($game)
            ->setClass('EXACT_ANK')
            ->setCode('1502')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(1)
            ->setStatusInPresets(true)
            ->setEnabled(true)
            ->setDescription('')
            ->setPublishedDate(new \DateTime())
            ->setPublishedBy(1)
            ->setIsPokerOdd(false)
            ->setShowInRandom(false)
            ->setIsTopOdd(false)
            ->setType('classic')
        ;
        $manager->persist($odd6);

        $odd7 = (new Odd())
            ->setGame($game)
            ->setClass('EXACT_SINGLE_PANA')
            ->setCode('1502')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(2)
            ->setStatusInPresets(true)
            ->setEnabled(true)
            ->setDescription('')
            ->setPublishedDate(new \DateTime())
            ->setPublishedBy(1)
            ->setIsPokerOdd(false)
            ->setShowInRandom(false)
            ->setIsTopOdd(false)
            ->setType('classic')
        ;
        $manager->persist($odd7);

        $odd8 = (new Odd())
            ->setGame($game)
            ->setClass('EXACT_DOUBLE_PANA')
            ->setCode('1502')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(2)
            ->setStatusInPresets(true)
            ->setEnabled(true)
            ->setDescription('')
            ->setPublishedDate(new \DateTime())
            ->setPublishedBy(1)
            ->setIsPokerOdd(false)
            ->setShowInRandom(false)
            ->setIsTopOdd(false)
            ->setType('classic')
        ;
        $manager->persist($odd8);

        $odd9 = (new Odd())
            ->setGame($game)
            ->setClass('EXACT_TRIPLE_PANA')
            ->setCode('1502')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(2)
            ->setStatusInPresets(true)
            ->setEnabled(true)
            ->setDescription('')
            ->setPublishedDate(new \DateTime())
            ->setPublishedBy(1)
            ->setIsPokerOdd(false)
            ->setShowInRandom(false)
            ->setIsTopOdd(false)
            ->setType('classic')
        ;
        $manager->persist($odd9);

        $odd10 = (new Odd())
            ->setGame($game)
            ->setClass('ANK_SINGLE_PANA')
            ->setCode('1502')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(1)
            ->setStatusInPresets(true)
            ->setEnabled(true)
            ->setDescription('')
            ->setPublishedDate(new \DateTime())
            ->setPublishedBy(1)
            ->setIsPokerOdd(false)
            ->setShowInRandom(false)
            ->setIsTopOdd(false)
            ->setType('classic')
        ;
        $manager->persist($odd10);

        $odd11 = (new Odd())
            ->setGame($game)
            ->setClass('ANK_DOUBLE_PANA')
            ->setCode('1502')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(1)
            ->setStatusInPresets(true)
            ->setEnabled(true)
            ->setDescription('')
            ->setPublishedDate(new \DateTime())
            ->setPublishedBy(1)
            ->setIsPokerOdd(false)
            ->setShowInRandom(false)
            ->setIsTopOdd(false)
            ->setType('classic')
        ;
        $manager->persist($odd11);

        $odd12 = (new Odd())
            ->setGame($game)
            ->setClass('ANK_TRIPLE_PANA')
            ->setCode('1502')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(1)
            ->setStatusInPresets(true)
            ->setEnabled(true)
            ->setDescription('')
            ->setPublishedDate(new \DateTime())
            ->setPublishedBy(1)
            ->setIsPokerOdd(false)
            ->setShowInRandom(false)
            ->setIsTopOdd(false)
            ->setType('classic')
        ;
        $manager->persist($odd12);

        $manager->flush();

        $this->addReference('matka-odd:1', $odd1);
        $this->addReference('bazaar-odd:2', $odd2);
        $this->addReference('bazaar-odd:3', $odd3);
        $this->addReference('bazaar-odd:4', $odd4);
        $this->addReference('bazaar-odd:5', $odd5);
        $this->addReference('bazaar-odd:6', $odd6);
        $this->addReference('bazaar-odd:7', $odd7);
        $this->addReference('bazaar-odd:8', $odd8);
        $this->addReference('bazaar-odd:9', $odd9);
        $this->addReference('bazaar-odd:10', $odd10);
        $this->addReference('bazaar-odd:11', $odd11);
        $this->addReference('bazaar-odd:12', $odd12);
    }
}
