<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture;

use Acme\SymfonyDb\Entity\TaxScheme;
use Doctrine\Persistence\ObjectManager;
use SymfonyTests\Unit\CoreBundle\Fixture\AbstractCustomizableFixture;

/**
 * Class TaxSchemeFixture
 */
final class TaxSchemeFixture extends AbstractCustomizableFixture
{
    /**
     * @var array
     */
    protected array $tables = [
        TaxScheme::class
    ];

    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    protected function doLoad(ObjectManager $manager): void
    {
        $taxScheme1 = (new TaxScheme())
            ->setName('Croatia')
            ->setCalculationPayOutType('staircase')
            ->setCalculationPayInType('regular')
            ->setCalculationAmountTaxedType('profit')
            ->setPayinApplied(true)
            ->setPayoutApplied(true);
        $manager->persist($taxScheme1);

        $taxScheme2 = (new TaxScheme())
            ->setName('Kenya')
            ->setCalculationPayOutType('staircase')
            ->setCalculationPayInType(null)
            ->setCalculationAmountTaxedType('profit')
            ->setPayinApplied(false)
            ->setPayoutApplied(true);
        $manager->persist($taxScheme2);

        $taxScheme3 = (new TaxScheme())
            ->setName('Uganda')
            ->setCalculationPayOutType('staircase')
            ->setCalculationPayInType('regular')
            ->setCalculationAmountTaxedType('full')
            ->setPayinApplied(false)
            ->setPayoutApplied(true);
        $manager->persist($taxScheme3);

        $taxScheme4 = (new TaxScheme())
            ->setName('Poland (STS)')
            ->setCalculationPayOutType('staircase')
            ->setCalculationPayInType('regular')
            ->setCalculationAmountTaxedType('full')
            ->setPayinApplied(true)
            ->setPayoutApplied(false);
        $manager->persist($taxScheme4);

        $this->setReference('tax-scheme:1', $taxScheme1);
        $this->setReference('tax-scheme:2', $taxScheme2);
        $this->setReference('tax-scheme:3', $taxScheme3);
        $this->setReference('tax-scheme:4', $taxScheme4);

        $this->flushEntities($manager);
    }
}
