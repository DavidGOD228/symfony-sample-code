<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Betting;

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
     */
    public function load(ObjectManager $manager)
    {
        /** @var Game $game7 */
        $game7 = $this->getReference('game:7');
        /** @var Game $game15 */
        $game15 = $this->getReference('game:15');

        $odd = (new Odd())
            ->setGame($game7)
            ->setClass('test')
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
        $manager->persist($odd);

        $odd1 = (new Odd())
            ->setGame($game15)
            ->setClass('zone_1')
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
        $manager->persist($odd1);

        $odd2 = (new Odd())
            ->setGame($game15)
            ->setClass('zone_2')
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
        $manager->persist($odd2);

        $manager->flush();
    }
}
