<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification;

use Acme\SymfonyDb\Entity\Subscription;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class SubscriptionFixture
 */
class SubscriptionFixture extends Fixture
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
        $odd = $this->getReference("odd:1:1");

        $subscription1 = (new Subscription())
            ->setAmount(1.23)
            ->setAmountWon(1.35)
            ->setBetsTotal(3)
            ->setBetsCalculated(3)
            ->setCurrency($currency)
            ->setOddValue(1.10)
            ->setPlayer($player)
            ->setIsReturned(false)
            ->setDateCreated(new \DateTimeImmutable())
            ->setOdd($odd)
            ->setInvalid(false);

        $manager->persist($subscription1);

        $manager->flush();

        $this->addReference('subscription:1', $subscription1);
    }
}
