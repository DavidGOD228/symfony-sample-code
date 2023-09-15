<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\GameOptions;

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

        $partnerWithNoGames = CorePartnerFixture::getDefault()
            ->setEnabled(true)
            ->setApiCode('no-games-token')
            ->setCurrency($currency)
            ->setApiBetInformationLanguage($language)
            ->setName('Partner No Games');
        $manager->persist($partnerWithNoGames);

        $manager->flush();

        $this->addReference('partner:no-games', $partnerWithNoGames);
    }
}
