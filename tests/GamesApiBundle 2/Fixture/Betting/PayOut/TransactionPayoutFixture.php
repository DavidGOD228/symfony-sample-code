<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Betting\PayOut;

use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\TransactionPayout;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class TransactionPayoutFixture
 */
class TransactionPayoutFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Bet $bet */
        $bet = $this->getReference('bet:1:valid');

        $transactionPayout = (new TransactionPayout())
            ->setBet($bet)
            ->setTransaction($bet->getTransactionAmountBet())
            ->setTimeCreated(CarbonImmutable::now());

        $bet->setTransactionPayout($transactionPayout);

        $manager->persist($bet);
        $manager->flush();
    }
}
