<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture;

use Carbon\CarbonImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Acme\SymfonyDb\Entity\PlayerTagSource;

/**
 * Class PlayerTagSyncFixture
 */
final class PlayerTagSourceFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $player = $this->getReference('player:1');

        $playerTagSource = (new PlayerTagSource())
            ->setPlayerId($player->getId())
            ->setTag('ExampleTag')
            ->setUpdatedAt(CarbonImmutable::now())
        ;

        $manager->persist($playerTagSource);
        $manager->flush();
    }
}
