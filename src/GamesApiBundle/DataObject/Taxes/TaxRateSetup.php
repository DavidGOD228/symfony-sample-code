<?php

namespace GamesApiBundle\DataObject\Taxes;

/**
 * Class TaxRateSetup
 */
class TaxRateSetup
{
    /**
     * @var float
     */
    private $from;

    /**
     * @var float
     */
    private $to;

    /**
     * @var float
     */
    private $rate;

    /**
     * TaxRateSetup constructor.
     *
     * @param float $from
     * @param float $to
     * @param float $rate
     */
    public function __construct(float $from, float $to, float $rate)
    {
        $this->from = $from;
        $this->to = $to;
        $this->rate = $rate;
    }

    /**
     * @return float
     */
    public function getFrom(): float
    {
        return $this->from;
    }

    /**
     * @return float
     */
    public function getTo(): float
    {
        return $this->to;
    }

    /**
     * @return float
     */
    public function getRate(): float
    {
        return $this->rate;
    }
}