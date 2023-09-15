<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\HowToPlay;

use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\Promotion;
use Acme\SymfonyDb\Entity\PromotionEnabledFor;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class PromotionEnabledForFixture
 */
final class PromotionEnabledForFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        /** @var Game $game */
        $game = $this->getReference('game:1');
        /** @var Game $game11 */
        $game11 = $this->getReference('game:11');
        /** @var Game $game12 */
        $game12 = $this->getReference('game:12');

        /** @var Partner $partner */
        $partner = $this->getReference('partner:1');

        /** @var Currency $currency */
        $currency = $this->getReference('currency:eur');

        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(false)
            ->setResultsEntered(true)
            ->setVideoConfirmationRequired(false)
            ->setIsImported(true)
            ->setTime(new DateTimeImmutable('2020-01-02 03:04:05'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setGame($game);
        $manager->persist($gameRun);

        $gameRunSpeedy7 = (new GameRun())
            ->setCode('101')
            ->setIsReturned(false)
            ->setResultsEntered(true)
            ->setVideoConfirmationRequired(false)
            ->setIsImported(true)
            ->setTime(new DateTimeImmutable('2020-01-02 03:04:05'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setGame($game11);
        $manager->persist($gameRunSpeedy7);

        $gameRunHeadsup = (new GameRun())
            ->setCode('102')
            ->setIsReturned(false)
            ->setResultsEntered(true)
            ->setVideoConfirmationRequired(false)
            ->setIsImported(true)
            ->setTime(new DateTimeImmutable('2020-01-02 03:04:05'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setGame($game12);
        $manager->persist($gameRunHeadsup);

        $jackpotPromotionForSpeedy7 = (new Promotion())
            ->setStartsAt(new DateTimeImmutable())
            ->setCurrency($currency)
            ->setNote('Jackpot promotion Speedy7')
            ->setUpdatedAt(new DateTimeImmutable())
            ->setWonAtRun($gameRunSpeedy7)
            ->setWonAt(new DateTimeImmutable())
            ->setStatus(Promotion::STATUS_WON)
            ->setType('Speedy7 JackPot')
            ->setEligiblePlayerCount(1)
            ->setCurrentAmount(11)
            ->setExternalId(1)
            ->setAccumulationPercent(1);
        $manager->persist($jackpotPromotionForSpeedy7);

        $jackpotPromotionForHeadsup = (new Promotion())
            ->setStartsAt(new DateTimeImmutable())
            ->setCurrency($currency)
            ->setNote('Jackpot promotion Poker Headsup')
            ->setUpdatedAt(new DateTimeImmutable())
            ->setWonAtRun($gameRunHeadsup)
            ->setWonAt(new DateTimeImmutable())
            ->setStatus(Promotion::STATUS_WON)
            ->setType('Poker Headsup JackPot')
            ->setEligiblePlayerCount(1)
            ->setCurrentAmount(11)
            ->setExternalId(1)
            ->setAccumulationPercent(1);
        $manager->persist($jackpotPromotionForHeadsup);

        $cashbackPromotion = (new Promotion())
            ->setStartsAt(new DateTimeImmutable())
            ->setCurrency($currency)
            ->setNote('Cashback promotion')
            ->setUpdatedAt(new DateTimeImmutable())
            ->setWonAtRun($gameRun)
            ->setWonAt(new DateTimeImmutable())
            ->setStatus(Promotion::STATUS_WON)
            ->setType('Cashback')
            ->setEligiblePlayerCount(1)
            ->setCurrentAmount(11)
            ->setExternalId(1)
            ->setAccumulationPercent(1);
        $manager->persist($cashbackPromotion);

        $jackpotPromotionEnabledForSpeedy7 = (new PromotionEnabledFor())
            ->setGame($game11)
            ->setPartner($partner)
            ->setPromotion($jackpotPromotionForSpeedy7);
        $manager->persist($jackpotPromotionEnabledForSpeedy7);

        $jackpotPromotionEnabledForHeadsup = (new PromotionEnabledFor())
            ->setGame($game12)
            ->setPartner($partner)
            ->setPromotion($jackpotPromotionForHeadsup);
        $manager->persist($jackpotPromotionEnabledForHeadsup);

        $cashbackPromotionEnabledFor = (new PromotionEnabledFor())
            ->setGame($game)
            ->setPartner($partner)
            ->setPromotion($cashbackPromotion);
        $manager->persist($cashbackPromotionEnabledFor);

        $manager->flush();
    }
}
