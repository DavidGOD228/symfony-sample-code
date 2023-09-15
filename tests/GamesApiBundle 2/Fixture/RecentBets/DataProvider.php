<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\RecentBets;

use Acme\SymfonyDb\Entity\BazaarBet;
use Acme\SymfonyDb\Entity\BazaarRun;
use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Combination;
use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Entity\Subscription;
use Acme\SymfonyDb\Type\BazaarBetType;
use Acme\SymfonyDb\Type\BetStatusType;
use Acme\SymfonyDb\Type\BetType;
use DateTimeImmutable;
use SymfonyTests\_support\Doctrine\EntityHelper;

/**
 * Simple class for generating Entities without EM.
 */
final class DataProvider
{
    private int $betId = 1;
    private int $subscriptionId = 60;
    private int $combinationId = 50;
    private int $bazaarId = 90;

    private GameRun $gameRun;
    private Currency $currency;
    private Player $player;
    private Odd $odd;

    /**
     * DataProvider constructor.
     *
     * @param int $gameId
     */
    public function __construct(int $gameId)
    {
        $this->gameRun = (new GameRun())
            ->setCode('54327')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setVideoUrl(null)
            ->setTime(new \DateTimeImmutable('2020-01-01 00:00:00'));
        EntityHelper::setId($this->gameRun, 65536);

        $this->currency = Currency::createFromId(20)
            ->setTemplate('eurX');

        $this->player = (new Player())
            ->setTag('existing')
            ->setTaggedAt(new DateTimeImmutable())
        ;
        EntityHelper::setId($this->player, 123);

        $game = Game::createFromId($gameId);

        $this->odd = (new Odd())
            ->setClass('MY_ODD')
            ->setItemsCount(0)
            ->setGame($game);
        EntityHelper::setId($this->odd, 5);
    }

    /**
     * @return Player
     */
    public function getPlayer(): Player
    {
        return $this->player;
    }

    /**
     * @return Bet
     */
    public function getNewBet(): Bet
    {
        $bet = (new Bet())
            ->setAmount(5.12)
            ->setOddValue(1.05)
            ->setGameRun($this->gameRun)
            ->setCurrency($this->currency)
            ->setOdd($this->odd)
            ->setPlayer($this->player)
            ->setStatus(BetStatusType::ACTIVE)
            ->setType(BetType::SINGLE)
            ->setIsReturned(false)
            ->setIsWidget(false)
            ->setTime(new \DateTimeImmutable('2020-01-05 00:00:00'))
        ;
        EntityHelper::setId($bet, $this->betId++);

        return $bet;
    }

    /**
     * @return BazaarBet
     */
    public function getNewBazaar(): BazaarBet
    {
        $bazaarRun = (new BazaarRun)
            ->setTitle('Ruby Midnight')
            ->setCode('ruby-midnight-yesterday-open:ruby-midnight-yesterday-close')
            ->setIsReturned(false)
            ->setOpeningRun($this->gameRun)
            ->setClosingRun($this->gameRun)
        ;

        $bazaarBet = (new BazaarBet())
            ->setBazaarRun($bazaarRun)
            ->setBazaarBetType(BazaarBetType::OPEN)
        ;
        EntityHelper::setId($bazaarBet, $this->bazaarId++);

        $bet = $this
            ->getNewBet()
            ->setBazaarBet($bazaarBet)
            ->setType(BetType::BAZAAR)
        ;
        $bazaarBet->setBet($bet);

        return $bazaarBet;
    }

    /**
     * @return Subscription
     */
    public function getNewSubscription(): Subscription
    {
        $subscription = (new Subscription())
            ->setAmount(5)
            ->setOddValue(1.05)
            ->setCurrency($this->currency)
            ->setBetsCalculated(0)
            ->setBetsTotal(3)
            ->setPlayer($this->player)
            ->setDateCreated(new \DateTimeImmutable('2020-01-04 00:00:00'));
        EntityHelper::setId($subscription, $this->subscriptionId++);

        $bet1 = $this
            ->getNewBet()
            ->setSubscription($subscription)
            ->setType(BetType::SUBSCRIPTION)
        ;
        $subscription->addBet($bet1);
        $bet2 = $this
            ->getNewBet()
            ->setSubscription($subscription)
            ->setType(BetType::SUBSCRIPTION)
        ;
        $subscription->addBet($bet2);
        $bet3 = $this
            ->getNewBet()
            ->setSubscription($subscription)
            ->setType(BetType::SUBSCRIPTION)
        ;
        $subscription->addBet($bet3);

        return $subscription;
    }

    /**
     * @return Combination
     */
    public function getNewCombination(): Combination
    {
        $combination = (new Combination())
            ->setAmount(5)
            ->setOddValue(1.10)
            ->setCurrency($this->currency)
            ->setPlayer($this->player)
            ->setDateCreated(new \DateTimeImmutable('2020-01-04 00:00:00'));
        EntityHelper::setId($combination, $this->combinationId++);

        $bet1 = $this
            ->getNewBet()
            ->setCombination($combination)
            ->setType(BetType::COMBINATION)
        ;
        $combination->addBet($bet1);
        $bet2 = $this
            ->getNewBet()
            ->setCombination($combination)
            ->setType(BetType::COMBINATION)
        ;
        $combination->addBet($bet2);

        return $combination;
    }
}
