<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture;

use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\PartnerEnabledGame;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\Promotion;
use Acme\SymfonyDb\Entity\PromotionAmountByType;
use Acme\SymfonyDb\Entity\PromotionEnabledFor;
use Doctrine\Persistence\ObjectManager;
use SymfonyTests\Unit\CoreBundle\Fixture\AbstractCustomizableFixture;

/**
 * Class PromotionFixture
 *
 * Provided references:
 * promotion:1
 * promotion:2
 */
class PromotionFixture extends AbstractCustomizableFixture
{
    /**
     * @var array
     */
    protected array $tables = [
        Promotion::class,
        PromotionEnabledFor::class
    ];

    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    protected function doLoad(ObjectManager $manager): void
    {
        $date = (new \DateTime('2000-01-01 00:00'));
        /** @var PartnerEnabledGame $allowedGame */
        $allowedGame = $this->getReference('partnerAllowedGame:1:1');
        /** @var Currency $currency */
        $currency = $this->getReference('currency:rub');
        /** @var Partner $partner */
        $partner = $this->getReference('partner:1');

        $promotion1 = (new Promotion())
            ->setStartsAt($date)
            ->setCurrency($currency)
            ->setCurrentAmount(11)
            ->setExternalId(1)
            ->setStatus(Promotion::STATUS_ACTIVE)
            ->setType('mockedType')
            ->setUpdatedAt($date)
            ->setEligiblePlayerCount(1)
            ->setNote('test active promotion')
            ->setAccumulationPercent(1)
            ->setAmountByType([

            ])
        ;
        $manager->persist($promotion1);

        $amountByType = (new PromotionAmountByType())->setAmount(1.11)
            ->setType('mockedType')
            ->setPromotion($promotion1);


        $manager->persist($amountByType);

        $enabledFor = (new PromotionEnabledFor())
            ->setPromotion($promotion1)
            ->setPartner($partner)
            ->setGame($allowedGame->getGame());
        $manager->persist($enabledFor);

        $wonRun = $this->getReference('game:1:run');

        $promotion2 = (new Promotion())
            ->setExternalId(2)
            ->setStartsAt($date)
            ->setCurrency($currency)
            ->setCurrentAmount(22)
            ->setStatus(Promotion::STATUS_WON)
            ->setType('mockedType')
            ->setUpdatedAt($date)
            ->setWonAt($date)
            ->setWonAtRoundId(1)
            ->setWonAtRun($wonRun)
            ->setEligiblePlayerCount(1)
            ->setAccumulationPercent(1)
            ->setNote('test active promotion')
        ;
        $manager->persist($promotion2);
        $manager->flush();


        $enabledFor = (new PromotionEnabledFor())
            ->setPromotion($promotion2)
            ->setPartner($partner)
            ->setGame($allowedGame->getGame());
        $manager->persist($enabledFor);


        $manager->flush();
        $this->addReference('promotion:1', $promotion1);
        $this->addReference('promotion:2', $promotion2);
    }
}
