<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\HowToPlay;

use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\Language;
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
     */
    public function load(ObjectManager $manager): void
    {
        /** @var Currency $currency */
        $currency = $this->getReference('currency:eur');
        /** @var Language $language */
        $language = $this->getReference('language:en');

        $partner1 = CorePartnerFixture::getDefault()
            ->setEnabled(true)
            ->setCurrency($currency)
            ->setApiBetInformationLanguage($language)
            ->setCustomGameOrderEnabled(false)
            ->setName('Partner 1')
            ->setSubscriptionEnabled(true)
            ->setCombinationEnabled(true)
            ->setShowRtpInHtp(true)
            ->setGamificationEnabled(true)
        ;
        $manager->persist($partner1);

        $partner2 = CorePartnerFixture::getDefault()
            ->setEnabled(true)
            ->setCurrency($currency)
            ->setApiBetInformationLanguage($language)
            ->setCustomGameOrderEnabled(false)
            ->setName('Partner 2')
            ->setParent($partner1)
            ->setSubscriptionEnabled(false)
            ->setCombinationEnabled(false)
            ->setShowRtpInHtp(false)
        ;
        $manager->persist($partner2);

        $partner3 = CorePartnerFixture::getDefault()
            ->setEnabled(true)
            ->setCurrency($currency)
            ->setApiBetInformationLanguage($language)
            ->setCustomGameOrderEnabled(false)
            ->setName('Partner 3')
            ->setSubscriptionEnabled(false)
            ->setCombinationEnabled(false)
            ->setShowRtpInHtp(false)
        ;
        $manager->persist($partner3);

        $manager->flush();

        $this->addReference('partner:1', $partner1);
        $this->addReference('partner:2', $partner2);
        $this->addReference('partner:3', $partner3);
    }
}
