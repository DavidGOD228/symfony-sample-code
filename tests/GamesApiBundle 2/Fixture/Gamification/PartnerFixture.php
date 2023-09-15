<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use SymfonyTests\Unit\CoreBundle\Fixture\PartnerFixture as CorePartnerFixture;

/**
 * Class PartnerFixture
 */
class PartnerFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $currency = $this->getReference('currency:eur');
        $language = $this->getReference('language:en');

        $partner1 = CorePartnerFixture::getDefault()
            ->setName("DefaultPartner1")
            ->setApiCode('testApiCode1')
            ->setApiBetInformationLanguage($language)
            ->setCurrency($currency)
            ->setNotificationEnabled(true)
            ->setGamificationEnabled(false)
        ;
        $manager->persist($partner1);

        $partner2 = CorePartnerFixture::getDefault()
            ->setName("DefaultPartner2")
            ->setApiCode('testApiCode2')
            ->setApiBetInformationLanguage($language)
            ->setCurrency($currency)
            ->setNotificationEnabled(false)
            ->setGamificationEnabled(true)
        ;
        $manager->persist($partner2);

        $manager->flush();

        $this->addReference('partner1:gamification', $partner1);
        $this->addReference('partner2:gamification', $partner2);
    }
}