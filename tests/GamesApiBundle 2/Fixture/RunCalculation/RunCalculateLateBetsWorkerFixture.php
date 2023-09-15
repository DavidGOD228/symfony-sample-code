<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\RunCalculation;

use Acme\SymfonyDb\Entity\GameItem;
use Acme\SymfonyDb\Entity\GameRunResult;
use Acme\SymfonyDb\Entity\GameRunResultItem;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class RunCalculateLateBetsWorkerFixture
 */
final class RunCalculateLateBetsWorkerFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->addRunResultItemsToLucky7Run($manager);

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    private function addRunResultItemsToLucky7Run(ObjectManager $manager): void
    {
        /** @var GameRunResult $gameRunResult */
        $gameRunResult = $this->getReference('game:1:result');
        /** @var GameItem $lotteryItem1 */
        $lotteryItem1 = $this->getReference('lottery-item:1:1');

        $item1 = (new GameRunResultItem())
            ->setGameItem($lotteryItem1)
            ->setOrder(1);
        $gameRunResult->addGameRunResultItem($item1);
        $manager->persist($gameRunResult);
    }
}
