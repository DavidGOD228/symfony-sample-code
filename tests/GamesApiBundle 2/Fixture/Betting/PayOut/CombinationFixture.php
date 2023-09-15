<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Betting\PayOut;

use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Combination;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class CombinationFixture
 */
final class CombinationFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Bet $bet1 */
        $bet1 = $this->getReference('bet:1:valid');
        /** @var Bet $bet2 */
        $bet2 = $this->getReference('bet:3:valid');

        $combination = (new Combination())
            ->setIsPayouted(false)
            ->setDateCreated(CarbonImmutable::now())
            ->setValid(true)
            ->setCurrency($bet1->getCurrency())
            ->setPlayer($bet1->getPlayer())
            ->addBet($bet1)
            ->addBet($bet2)
            ->setAmount(10)
            ->setAmountWon(55)
            ->setOddValue(5);
        $this->addReference('combination:1', $combination);

        $bet1->setCombination($combination);
        $bet2->setCombination($combination);

        $manager->persist($combination);
        $manager->flush();
    }
}
