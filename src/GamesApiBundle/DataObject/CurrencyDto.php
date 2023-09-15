<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject;

use Acme\SymfonyDb\Entity\Currency;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class CurrencyDto
 *
 * @psalm-immutable
 */
final class CurrencyDto
{
    /** @SerializedName("code") */
    public string $code;
    /** @SerializedName("approximateRate") */
    public string $approximateRate;
    /** @SerializedName("precision") */
    public int $precision;
    /** @SerializedName("template") */
    public string $template;

    /**
     * PartnerInitialInfoCurrency constructor.
     * @param Currency $currency
     */
    public function __construct(Currency $currency)
    {
        $this->code = $currency->getCode();
        // Converting to string to get normal precision, not json serialize_precision like 1.0000000001.
        $this->approximateRate = (string) $currency->getApproximateRate();
        $this->precision = $currency->getPrecision();
        $this->template = $currency->getTemplate();
    }
}
