<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\GameRunResults;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\GameItem;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\GameRunResult;
use Acme\SymfonyDb\Entity\GameRunResultItem;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class ListLucky5Lucky7Fixture
 */
class ListLucky5Lucky7Fixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $gameResult = (new GameRunResult())
            ->setIsConfirmed(true)
            ->setPublishedDate(CarbonImmutable::now());

        /** @var GameItem $gameItem1 */
        $gameItem1 = $this->getReference('lottery-item:1:1');
        $gameRunResultItem1 = (new GameRunResultItem())
            ->setGameRunResult($gameResult)
            ->setGameItem($gameItem1)
            ->setOrder(1);
        $manager->persist($gameRunResultItem1);

        /** @var GameItem $gameItem2 */
        $gameItem2 = $this->getReference('lottery-item:1:4');
        $gameRunResultItem2 = (new GameRunResultItem())
            ->setGameRunResult($gameResult)
            ->setGameItem($gameItem2)
            ->setOrder(1);
        $manager->persist($gameRunResultItem2);

        $gameResult->addGameRunResultItem($gameRunResultItem1);
        $gameResult->addGameRunResultItem($gameRunResultItem2);

        $manager->persist($gameResult);

        /** @var GameRun $lucky7run */
        $lucky7run = $this->getReference('game:' . GameDefinition::LUCKY_7 . ':run');
        $lucky7run->setResultsEntered(true)
            ->addGameRunResult($gameResult)
            ->setVideoUrl('/stream742/201216/72012160102.mp4');
        $manager->persist($lucky7run);

        $gameResult->setGameRun($lucky7run);

        /** @var GameRun $lucky5run */
        $lucky5run = $this->getReference('game:' . GameDefinition::LUCKY_5 . ':run');
        $lucky5run->setResultsEntered(true)
            ->setVideoUrl('/stream536/201216/52012160099.mp4');
        $manager->persist($lucky5run);

        $manager->flush();
    }
}
