<?php

namespace GamesApiBundle\DataObject\Taxes;

use Acme\SymfonyDb\Entity\TaxScheme;
use CoreBundle\Service\Utility\MoneyService;
use GamesApiBundle\Service\TaxService;

/**
 * Class AbstractTax
 */
abstract class AbstractTax
{
    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var TaxSchemeSetup
     */
    protected $payin;

    /**
     * @var TaxSchemeSetup
     */
    protected $payout;

    /**
     * @var string|null
     */
    protected $calculationPayInType;

    /**
     * @var string|null
     */
    protected $calculationPayOutType;

    /**
     * @var string|null
     */
    protected $calculationAmountTaxedType;

    /**
     * @var bool
     */
    protected $payinApplied;

    /**
     * @var bool
     */
    protected $payoutApplied;

    /**
     * AbstractTax constructor.
     *
     * @param iterable $rates
     * @param TaxScheme $taxScheme
     */
    public function __construct(iterable $rates, TaxScheme $taxScheme)
    {
        $payinRates = [];
        $payoutRates = [];

        foreach ($rates as $rate) {
            if ($rate->getType() == TaxService::PAYIN) {
                $payinRates[] = new TaxRateSetup(
                    $rate->getFrom(),
                    $rate->getTo(),
                    $rate->getPercent() / MoneyService::PERCENT_ALL
                );
            } elseif ($rate->getType() == TaxService::PAYOUT) {
                $payoutRates[] = new TaxRateSetup(
                    $rate->getFrom(),
                    $rate->getTo(),
                    $rate->getPercent() / MoneyService::PERCENT_ALL
                );
            }
        }

        $this->payin = new TaxSchemeSetup(false, $payinRates);
        $this->payout = new TaxSchemeSetup(true, $payoutRates);

        $this->calculationPayInType = $taxScheme->getCalculationPayInType();
        $this->calculationPayOutType = $taxScheme->getCalculationPayOutType();

        $this->calculationAmountTaxedType = $taxScheme->getCalculationAmountTaxedType();
        $this->payinApplied = $taxScheme->getPayinApplied();
        $this->payoutApplied = $taxScheme->getPayoutApplied();
    }

    /**
     * @return bool
     */
    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return TaxSchemeSetup
     */
    public function getPayin(): TaxSchemeSetup
    {
        return $this->payin;
    }

    /**
     * @return TaxSchemeSetup
     */
    public function getPayout(): TaxSchemeSetup
    {
        return $this->payout;
    }

    /**
     * @return string|null
     */
    public function getCalculationPayInType(): ?string
    {
        return $this->calculationPayInType;
    }

    /**
     * @return string|null
     */
    public function getCalculationPayOutType(): ?string
    {
        return $this->calculationPayOutType;
    }

    /**
     * @return string|null
     */
    public function getCalculationAmountTaxedType(): ?string
    {
        return $this->calculationAmountTaxedType;
    }

    /**
     * @return bool|null
     */
    public function getPayinApplied(): ?bool
    {
        return $this->payinApplied;
    }

    /**
     * @return bool|null
     */
    public function getPayoutApplied(): ?bool
    {
        return $this->payoutApplied;
    }
}
