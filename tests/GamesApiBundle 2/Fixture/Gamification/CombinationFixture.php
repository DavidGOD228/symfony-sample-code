<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification;

use Acme\SymfonyDb\Entity\Combination;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class CombinationFixture
 */
class CombinationFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $player = $this->getReference('player2:gamification');
        $currency = $this->getReference('currency:eur');

        $combination1 = (new Combination())
            ->setCurrency($currency)
            ->setAmount(10)
            ->setAmountWon(10)
            ->setOddValue(2.34)
            ->setOddValueFinal(2.35)
            ->setPlayer($player)
            ->setDateCreated(new \DateTimeImmutable())
            ->setInvalid(false);

        $manager->persist($combination1);

        $manager->flush();

        $this->addReference('combination:1', $combination1);
    }
}
