<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Matka;

use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\Odd;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class OddFixture
 */
class OddFixture extends Fixture
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
            ->setClass('EXACT_SINGLE_PANA')
            ->setCode('test')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(0)
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
            ->setClass('EXACT_DOUBLE_PANA')
            ->setCode('1501')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(0)
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
        $manager->persist($odd2);

        $odd3 = (new Odd())
            ->setGame($game)
            ->setClass('EXACT_TRIPLE_PANA')
            ->setCode('1502')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(0)
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
        $manager->persist($odd3);

        $odd4 = (new Odd())
            ->setGame($game)
            ->setClass('EXACT_ANK')
            ->setCode('1502')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(0)
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
        $manager->persist($odd4);

        $odd5 = (new Odd())
            ->setGame($game)
            ->setClass('JODI')
            ->setCode('1502')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(0)
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
            ->setClass('SANGAM')
            ->setCode('1502')
            ->setOrder(0)
            ->setProbability(1)
            ->setItemsCount(0)
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
        $manager->persist($odd6);

        $manager->flush();

        $this->addReference('matka-odd:1', $odd1);
        $this->addReference('matka-odd:2', $odd2);
        $this->addReference('matka-odd:3', $odd3);
        $this->addReference('matka-odd:4', $odd4);
        $this->addReference('matka-odd:5', $odd5);
        $this->addReference('matka-odd:6', $odd6);
    }
}
