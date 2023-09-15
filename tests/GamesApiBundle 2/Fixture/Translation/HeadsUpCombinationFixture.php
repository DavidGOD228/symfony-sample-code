<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Translation;

use Acme\SymfonyDb\Entity\HeadsUpCombination;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class HeadsUpCombinationFixture
 */
class HeadsUpCombinationFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $combination1 = (new HeadsUpCombination())
            ->setCode('hand_1');
        $combination2 = (new HeadsUpCombination())
            ->setCode('hand_2');

        $manager->persist($combination1);
        $manager->persist($combination2);
        $manager->flush();
    }
}
