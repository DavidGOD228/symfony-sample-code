<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\InitialInfo\Component;

use Acme\SymfonyDb\Entity\Partner;
use GamesApiBundle\DataObject\Taxes\AbstractTax;
use GamesApiBundle\DataObject\Taxes\TaxSchemeSetup;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class PartnerInitialInfoTaxes
 *
 * @psalm-immutable
 */
final class PartnerInitialInfoTaxes
{
    /** @SerializedName("schemeId") */
    public ?int $schemeId;
    /** @SerializedName("enabled") */
    public bool $enabled;
    /** @SerializedName("calculationPayInType") */
    public ?string $calculationPayInType;
    /** @SerializedName("calculationPayOutType") */
    public ?string $calculationPayOutType;
    /** @SerializedName("calculationAmountTaxedType") */
    public ?string $calculationAmountTaxedType;
    /** @SerializedName("payinApplied") */
    public ?bool $payinApplied;
    /** @SerializedName("payoutApplied") */
    public ?bool $payoutApplied;

    /**
     * @var array[]
     * @SerializedName("payin")
     */
    public array $payin;
    /**
     * @var array[]
     * @SerializedName("payout")
     */
    public array $payout;

    /**
     * @param AbstractTax $tax
     * @param Partner $partner
     */
    public function __construct(AbstractTax $tax, Partner $partner)
    {
        $this->enabled = $tax->getEnabled();

        $this->payin = $this->convertSchemeToArray($tax->getPayin());
        $this->payout = $this->convertSchemeToArray($tax->getPayout());

        $this->calculationPayInType = $tax->getCalculationPayInType();
        $this->calculationPayOutType = $tax->getCalculationPayOutType();

        $this->calculationAmountTaxedType = $tax->getCalculationAmountTaxedType();
        $this->payinApplied = $tax->getPayinApplied();
        $this->payoutApplied = $tax->getPayoutApplied();
        $this->schemeId = $partner->getTaxScheme() ? $partner->getTaxScheme()->getId() : null;
    }

    /**
     * @param TaxSchemeSetup $scheme
     *
     * @return array
     */
    private function convertSchemeToArray(TaxSchemeSetup $scheme): array
    {
        $rates = [
            'isOnOurSide' => $scheme->isOnOurSide(),
            'rates' => [],
        ];

        foreach ($scheme->getRates() as $rate) {
            $rates['rates'][] = [
                'from' => (string) $rate->getFrom(),
                'to' => (string) $rate->getTo(),
                'rate' => (string) $rate->getRate(),
            ];
        }

        return $rates;
    }
}
