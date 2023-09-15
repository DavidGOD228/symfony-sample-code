<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification;

use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Combination;
use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Entity\PlayerProfile;
use Acme\SymfonyDb\Entity\Subscription;
use Acme\SymfonyDb\Type\BetStatusType;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use SymfonyTests\_support\Doctrine\EntityHelper;

/**
 * Simple class for generating Entities without EM.
 */
final class DataProvider
{
    private int $betId = 1;

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
        $game = Game::createFromId($gameId);

        $this->gameRun = (new GameRun())
            ->setCode('54327')
            ->setGame($game)
            ->setIsReturned(false)
            ->setVideoUrl('any')
        ;

        $this->currency = Currency::createFromId(20)
            ->setApproximateRate(2)
            ->setCode('LT');

        $partner = (new Partner())
            ->setApiCode('partner-code')
            ->setGamificationEnabled(true);
        EntityHelper::setId($partner, 1);

        $profile = (new PlayerProfile())->setBlocked(false);

        $this->player = (new Player())
            ->setPartner($partner)
            ->setProfile($profile)
            ->setTag('existing')
            ->setTaggedAt(new DateTimeImmutable())
        ;
        $profile->setPlayer($this->player);
        EntityHelper::setId($this->player, 123);

        $this->odd = (new Odd())
            ->setClass('MY_ODD')
            ->setGame($game);
        EntityHelper::setId($this->odd, 555);
    }

    /**
     * @return Bet
     */
    public function getNewBet(): Bet
    {
        $bet = (new Bet())
            ->setRunRoundId(1)
            ->setOddValue(1.05)
            ->setAmount(1)
            ->setAmountWon(1.10)
            ->setGameRun($this->gameRun)
            ->setCurrency($this->currency)
            ->setOdd($this->odd)
            ->setPlayer($this->player)
            ->setStatus(BetStatusType::WON)
            ->setTime(new \DateTimeImmutable('2020-01-05 00:00:00'))
        ;
        EntityHelper::setId($bet, $this->betId++);

        return $bet;
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
            ->setPlayer($this->player)
            ->setDateCreated(CarbonImmutable::now());
        EntityHelper::setId($subscription, 60);

        $bet1 = $this->getNewBet()->setSubscription($subscription);
        $subscription->addBet($bet1);
        $bet2 = $this->getNewBet()->setSubscription($subscription);
        $subscription->addBet($bet2);
        $bet3 = $this->getNewBet()->setSubscription($subscription);
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
            ->setDateCreated(CarbonImmutable::now());
        EntityHelper::setId($combination, 50);

        $bet1 = $this->getNewBet()->setCombination($combination);
        $combination->addBet($bet1);
        $bet2 = $this->getNewBet()->setCombination($combination);
        $combination->addBet($bet2);

        return $combination;
    }
}
