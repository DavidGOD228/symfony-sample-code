<?php
namespace SymfonyTests\Unit\GamesApiBundle\DataObject\Taxes;

use Acme\SymfonyDb\Entity\TaxScheme;
use GamesApiBundle\DataObject\Taxes\None;
use GamesApiBundle\DataObject\Taxes\TaxSchemeSetup;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class NoneCest
 */
class NoneCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     */
    public function testNoneTaxes(UnitTester $I): void
    {
        $noneTaxes = new None([], new TaxScheme());
        $taxSchemeSetup = new TaxSchemeSetup(false, []);

        $I->assertEquals(false, $noneTaxes->getEnabled());
        $I->assertEquals($taxSchemeSetup, $noneTaxes->getPayin());
        $I->assertEquals($taxSchemeSetup, $noneTaxes->getPayout());
    }
}
