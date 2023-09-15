<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Translation;

use Acme\SymfonyDb\Entity\PokerCombination;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class PokerCombinationFixture
 */
class PokerCombinationFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $combination1 = (new PokerCombination())
            ->setCode('hand_1');
        $combination2 = (new PokerCombination())
            ->setCode('hand_2');

        $manager->persist($combination1);
        $manager->persist($combination2);
        $manager->flush();
    }
}
