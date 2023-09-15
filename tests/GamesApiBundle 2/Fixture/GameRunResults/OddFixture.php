<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\GameRunResults;

use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\Odd;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class OddsFixture
 */
final class OddFixture extends Fixture
{
    private const BACCARAT_ODDS_CLASSES = [
        'PLAYER',
        'BANKER',
        'TIE',
        'PAIR_PLAYER',
        'PAIR_BANKER',
        'PAIR_ANY',
        'PAIR_PERFECT',
        'HAND_SMALL',
        'HAND_BIG',
        'MORE_RED_CARDS',
        'MORE_BLACK_CARDS',
        'RED_BLACK_EQUAL',
        'ALL_CARDS_RED',
        'ALL_CARDS_BLACK',
        'ALL_CARDS_SAME_SUIT',
        'PLAYER_FIRST_TWO_CARDS_SAME_SUIT',
        'BANKER_FIRST_TWO_CARDS_SAME_SUIT',
        'PLAYER_BANKER_TOTAL_LESS_95',
        'PLAYER_BANKER_TOTAL_MORE_95',
        'PLAYER_BANKER_TOTAL_EVEN',
        'PLAYER_BANKER_TOTAL_ODD',
        'PLAYER_SCORE_EVEN',
        'PLAYER_SCORE_ODD',
        'BANKER_SCORE_EVEN',
        'BANKER_SCORE_ODD',
        'NEXT_CARD_RED',
        'NEXT_CARD_BLACK',
        'NEXT_CARD_SPADES',
        'NEXT_CARD_HEARTS',
        'NEXT_CARD_CLUBS',
        'NEXT_CARD_DIAMONDS',
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Game $game5 */
        $game5 = $this->getReference('game:5');
        /** @var Game $game6 */
        $game6 = $this->getReference('game:6');

        foreach (self::BACCARAT_ODDS_CLASSES as $index => $class) {
            $odd = (new Odd())
                ->setClass($class)
                ->setOrder($index)
                ->setCode('')
                ->setGame($game6)
                ->setDescription('')
                ->setItemsCount(0)
                ->setProbability(1)
                ->setStatusInPresets(true)
                ->setIsTopOdd(false)
                ->setIsPokerOdd(false)
                ->setShowInRandom(false)
                ->setEnabled(true)
                ->setPublishedDate(Carbon::now())
                ->setPublishedBy(1)
                ->setType('classic')
            ;
            $manager->persist($odd);
        }

        $pokerOddForCollisionTesting = (new Odd())
            ->setClass($class)
            ->setOrder($index)
            ->setCode('')
            ->setGame($game5)
            ->setDescription('')
            ->setItemsCount(0)
            ->setProbability(1)
            ->setStatusInPresets(true)
            ->setIsTopOdd(false)
            ->setIsPokerOdd(false)
            ->setShowInRandom(false)
            ->setEnabled(true)
            ->setPublishedDate(Carbon::now())
            ->setPublishedBy(1)
            ->setType('classic')
        ;
        $manager->persist($pokerOddForCollisionTesting);

        $manager->flush();
    }
}
