<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\BetAmounts;

use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\Language;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Acme\SymfonyDb\Entity\Partner;
use SymfonyTests\Unit\CoreBundle\Fixture\PartnerFixture as CorePartnerFixture;

/**
 * Class PartnerFixture
 */
final class PartnerFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $fixtures = [
            'partner:1' => [
                'number' => 1,
                'showBetsSum' => true
            ],
            'partner:2' => [
                'number' => 2,
                'showBetsSum' => true
            ],
            'partner:show-bets-sum-disabled' => [
                'number' => 3,
                'showBetsSum' => false
            ],
        ];

        foreach ($fixtures as $ref => $params) {
            $partner = $this->getEntity(
                $params['number'],
                $params['showBetsSum']
            );

            $this->addReference($ref, $partner);
            $manager->persist($partner);
        }

        $manager->flush();
    }

    /**
     * @param int $number
     * @param bool $showBetsSum
     *
     * @return Partner
     */
    private function getEntity(int $number, bool $showBetsSum): Partner
    {
        /** @var Currency $currency */
        $currency = $this->getReference('currency:eur');
        /** @var Language $language */
        $language = $this->getReference('language:en');

        $entity = CorePartnerFixture::getDefault()
            ->setName('Partner' . $number)
            ->setApiCode('test' . $number)
            ->setApiBetInformationLanguage($language)
            ->setCurrency($currency)
            ->setShowBetsSum($showBetsSum)
            ;

        return $entity;
    }
}
