<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture;

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
        /* @var Game $game */
        $game = $this->getReference('game:1');

        for ($i = 0; $i < 3; $i++) {
            $odd = (new Odd())
                ->setGame($game)
                ->setCode('-')
                ->setDescription('')
                ->setPublishedBy(1)
                ->setClass('CLASS')
                ->setItemsCount(0)
                ->setPublishedDate(new \DateTime())
                ->setIsPokerOdd(false)
                ->setEnabled(true)
                ->setIsTopOdd(true)
                ->setOrder($i)
                ->setStatusInPresets(true)
                ->setProbability(0.1)
                ->setShowInRandom(true)
                ->setType('classic')
            ;

            $manager->persist($odd);
        }

        /* @var Game $lottery */
        $game = $this->getReference('game:8');

        for ($i = 0; $i < 2; $i++) {
            $odd = (new Odd())
                ->setGame($game)
                ->setCode('-')
                ->setDescription('')
                ->setPublishedBy(1)
                ->setClass('CLASS')
                ->setItemsCount(0)
                ->setPublishedDate(new \DateTime())
                ->setIsPokerOdd(false)
                ->setEnabled(true)
                ->setIsTopOdd(true)
                ->setOrder($i)
                ->setStatusInPresets(true)
                ->setProbability(0.1)
                ->setShowInRandom(true)
                ->setType('classic')
            ;

            $manager->persist($odd);
        }

        $manager->flush();
    }
}
