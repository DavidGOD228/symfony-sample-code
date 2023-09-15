<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification;

use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Entity\RpsBet;
use Acme\SymfonyDb\Type\RpsDealtToType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;

/**
 * Class RpsBetFixture
 */
class RpsBetFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        /** @var Player $player */
        $player = $this->getReference('player2:gamification');
        $rounds = [
            $this->getReference('rps-run-round:first'),
            $this->getReference('rps-run-round:second')
        ];

        /** @var Odd $odds */
        $odds = $this->getReference('odd:15:1');
        $odds->setClass('ZONE_1_ROCK');

        /** @var Bet $bet */
        $bet = $this->getReference('bet:1:1');

        foreach ($rounds as $round) {
            $roundBet = (new RpsBet())
                ->setOdds($odds)
                ->setZone(RpsDealtToType::ZONE_1)
                ->setPushValue(2.22)
                ->setBet($bet)
                ->setPlayer($player)
                ->setRunRound($round)
            ;
            $manager->persist($roundBet);
        }
        $manager->flush();
    }
}
