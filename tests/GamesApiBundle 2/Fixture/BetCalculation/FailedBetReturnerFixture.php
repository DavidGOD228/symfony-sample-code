<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\BetCalculation;

use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\BetApiFailed;
use Acme\SymfonyDb\Entity\Combination;
use Acme\SymfonyDb\Entity\Subscription;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class FailedBetReturnerFixture
 */
final class FailedBetReturnerFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Bet $bet1single */
        $bet1single = $this->getReference('bet:3:invalid');

        $failedBet = (new BetApiFailed())
            ->setServer('')
            ->setBet($bet1single)
            ->setErrorText('ERROR')
            ->setTime(CarbonImmutable::now())
        ;
        $bet1single->setFailed($failedBet);

        $manager->persist($bet1single);


        /** @var Bet $bet2subscription */
        $bet2subscription = $this->getReference('bet:3:1');
        $failedBet = (new BetApiFailed())
            ->setServer('')
            ->setBet($bet2subscription)
            ->setErrorText('ERROR')
            ->setTime(CarbonImmutable::now())
        ;
        $bet2subscription->setFailed($failedBet)->setInvalid(true);

        $subscription = (new Subscription())
            ->setCurrency($bet2subscription->getCurrency())
            ->setPlayer($bet2subscription->getPlayer())
            ->setAmount($bet2subscription->getAmount())
            ->setOddValue($bet2subscription->getOddValue())
            ->setDateCreated($bet2subscription->getTime())
            ->setOdd($bet2subscription->getOdd())
            ->setBetsCalculated(0)
            ->setBetsTotal(1)
            ->setInvalid(true)
            ->setIsReturned(false)
            ->addBet($bet2subscription);

        $manager->persist($subscription);


        /** @var Bet $bet3combination */
        $bet3combination = $this->getReference('bet:3:2');

        $failedBet = (new BetApiFailed())
            ->setServer('')
            ->setBet($bet3combination)
            ->setErrorText('ERROR')
            ->setTime(CarbonImmutable::now())
        ;
        $bet3combination->setFailed($failedBet)->setInvalid(true);

        $combination = (new Combination())
            ->setCurrency($bet3combination->getCurrency())
            ->setPlayer($bet3combination->getPlayer())
            ->setAmount($bet3combination->getAmount())
            ->setOddValue($bet3combination->getOddValue())
            ->setDateCreated($bet3combination->getTime())
            ->setInvalid(true)
            ->addBet($bet3combination);

        $manager->persist($combination);


        $manager->flush();
    }
}
