<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture;

use Acme\SymfonyDb\Entity\TaxScheme;
use Acme\SymfonyDb\Entity\TaxSchemeRate;
use Doctrine\Persistence\ObjectManager;
use SymfonyTests\Unit\CoreBundle\Fixture\AbstractCustomizableFixture;

/**
 * Class TaxSchemeRateFixture
 */
class TaxSchemeRateFixture extends AbstractCustomizableFixture
{
    /**
     * @var array
     */
    protected array $tables = [
        TaxScheme::class,
        TaxSchemeRate::class
    ];

    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    protected function doLoad(ObjectManager $manager): void
    {
        $taxScheme1 = $this->getReference('tax-scheme:1');
        $taxScheme2 = $this->getReference('tax-scheme:2');
        $taxScheme3 = $this->getReference('tax-scheme:3');
        $taxScheme4 = $this->getReference('tax-scheme:4');

        $taxSchemeRate11 = (new TaxSchemeRate())
            ->setTaxScheme($taxScheme1)
            ->setType('payin')
            ->setFrom(0)
            ->setTo(-1)
            ->setPercent(5);

        $manager->persist($taxSchemeRate11);

        $taxSchemeRate12 = (new TaxSchemeRate())
            ->setTaxScheme($taxScheme1)
            ->setType('payout')
            ->setFrom(0)
            ->setTo(10000)
            ->setPercent(10);

        $manager->persist($taxSchemeRate12);

        $taxSchemeRate13 = (new TaxSchemeRate())
            ->setTaxScheme($taxScheme1)
            ->setType('payout')
            ->setFrom(10000)
            ->setTo(30000)
            ->setPercent(15);

        $manager->persist($taxSchemeRate13);

        $taxSchemeRate14 = (new TaxSchemeRate())
            ->setTaxScheme($taxScheme1)
            ->setType('payout')
            ->setFrom(30000)
            ->setTo(500000)
            ->setPercent(20);

        $manager->persist($taxSchemeRate14);

        $taxSchemeRate15 = (new TaxSchemeRate())
            ->setTaxScheme($taxScheme1)
            ->setType('payout')
            ->setFrom(500000)
            ->setTo(-1)
            ->setPercent(30);

        $manager->persist($taxSchemeRate15);

        $taxSchemeRate21 = (new TaxSchemeRate())
            ->setTaxScheme($taxScheme2)
            ->setType('payout')
            ->setFrom(0)
            ->setTo(-1)
            ->setPercent(20);

        $manager->persist($taxSchemeRate21);

        $taxSchemeRate31 = (new TaxSchemeRate())
            ->setTaxScheme($taxScheme3)
            ->setType('payout')
            ->setFrom(0)
            ->setTo(-1)
            ->setPercent(15);

        $manager->persist($taxSchemeRate31);

        $taxSchemeRate41 = (new TaxSchemeRate())
            ->setTaxScheme($taxScheme4)
            ->setType('payin')
            ->setFrom(0)
            ->setTo(-1)
            ->setPercent(12);

        $manager->persist($taxSchemeRate41);

        $this->setReference('tax-scheme:1:1', $taxSchemeRate11);
        $this->setReference('tax-scheme:1:2', $taxSchemeRate12);
        $this->setReference('tax-scheme:1:3', $taxSchemeRate13);
        $this->setReference('tax-scheme:1:4', $taxSchemeRate14);
        $this->setReference('tax-scheme:1:5', $taxSchemeRate15);
        $this->setReference('tax-scheme:2:1', $taxSchemeRate21);
        $this->setReference('tax-scheme:3:1', $taxSchemeRate31);
        $this->setReference('tax-scheme:4:1', $taxSchemeRate41);

        $this->flushEntities($manager);
    }
}
