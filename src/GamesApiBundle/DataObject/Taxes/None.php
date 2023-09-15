<?php

namespace GamesApiBundle\DataObject\Taxes;

use Acme\SymfonyDb\Entity\TaxScheme;

/**
 * Class None
 *
 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassAfterLastUsed
 */
class None extends AbstractTax
{
    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * None constructor.
     *
     * @param array $rates
     * @param TaxScheme $taxScheme
     */
    public function __construct(iterable $rates, TaxScheme $taxScheme)
    {
        $this->payin = new TaxSchemeSetup(false, $rates);
        $this->payout = new TaxSchemeSetup(false, $rates);
    }
}
