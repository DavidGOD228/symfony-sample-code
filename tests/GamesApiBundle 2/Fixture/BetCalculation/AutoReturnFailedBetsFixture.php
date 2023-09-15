<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\BetCalculation;

use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\BetApiFailed;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class AutoReturnFailedBetsFixture
 */
final class AutoReturnFailedBetsFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Bet $bet */
        $bet = $this->getReference('bet:7:invalid');


        $failedBet = (new BetApiFailed())
            ->setBet($bet)
            ->setIsFixed(false)
            ->setServer('')
            ->setErrorText('SOME_ERROR')
            ->setTime(new CarbonImmutable())
        ;

        $manager->persist($failedBet);
        $manager->flush();
    }
}
