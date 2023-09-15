<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture;

use Acme\SymfonyDb\Entity\FavoriteOdd;
use Doctrine\Persistence\ObjectManager;
use SymfonyTests\Unit\CoreBundle\Fixture\AbstractCustomizableFixture;

/**
 * Class PlayerFixture
 */
class FavoriteOddFixture extends AbstractCustomizableFixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $player = $this->getReference('player:1');
        $odd1 = $this->getReference('odd:1:1');
        $odd2 = $this->getReference('odd:1:2');

        $favoriteOdd1 = (new FavoriteOdd())
            ->setPlayer($player)
            ->setOdd($odd1);

        $manager->persist($favoriteOdd1);

        $favoriteOdd2 = (new FavoriteOdd())
            ->setPlayer($player)
            ->setOdd($odd2);

        $manager->persist($favoriteOdd2);

        $manager->flush();

        $this->addReference('favorite-odd:1', $favoriteOdd1);
        $this->addReference('favorite-odd:2', $favoriteOdd2);
    }
}
