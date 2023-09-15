<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\GameRunResults;

use Acme\SymfonyDb\Entity\AbRunRoundCard;
use Acme\SymfonyDb\Entity\PokerRunCard;
use Acme\SymfonyDb\Entity\WarRunCard;
use Doctrine\Common\Collections\ArrayCollection;
use GamesApiBundle\Service\GameRunResults\CardsSorter;
use SymfonyTests\_support\Doctrine\EntityHelper;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class CardsSorterCest
 */
final class CardsSorterCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     */
    public function testSortingAndarBahar(UnitTester $I): void
    {
        $cards = [
            (new AbRunRoundCard())->setNumber(3),
            (new AbRunRoundCard())->setNumber(5),
            (new AbRunRoundCard())->setNumber(1),
            (new AbRunRoundCard())->setNumber(4),
            (new AbRunRoundCard())->setNumber(2),
        ];

        $sorter = new CardsSorter();
        /** @var AbRunRoundCard[] $sortedCards */
        $sortedCards = $sorter->sortAndarBahar($cards);
        $I->assertCount(5, $sortedCards);

        $expectedNumber = 1;
        foreach ($sortedCards as $card) {
            $I->assertEquals($expectedNumber, $card->getNumber());
            $expectedNumber++;
        }
    }

    /**
     * @param UnitTester $I
     */
    public function testSortingAndarBaharFromCollection(UnitTester $I): void
    {
        $cards = new ArrayCollection([
            (new AbRunRoundCard())->setNumber(3),
            (new AbRunRoundCard())->setNumber(5),
            (new AbRunRoundCard())->setNumber(1),
            (new AbRunRoundCard())->setNumber(4),
            (new AbRunRoundCard())->setNumber(2),
        ]);

        $sorter = new CardsSorter();
        /** @var AbRunRoundCard[] $sortedCards */
        $sortedCards = $sorter->sortAndarBahar($cards);
        $I->assertCount(5, $sortedCards);

        $expectedNumber = 1;
        foreach ($sortedCards as $card) {
            $I->assertEquals($expectedNumber, $card->getNumber());
            $expectedNumber++;
        }
    }

    /**
     * @param UnitTester $I
     *
     * @throws \ReflectionException
     */
    public function testSortingCollection(UnitTester $I): void
    {
        $cards = new ArrayCollection([
            EntityHelper::getEntityWithId(WarRunCard::class, 2),
            EntityHelper::getEntityWithId(WarRunCard::class, 1),
        ]);

        $sorter = new CardsSorter();
        /** @var WarRunCard[] $sortedCards */
        $sortedCards = $sorter->sortPoker($cards);
        $I->assertCount(2, $sortedCards);

        $expectedNumber = 1;
        foreach ($sortedCards as $card) {
            $I->assertEquals($expectedNumber, $card->getId());
            $expectedNumber++;
        }
    }

    /**
     * @param UnitTester $I
     *
     * @throws \ReflectionException
     */
    public function testSortingPoker(UnitTester $I): void
    {
        $cards = [
            EntityHelper::getEntityWithId(PokerRunCard::class, 3),
            EntityHelper::getEntityWithId(PokerRunCard::class, 2),
            EntityHelper::getEntityWithId(PokerRunCard::class, 4),
            EntityHelper::getEntityWithId(PokerRunCard::class, 1),
        ];

        $sorter = new CardsSorter();
        /** @var PokerRunCard[] $sortedCards */
        $sortedCards = $sorter->sortPoker($cards);
        $I->assertCount(4, $sortedCards);

        $expectedNumber = 1;
        foreach ($sortedCards as $card) {
            $I->assertEquals($expectedNumber, $card->getId());
            $expectedNumber++;
        }
    }
}
