<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\BetHistory;

use Acme\SymfonyDb\Entity\GameItem;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\GameRunResult;
use Acme\SymfonyDb\Entity\GameRunResultItem;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class BetHistoryRunResultsFixture
 */
final class BetHistoryRunResultsFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var GameRun $lotteryRun */
        $lotteryRun = $this->getReference('game:1:run');

        $lotteryRun->setResultsEntered(true);
        $manager->persist($lotteryRun);

        $lotteryRunResult = (new GameRunResult())
            ->setIsConfirmed(true)
            ->setGameRun($lotteryRun)
            ->setPublishedDate(CarbonImmutable::now());
        $manager->persist($lotteryRunResult);

        /** @var GameItem $lotteryItem */
        $lotteryItem = $this->getReference('lottery-item:1:1');
        $lotteryRunResultItem = (new GameRunResultItem())
            ->setGameItem($lotteryItem)
            ->setGameRunResult($lotteryRunResult)
            ->setOrder(0);
        $manager->persist($lotteryRunResultItem);

        $manager->flush();
    }
}
