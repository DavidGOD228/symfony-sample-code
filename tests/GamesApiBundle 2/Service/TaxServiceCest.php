<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Service;

use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\TaxScheme;
use GamesApiBundle\DataObject\Taxes\Taxed;
use GamesApiBundle\DataObject\Taxes\None;
use GamesApiBundle\Service\TaxService;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class TaxesServiceCest
 */
final class TaxServiceCest extends AbstractUnitTest
{
    /**
     * @var TaxService
     */
    private $service;

    /** @inheritDoc */
    protected function setUpFixtures(): void
    {
        $this->fixtureBoostrapper->addPartners(1, true);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);
        $this->service = new TaxService();
    }

    /**
     * @param UnitTester $I
     */
    public function testGetTaxDtoShouldProperPlaceTaxes(UnitTester $I): void
    {
        /** @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');

        $partner->setTaxScheme(null);

        $taxDto = $this->service->getTaxDto($partner->getTaxScheme());
        $I->assertEquals(new None([], new TaxScheme()), $taxDto);

        /** @var TaxScheme $taxScheme1 */
        $taxScheme1 = $this->getEntityByReference('tax-scheme:1');
        $partner->setTaxScheme($taxScheme1);
        $taxDto = $this->service->getTaxDto($partner->getTaxScheme());
        $I->assertEquals(
            new Taxed($taxScheme1->getRates(), $taxScheme1),
            $taxDto
        );

        /** @var TaxScheme $taxScheme2 */
        $taxScheme2 = $this->getEntityByReference('tax-scheme:2');
        $partner->setTaxScheme($taxScheme2);
        $taxDto = $this->service->getTaxDto($partner->getTaxScheme());
        $I->assertEquals(
            new Taxed($taxScheme2->getRates(), $taxScheme2),
            $taxDto
        );

        /** @var TaxScheme $taxScheme3 */
        $taxScheme3 = $this->getEntityByReference('tax-scheme:3');
        $partner->setTaxScheme($taxScheme3);
        $taxDto = $this->service->getTaxDto($partner->getTaxScheme());
        $I->assertEquals(
            new Taxed($taxScheme3->getRates(), $taxScheme3),
            $taxDto
        );

        /** @var TaxScheme $taxScheme4 */
        $taxScheme4 = $this->getEntityByReference('tax-scheme:4');
        $partner->setTaxScheme($taxScheme4);
        $taxDto = $this->service->getTaxDto($partner->getTaxScheme());
        $I->assertEquals(
            new Taxed($taxScheme4->getRates(), $taxScheme4),
            $taxDto
        );
    }
}
