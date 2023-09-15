<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\BetHistory;

use Acme\SymfonyDb\Entity\BazaarBet;
use Acme\SymfonyDb\Entity\BazaarRun;
use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Combination;
use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\MatkaCard;
use Acme\SymfonyDb\Entity\MatkaRunCard;
use Acme\SymfonyDb\Entity\MatkaRunRound;
use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Entity\Subscription;
use Acme\SymfonyDb\Entity\Transaction;
use Acme\SymfonyDb\Entity\TransactionType;
use Acme\SymfonyDb\Type\BazaarBetType;
use Acme\SymfonyDb\Type\BetStatusType;
use Acme\SymfonyDb\Type\BetType;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class BetHistoryFixture
 */
final class BetHistoryFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadRubyMidnightYesterday($manager);
        $this->loadTaraTwilightYesterday($manager);

        /** @var Player $player1 */
        $player1 = $this->getReference('player:1');
        /** @var Currency $currency */
        $currency = $this->getReference('currency:eur');
        /** @var GameRun $game1Run */
        $game1Run = $this->getReference('game:1:run');
        /** @var Odd $game1Odd */
        $game1Odd = $this->getReference('odd:1:1');

        // Dummy transaction, not used, but required because of FK.
        /** @var TransactionType $transactionType */
        $transactionType = $this->getReference('transaction-type:1');
        $transaction = (new Transaction());

        // Invalid bet
        $betInvalid = (new Bet())
            ->setAmount(1)
            ->setOddValue(1.5)
            ->setFinalOddValue(1.5)
            ->setStatus(BetStatusType::INVALID)
            ->setTransactionAmountBet($transaction)
            ->setOdd($game1Odd)
            ->setPlayer($player1)
            ->setGameRun($game1Run)
            ->setCurrency($currency)
            ->setIsWidget(false)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsMobile(false)
            ->setInvalid(true)
            ->setTime(new CarbonImmutable('2020-01-01 00:00:00'))
            ->setIp('ip')
            ->setType(BetType::SINGLE)
        ;
        $manager->persist($betInvalid);

        $singleBetValid = (new Bet())
            ->setAmount(1)
            ->setOddValue(1.5)
            ->setFinalOddValue(1.5)
            ->setAmountWon(1.5)
            ->setStatus(BetStatusType::WON)
            ->setTransactionAmountBet($transaction)
            ->setOdd($game1Odd)
            ->setPlayer($player1)
            ->setGameRun($game1Run)
            ->setCurrency($currency)
            ->setIsWidget(false)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setTime(new CarbonImmutable('2020-01-01 00:00:00'))
            ->setIp('ip')
            ->setType(null)
        ;
        $manager->persist($singleBetValid);

        $subscriptionBetValid = (new Bet())
            ->setAmount(1)
            ->setOddValue(1.5)
            ->setFinalOddValue(1.5)
            ->setStatus(BetStatusType::ACTIVE)
            ->setTransactionAmountBet($transaction)
            ->setOdd($game1Odd)
            ->setPlayer($player1)
            ->setGameRun($game1Run)
            ->setCurrency($currency)
            ->setIsWidget(false)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setTime(new CarbonImmutable('2020-01-02 01:00:00'))
            ->setIp('ip')
            ->setType(BetType::SUBSCRIPTION)
        ;
        $subscription = (new Subscription())
            ->setAmount(3)
            ->setAmountWon(4.5)
            ->setOddValue(1.5)
            ->setBetsCalculated(3)
            ->setBetsTotal(3)
            ->setInvalid(false)
            ->setDateCreated(new CarbonImmutable('2020-01-02 00:00:00'))
            ->setOdd($game1Odd)
            ->setPlayer($player1)
            ->setCurrency($currency)
            ->addBet($subscriptionBetValid)
            ->setIsReturned(false)
        ;
        $manager->persist($subscription);

        $combinationBetValid = (new Bet())
            ->setAmount(1)
            ->setOddValue(1.5)
            ->setFinalOddValue(1.5)
            ->setStatus(BetStatusType::WON)
            ->setTransactionAmountBet($transaction)
            ->setOdd($game1Odd)
            ->setPlayer($player1)
            ->setGameRun($game1Run)
            ->setCurrency($currency)
            ->setIsWidget(false)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setTime(new CarbonImmutable('2020-01-03 01:00:00'))
            ->setIp('ip')
            ->setType(BetType::COMBINATION)
        ;
        $combination = (new Combination())
            ->setAmount(3)
            ->setAmountWon(4.5)
            ->setOddValue(1.5)
            ->setInvalid(false)
            ->setDateCreated(new CarbonImmutable('2020-01-03 00:00:00'))
            ->setPlayer($player1)
            ->setCurrency($currency)
            ->addBet($combinationBetValid)
        ;
        $manager->persist($combination);

        /** @var BazaarRun $bazaarRunRubyMidnight */
        $bazaarRunRubyMidnight = $this->getReference('bazaar-run:ruby-midnight-yesterday');

        $bazaarOpeningRunBet = (new Bet())
            ->setAmount(1)
            ->setOddValue(1.5)
            ->setFinalOddValue(1.5)
            ->setStatus(BetStatusType::WON)
            ->setTransactionAmountBet($transaction)
            ->setOdd($game1Odd)
            ->setPlayer($player1)
            ->setGameRun($bazaarRunRubyMidnight->getOpeningRun())
            ->setCurrency($currency)
            ->setIsWidget(false)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setTime(new CarbonImmutable('2020-01-03 01:00:00'))
            ->setIp('ip')
            ->setType(BetType::BAZAAR)
        ;

        $bazaarOpenBetValid = (new BazaarBet())
            ->setBazaarRun($bazaarRunRubyMidnight)
            ->setBazaarBetType(BazaarBetType::OPEN)
            ->setBet($bazaarOpeningRunBet)
        ;
        $manager->persist($bazaarOpenBetValid);
        $bazaarOpeningRunBet->setBazaarBet($bazaarOpenBetValid);
        $manager->persist($bazaarOpeningRunBet);

        $bazaarClosingRunBet = (new Bet())
            ->setAmount(1)
            ->setOddValue(1.5)
            ->setFinalOddValue(1.5)
            ->setStatus(BetStatusType::WON)
            ->setTransactionAmountBet($transaction)
            ->setOdd($game1Odd)
            ->setPlayer($player1)
            ->setGameRun($bazaarRunRubyMidnight->getClosingRun())
            ->setCurrency($currency)
            ->setIsWidget(false)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setTime(new CarbonImmutable('2020-01-03 01:00:00'))
            ->setIp('ip')
            ->setType(BetType::BAZAAR)
        ;

        $bazaarCloseBetValid = (new BazaarBet())
            ->setBazaarRun($bazaarRunRubyMidnight)
            ->setBazaarBetType(BazaarBetType::CLOSE)
            ->setBet($bazaarClosingRunBet)
        ;
        $manager->persist($bazaarCloseBetValid);
        $bazaarClosingRunBet->setBazaarBet($bazaarCloseBetValid);
        $manager->persist($bazaarClosingRunBet);

        /** @var BazaarRun $bazaarRunTaraTwilight */
        $bazaarRunTaraTwilight = $this->getReference('bazaar-run:tara-twilight-yesterday');

        $bazaarOpeningRunBet = (new Bet())
            ->setAmount(1)
            ->setOddValue(1.5)
            ->setFinalOddValue(1.5)
            ->setStatus(BetStatusType::WON)
            ->setTransactionAmountBet($transaction)
            ->setOdd($game1Odd)
            ->setPlayer($player1)
            ->setGameRun($bazaarRunTaraTwilight->getOpeningRun())
            ->setCurrency($currency)
            ->setIsWidget(false)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setTime(new CarbonImmutable('2020-01-03 01:00:00'))
            ->setIp('ip')
            ->setType(BetType::BAZAAR)
        ;

        $bazaarOpenBetValid = (new BazaarBet())
            ->setBazaarRun($bazaarRunTaraTwilight)
            ->setBazaarBetType(BazaarBetType::OPEN)
            ->setBet($bazaarOpeningRunBet)
        ;
        $manager->persist($bazaarOpenBetValid);
        $bazaarOpeningRunBet->setBazaarBet($bazaarOpenBetValid);
        $manager->persist($bazaarOpeningRunBet);

        $bazaarClosingRunBet = (new Bet())
            ->setAmount(1)
            ->setOddValue(1.5)
            ->setFinalOddValue(1.5)
            ->setStatus(BetStatusType::WON)
            ->setTransactionAmountBet($transaction)
            ->setOdd($game1Odd)
            ->setPlayer($player1)
            ->setGameRun($bazaarRunTaraTwilight->getClosingRun())
            ->setCurrency($currency)
            ->setIsWidget(false)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setTime(new CarbonImmutable('2020-01-03 01:00:00'))
            ->setIp('ip')
            ->setType(BetType::BAZAAR)
        ;

        $bazaarCloseBetValid = (new BazaarBet())
            ->setBazaarRun($bazaarRunTaraTwilight)
            ->setBazaarBetType(BazaarBetType::CLOSE)
            ->setBet($bazaarClosingRunBet)
        ;
        $manager->persist($bazaarCloseBetValid);
        $bazaarClosingRunBet->setBazaarBet($bazaarCloseBetValid);
        $manager->persist($bazaarClosingRunBet);

        /** @var Player $player2 */
        $player2 = $this->getReference('player:2');

        $singleBetValid = (new Bet())
            ->setAmount(1)
            ->setOddValue(1.5)
            ->setFinalOddValue(1.5)
            ->setStatus(BetStatusType::ACTIVE)
            ->setTransactionAmountBet($transaction)
            ->setOdd($game1Odd)
            ->setPlayer($player2)
            ->setGameRun($game1Run)
            ->setCurrency($currency)
            ->setIsWidget(false)
            ->setIsReturned(false)
            ->setIsPaidOut(false)
            ->setIsMobile(false)
            ->setInvalid(false)
            ->setTime(new CarbonImmutable('2020-01-04 00:00:00'))
            ->setIp('ip')
            ->setType(BetType::SINGLE)
        ;
        $manager->persist($singleBetValid);

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    private function loadRubyMidnightYesterday(ObjectManager $manager): void
    {
        $openingTime = new CarbonImmutable('2021-09-05 16:30:00');

        $openingRunRound = $this->getDefaultRunRound()
            ->setTime($openingTime)
        ;

        $card1 = $this->getRunCard('hearts', '2', $openingRunRound);
        $card2 = $this->getRunCard('spades', '2', $openingRunRound);
        $card3 = $this->getRunCard('diamonds', '2', $openingRunRound);

        $openingRun = $this->getDefaultGameRun()
            ->setCode('ruby-midnight-yesterday-open')
            ->setTime($openingTime)
            ->setMatkaRunRound($openingRunRound)
            ->addMatkaRunCard($card1)
            ->addMatkaRunCard($card2)
            ->addMatkaRunCard($card3)
        ;
        $manager->persist($openingRun);

        $closingTime = new CarbonImmutable('2021-09-05 18:00:00');

        $closingRunRound = $this->getDefaultRunRound()
            ->setTime($closingTime)
        ;

        $card4 = $this->getRunCard('clubs', 'a', $closingRunRound);
        $card5 = $this->getRunCard('diamonds', '3', $closingRunRound);
        $card6 = $this->getRunCard('spades', '10', $closingRunRound);

        $closingRun = $this->getDefaultGameRun()
            ->setCode('ruby-midnight-yesterday-close')
            ->setTime($closingTime)
            ->setMatkaRunRound($closingRunRound)
            ->addMatkaRunCard($card4)
            ->addMatkaRunCard($card5)
            ->addMatkaRunCard($card6)
        ;
        $manager->persist($closingRun);

        $bazaarRun = (new BazaarRun)
            ->setTitle('Ruby Midnight')
            ->setCode('ruby-midnight-yesterday-open:ruby-midnight-yesterday-close')
            ->setIsReturned(false)
            ->setOpeningRun($openingRun)
            ->setClosingRun($closingRun)
        ;
        $manager->persist($bazaarRun);

        $manager->flush();

        $this->setReference('bazaar-run:ruby-midnight-yesterday', $bazaarRun);
    }

    /**
     * @param ObjectManager $manager
     */
    private function loadTaraTwilightYesterday(ObjectManager $manager): void
    {
        $openingTime = new CarbonImmutable('2021-09-06 12:30:00');

        $openingRunRound = $this->getDefaultRunRound()
            ->setTime($openingTime)
        ;

        $card1 = $this->getRunCard('hearts', '7', $openingRunRound);
        $card2 = $this->getRunCard('spades', '9', $openingRunRound);
        $card3 = $this->getRunCard('clubs', '2', $openingRunRound);

        $openingRun = $this->getDefaultGameRun()
            ->setCode('tara-twilight-yesterday-open')
            ->setTime($openingTime)
            ->setIsReturned(true)
            ->setMatkaRunRound($openingRunRound)
            ->addMatkaRunCard($card1)
            ->addMatkaRunCard($card2)
            ->addMatkaRunCard($card3)
        ;
        $manager->persist($openingRun);

        $closingTime = new CarbonImmutable('2021-09-06 13:30:00');

        $closingRunRound = $this->getDefaultRunRound()
            ->setTime($closingTime)
        ;

        $card4 = $this->getRunCard('clubs', '2', $closingRunRound);
        $card5 = $this->getRunCard('hearts', '5', $closingRunRound);
        $card6 = $this->getRunCard('spades', '6', $closingRunRound);

        $closingRun = $this->getDefaultGameRun()
            ->setCode('tara-twilight-yesterday-close')
            ->setTime($closingTime)
            ->setMatkaRunRound($closingRunRound)
            ->setResultsEntered(false)
            ->addMatkaRunCard($card4)
            ->addMatkaRunCard($card5)
            ->addMatkaRunCard($card6)
        ;
        $manager->persist($closingRun);

        $bazaarRun = (new BazaarRun)
            ->setTitle('Tara Twilight')
            ->setCode('tara-twilight-yesterday-open:tara-twilight-yesterday-close')
            ->setIsReturned(false)
            ->setOpeningRun($openingRun)
            ->setClosingRun($closingRun)
        ;
        $manager->persist($bazaarRun);

        $manager->flush();

        $this->setReference('bazaar-run:tara-twilight-yesterday', $bazaarRun);
    }

    /**
     * @return GameRun
     */
    private function getDefaultGameRun(): GameRun
    {
        /** @var Game $game */
        $game = $this->getReference('game:18');

        return (new GameRun)
            ->setGame($game)
            ->setIsImported(true)
            ->setIsReturned(false)
            ->setResultsEntered(true)
            ->setVideoConfirmationRequired(false)
            ->setPublishedDate(new CarbonImmutable('2021-08-30 03:00:00'))
            ;
    }

    /**
     * @return MatkaRunRound
     */
    private function getDefaultRunRound(): MatkaRunRound
    {
        $matkaRunRound = new MatkaRunRound();

        $matkaRunRound
            ->setTime(CarbonImmutable::now())
            ->setResultsEntered(true)
            ->setIsActive(false)
            ->setRoundNumber(1)
        ;

        return $matkaRunRound;
    }

    /**
     * @param string $suit
     * @param string $value
     * @param MatkaRunRound $matkaRunRound
     *
     * @return MatkaRunCard
     */
    private function getRunCard(string $suit, string $value, MatkaRunRound $matkaRunRound): MatkaRunCard
    {
        /** @var MatkaCard $card */
        $card = $this->getReference("matka_card:$suit:$value");

        return (new MatkaRunCard())
            ->setCard($card)
            ->setIsConfirmed(true)
            ->setRunRound($matkaRunRound)
            ->setEnteredAt(CarbonImmutable::now())
            ;
    }
}
