<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\RunCalculation;

use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\BetApiFailed;
use Acme\SymfonyDb\Entity\Combination;
use Acme\SymfonyDb\Entity\Subscription;
use Acme\SymfonyDb\Entity\BazaarBet;
use Acme\SymfonyDb\Entity\BazaarRun;
use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Type\BazaarBetType;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class BetReturnerFixture
 */
final class BetReturnerFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Bet $bet1single */
        $bet1single = $this->getReference('bet:3:valid');
        $bet1single->setInvalid(false);

        $failedBet = (new BetApiFailed())
            ->setServer('')
            ->setBet($bet1single)
            ->setErrorText('ERROR')
            ->setTime(CarbonImmutable::now())
        ;
        $bet1single->setFailed($failedBet);

        $manager->persist($bet1single);


        /** @var Bet $bet2subscription */
        $bet2subscription = $this->getReference('bet:3:1');
        $failedBet = (new BetApiFailed())
            ->setServer('')
            ->setBet($bet2subscription)
            ->setErrorText('ERROR')
            ->setTime(CarbonImmutable::now())
        ;
        $bet2subscription->setFailed($failedBet);

        $subscription = (new Subscription())
            ->setCurrency($bet2subscription->getCurrency())
            ->setPlayer($bet2subscription->getPlayer())
            ->setAmount($bet2subscription->getAmount())
            ->setBetsCalculated(0)
            ->setOddValue($bet2subscription->getOddValue())
            ->setDateCreated($bet2subscription->getTime())
            ->setOdd($bet2subscription->getOdd())
            ->setBetsTotal(1)
            ->setInvalid(false)
            ->setIsReturned(false)
            ->addBet($bet2subscription);

        $manager->persist($subscription);


        /** @var Bet $bet3combination */
        $bet3combination = $this->getReference('bet:3:2');

        $failedBet = (new BetApiFailed())
            ->setServer('')
            ->setBet($bet3combination)
            ->setErrorText('ERROR')
            ->setTime(CarbonImmutable::now())
        ;
        $bet3combination->setFailed($failedBet);

        $combination = (new Combination())
            ->setCurrency($bet3combination->getCurrency())
            ->setPlayer($bet3combination->getPlayer())
            ->setAmount($bet3combination->getAmount())
            ->setOddValue($bet3combination->getOddValue())
            ->setDateCreated($bet3combination->getTime())
            ->setInvalid(false)
            ->addBet($bet3combination);

        $manager->persist($combination);

        $manager->flush();

        $this->loadBazaarBet($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    private function loadBazaarBet(ObjectManager $manager): void
    {
        $this->loadBazaarRun($manager);

        /** @var BazaarRun $bazaarRun */
        $bazaarRun = $this->getReference('bazaar-run');

        /** @var Bet $bet */
        $bet = $this->getReference('bet:18:valid');
        $bet
            ->setInvalid(false)
            ->setAmount(15.6)
            ->setAmountWon(null)
            ->setGameRun($bazaarRun->getClosingRun())
        ;

        $bazaarBet = (new BazaarBet())
            ->setBazaarRun($bazaarRun)
            ->setBazaarBetType(BazaarBetType::MAIN)
            ->setBet($bet)
        ;

        $manager->persist($bazaarBet);
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    private function loadBazaarRun(ObjectManager $manager): void
    {
        /** @var Game $game */
        $game = $this->getReference('game:18');

        $openingRun = (new GameRun())
            ->setCode('182108270546')
            ->setGame($game)
            ->setIsReturned(false)
            ->setResultsEntered(true)
            ->setIsImported(true)
            ->setVideoUrl(null)
            ->setVideoConfirmationRequired(false)
            ->setTime(CarbonImmutable::now())
            ->setPublishedDate(CarbonImmutable::now())
        ;

        $closingRun = (new GameRun())
            ->setCode('182108270547')
            ->setGame($game)
            ->setIsReturned(false)
            ->setResultsEntered(true)
            ->setIsImported(true)
            ->setVideoUrl(null)
            ->setVideoConfirmationRequired(false)
            ->setTime(CarbonImmutable::now())
            ->setPublishedDate(CarbonImmutable::now())
        ;

        $bazaarRun = (new BazaarRun())
            ->setCode('182108270546:182108270547')
            ->setTitle('Madhur evening')
            ->setIsReturned(false)
        ;

        $bazaarRun->setOpeningRun($openingRun);
        $bazaarRun->setClosingRun($closingRun);

        $openingRun->setBazaarRun($bazaarRun);
        $closingRun->setBazaarRun($bazaarRun);

        $manager->persist($openingRun);
        $manager->persist($closingRun);
        $manager->persist($bazaarRun);

        $this->setReference('bazaar-run', $bazaarRun);
        $this->setReference('bazaar-opening-run', $openingRun);
        $this->setReference('bazaar-closing-run', $closingRun);
    }
}
