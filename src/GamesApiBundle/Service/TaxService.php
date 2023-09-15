<?php

namespace GamesApiBundle\Service;

use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\TaxScheme;
use Acme\SymfonyDb\Interfaces\PlayerBetInterface;
use Acme\SymfonyDb\Type\AmountTaxedCalcType;
use CoreBundle\Service\Utility\MoneyService;
use GamesApiBundle\DataObject\Taxes\Taxed;
use GamesApiBundle\DataObject\Taxes\AbstractTax;
use GamesApiBundle\DataObject\Taxes\None;
use GamesApiBundle\DataObject\Taxes\TaxRateSetup;

/**
 * Class TaxesService
 */
class TaxService
{
    private const TAX_RATE_UNLIMITED = -1;

    public const PAYIN = 'payin';
    public const PAYOUT = 'payout';

    /**
     * @param TaxScheme|null $taxScheme
     *
     * @return AbstractTax
     */
    public function getTaxDto(?TaxScheme $taxScheme): AbstractTax
    {
        if (!$taxScheme) {
            return new None([], new TaxScheme());
        }

        $rates = $taxScheme->getRates();

        return new Taxed($rates, $taxScheme);
    }
    /**
     * Quite complex logic, please be aware that any type of pay in tax
     * with any type of pay out tax could affect possible winning
     *
     * @param PlayerBetInterface $bet
     * @param TaxScheme $taxScheme
     * @param float $oddValue
     *
     * @return float
     */
    public function getPossibleWinningAfterTaxes(
        PlayerBetInterface $bet,
        TaxScheme $taxScheme,
        float $oddValue
    ): float
    {
        $currency = $bet->getCurrency();

        $payInAmount = $bet->getAmount();

        $tax = $this->getTaxDto($taxScheme);

        $taxPayInAmount = $this->getTaxAmount($payInAmount, $tax->getPayin()->getRates(), $currency);
        $taxPayInAmountAfterTaxes = MoneyService::round($payInAmount - $taxPayInAmount, $currency);
        $possibleWinning = MoneyService::round($taxPayInAmountAfterTaxes * $oddValue, $currency);

        if ($taxScheme->getCalculationAmountTaxedType() === AmountTaxedCalcType::PROFIT) {
            $applicableAmount = $possibleWinning - $taxPayInAmountAfterTaxes;
        } else {
            $applicableAmount = $possibleWinning;
        }

        $taxPayOutAmountAfterTaxes = $this->getTaxAmount(
            $applicableAmount,
            $tax->getPayout()->getRates(),
            $currency
        );

        return MoneyService::round($possibleWinning - $taxPayOutAmountAfterTaxes, $currency);
    }

    /**
     * @param float $applicableAmount
     * @param TaxRateSetup[] $rates
     * @param Currency $currency
     *
     * @return float
     */
    private function getTaxAmount(float $applicableAmount, array $rates, Currency $currency): float
    {
        $taxAmount = 0;
        foreach ($rates as $rate) {
            $rateTo = $rate->getTo();
            // Loose comparison to avoid issues with float comparison.
            if ($rateTo == self::TAX_RATE_UNLIMITED || $applicableAmount <= $rateTo) {
                $rateTo = $applicableAmount;
                $taxAmount += MoneyService::applyPercent(
                    $rateTo - $rate->getFrom(),
                    $rate->getRate(),
                    $currency
                );

                return $taxAmount;
            }

            $taxAmount += MoneyService::applyPercent(
                $rateTo - $rate->getFrom(),
                $rate->getRate(),
                $currency
            );
        }

        return $taxAmount;
    }
}
