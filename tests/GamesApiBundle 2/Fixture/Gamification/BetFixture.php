<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification;

use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Combination;
use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Entity\RpsBet;
use Acme\SymfonyDb\Entity\RpsRunRound;
use Acme\SymfonyDb\Entity\Subscription;
use Acme\SymfonyDb\Entity\Transaction;
use Acme\SymfonyDb\Type\RpsDealtToType;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class BetFixture
 */
final class BetFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        /** @var Odd $odd */
        $odd = $this->getReference('odd:1:1');
        /** @var Odd $oddRps */
        $oddRps = $this->getReference('odd:15:1');
        /** @var Transaction $transaction */
        $transaction = $this->getReference('transaction:1');
        /** @var Player $player1 */
        $player1 = $this->getReference('player1:gamification');
        /** @var Player $player2 */
        $player2 = $this->getReference('player2:gamification');
        /** @var Player $player3 */
        $player3 = $this->getReference('player3:gamification');
        /** @var Currency $currency */
        $currency = $this->getReference('currency:eur');
        $currency->setCode('eur');
        /** @var GameRun $run */
        $run = $this->getReference('game:1:run');
        /** @var GameRun $runRps */
        $runRps = $this->getReference('game:15:run');
        /** @var Subscription $subscription */
        $subscription = $this->getReference('subscription:1');
        /** @var Combination $combination */
        $combination = $this->getReference('combination:1');
        /** @var RpsRunRound $rpsRunRound */
        $rpsRunRound = $this->getReference('rps-run-round:first');

        $bet1 = (new Bet())
            ->setTime(new DateTimeImmutable('2021-03-08 00:00:00'))
            ->setAmount(10)
            ->setAmountWon(100)
            ->setTransactionAmountBet($transaction)
            ->setOdd($odd)
            ->setPlayer($player1)
            ->setGameRun($run)
            ->setCurrency($currency)
            ->setOddValue(10)
            ->setFinalOddValue(10)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsWidget(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setRunRoundId(1)
            ->setStatus(1)
            ->setIp('ip')
        ;
        $manager->persist($bet1);

        $bet2 = (new Bet())
            ->setTime(new DateTimeImmutable('2021-03-08 00:00:00'))
            ->setAmount(11)
            ->setAmountWon(100)
            ->setTransactionAmountBet($transaction)
            ->setOdd($odd)
            ->setFinalOddValue(10)
            ->setPlayer($player2)
            ->setGameRun($run)
            ->setCurrency($currency)
            ->setOddValue(10)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsWidget(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setRunRoundId(1)
            ->setStatus(1)
            ->setIp('ip')
        ;
        $manager->persist($bet2);

        $rpsBet = (new RpsBet())
            ->setOdds($oddRps)
            ->setZone(RpsDealtToType::ZONE_1)
            ->setPushValue(2.22)
            ->setPlayer($player2)
            ->setRunRound($rpsRunRound)
        ;

        $bet3 = (new Bet())
            ->setTime(new DateTimeImmutable('2021-03-08 00:00:00'))
            ->setAmount(23)
            ->setAmountWon(100)
            ->setTransactionAmountBet($transaction)
            ->setOdd($oddRps)
            ->setFinalOddValue(10)
            ->setPlayer($player2)
            ->setGameRun($runRps)
            ->setCurrency($currency)
            ->setOddValue(10)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsWidget(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setRunRoundId($rpsRunRound->getId())
            ->setStatus(1)
            ->setRpsBet($rpsBet)
            ->setIp('ip')
        ;
        $manager->persist($bet3);


        $bet4 = (new Bet())
            ->setTime(new DateTimeImmutable('2021-03-08 00:00:00'))
            ->setAmount(10)
            ->setAmountWon(100)
            ->setTransactionAmountBet($transaction)
            ->setOdd($odd)
            ->setFinalOddValue(10)
            ->setPlayer($player1)
            ->setGameRun($run)
            ->setCurrency($currency)
            ->setOddValue(10)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsWidget(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setRunRoundId(1)
            ->setSubscription($subscription)
            ->setStatus(0)
            ->setIp('ip')
        ;
        $manager->persist($bet4);

        $bet5 = (new Bet())
            ->setTime(new DateTimeImmutable('2021-03-08 00:00:00'))
            ->setAmount(10)
            ->setAmountWon(100)
            ->setTransactionAmountBet($transaction)
            ->setOdd($odd)
            ->setFinalOddValue(10)
            ->setPlayer($player1)
            ->setGameRun($run)
            ->setCurrency($currency)
            ->setOddValue(10)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsWidget(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setRunRoundId(1)
            ->setSubscription($subscription)
            ->setStatus(4)
            ->setIp('ip')
        ;
        $manager->persist($bet5);

        $bet6 = (new Bet())
            ->setTime(new DateTimeImmutable('2021-03-08 00:00:00'))
            ->setAmount(10)
            ->setAmountWon(100)
            ->setTransactionAmountBet($transaction)
            ->setOdd($odd)
            ->setFinalOddValue(10)
            ->setPlayer($player1)
            ->setGameRun($run)
            ->setCurrency($currency)
            ->setOddValue(10)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsWidget(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setRunRoundId(1)
            ->setSubscription($subscription)
            ->setStatus(1)
            ->setIp('ip')
        ;
        $manager->persist($bet6);

        $bet7 = (new Bet())
            ->setTime(new DateTimeImmutable('2021-03-08 00:00:00'))
            ->setAmount(10)
            ->setAmountWon(100)
            ->setTransactionAmountBet($transaction)
            ->setOdd($odd)
            ->setFinalOddValue(10)
            ->setPlayer($player1)
            ->setGameRun($run)
            ->setCurrency($currency)
            ->setOddValue(10)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsWidget(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setRunRoundId(1)
            ->setCombination($combination)
            ->setStatus(2)
            ->setIp('ip')
        ;
        $manager->persist($bet7);

        $bet8 = (new Bet())
            ->setTime(new DateTimeImmutable('2021-03-08 00:00:00'))
            ->setAmount(10)
            ->setAmountWon(100)
            ->setTransactionAmountBet($transaction)
            ->setOdd($oddRps)
            ->setFinalOddValue(10)
            ->setPlayer($player1)
            ->setGameRun($runRps)
            ->setCurrency($currency)
            ->setOddValue(10)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsWidget(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setRunRoundId(1)
            ->setCombination($combination)
            ->setStatus(3)
            ->setIp('ip')
        ;
        $manager->persist($bet8);

        $bet9 = (new Bet())
            ->setTime(new DateTimeImmutable('2021-03-08 00:00:00'))
            ->setAmount(10)
            ->setAmountWon(100)
            ->setTransactionAmountBet($transaction)
            ->setOdd($oddRps)
            ->setFinalOddValue(10)
            ->setPlayer($player3)
            ->setGameRun($runRps)
            ->setCurrency($currency)
            ->setOddValue(10)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsWidget(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setRunRoundId(1)
            ->setStatus(4)
            ->setIp('ip')
        ;
        $manager->persist($bet9);

        $bet10 = (new Bet())
            ->setTime(new DateTimeImmutable('2021-03-08 00:00:00'))
            ->setAmount(11)
            ->setAmountWon(100)
            ->setTransactionAmountBet($transaction)
            ->setOdd($odd)
            ->setFinalOddValue(10)
            ->setPlayer($player2)
            ->setGameRun($run)
            ->setCurrency($currency)
            ->setOddValue(10)
            ->setIsReturned(true)
            ->setIsPaidOut(false)
            ->setIsWidget(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setRunRoundId(1)
            ->setStatus(4)
            ->setIp('ip')
        ;
        $manager->persist($bet10);

        $manager->flush();

        $this->addReference('bet1:gamification', $bet1);
        $this->addReference('bet2:gamification', $bet2);
        $this->addReference('bet3rps:gamification', $bet3);
        $this->addReference('bet4sub:gamification', $bet4);
        $this->addReference('bet5sub:gamification', $bet5);
        $this->addReference('bet6sub:gamification', $bet6);
        $this->addReference('bet7combo:gamification', $bet7);
        $this->addReference('bet8combo:gamification', $bet8);
        $this->addReference('bet9:gamification', $bet9);
        $this->addReference('bet10:gamification', $bet10);
    }
}
