<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\BetCalculation;

use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Combination;
use Acme\SymfonyDb\Entity\GameRunResult;
use Acme\SymfonyDb\Entity\GameRunResultItem;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class CombinationCalculateWorkerFixture
 */
final class CombinationCalculateWorkerFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Bet $bet1 */
        $bet1 = $this->getReference('bet:3:1');
        $bet1->setAmount(2.5);
        /** @var Bet $bet2 */
        $bet2 = $this->getReference('bet:3:2');
        $bet1->setAmount(2.5);

        $combination = (new Combination())
            ->setAmount($bet1->getAmount() + $bet2->getAmount())
            ->setOddValue($bet1->getOddValue() * $bet2->getOddValue())
            ->setPlayer($bet1->getPlayer())
            ->setCurrency($bet1->getCurrency())
            ->setDateCreated(new CarbonImmutable())
            ->setInvalid(false)
            ->addBet($bet1)
            ->addBet($bet2)
        ;

        $manager->persist($combination);

        $gameRun1 = $bet1->getGameRun();
        $gameRun1Result = (new GameRunResult())
            ->setGameRun($gameRun1)
            ->setIsConfirmed(true)
            ->setPublishedDate(new CarbonImmutable())
            ->addGameRunResultItem(
                (new GameRunResultItem())
                    ->setOrder(1)
                    ->setGameItem(
                        $this->getReference('lottery-item:3:1')
                    )
            )
        ;
        $gameRun1
            ->setResultsEntered(true)
            ->addGameRunResult($gameRun1Result)
        ;
        $manager->persist($gameRun1);

        $gameRun2 = $bet2->getGameRun();
        $gameRun2Result = (new GameRunResult())
            ->setGameRun($gameRun2)
            ->setIsConfirmed(true)
            ->setPublishedDate(new CarbonImmutable())
            ->addGameRunResultItem(
                (new GameRunResultItem())
                    ->setOrder(1)
                    ->setGameItem(
                        $this->getReference('lottery-item:3:1')
                    )
            )
        ;
        $gameRun2
            ->setResultsEntered(true)
            ->addGameRunResult($gameRun2Result)
        ;
        $manager->persist($gameRun2);

        $this->addReference('combination:1', $combination);

        $manager->flush();
    }
}
