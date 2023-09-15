<?php

namespace GamesApiBundle\DataObject\Taxes;

/**
 * Class TaxSchemeSetup
 */
class TaxSchemeSetup
{
    /**
     * @var bool
     */
    private $isOnOurSide;

    /**
     * @var TaxRateSetup[]
     */
    private $rates;

    /**
     * TaxSchemeSetup constructor.
     *
     * @param bool $isOnOurSide
     * @param array $rates
     */
    public function __construct(bool $isOnOurSide, array $rates)
    {
        $this->isOnOurSide = $isOnOurSide;
        $this->rates = $rates;
    }

    /**
     * @return bool
     */
    public function isOnOurSide(): bool
    {
        return $this->isOnOurSide;
    }

    /**
     * @return TaxRateSetup[]
     */
    public function getRates(): array
    {
        return $this->rates;
    }
}