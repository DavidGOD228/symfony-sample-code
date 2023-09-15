<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\InitialInfo;

use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoPromotion;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class PartnerInitialInfoPromotionData
 *
 * @psalm-immutable
 * @deprecated frontend is migrating to initial API v3 - avoid modifying any initial API v2 code if possible
 */
final class PartnerInitialInfoPromotionData
{
    /**
     * @var PartnerInitialInfoPromotion[]
     * @SerializedName("active")
     */
    public array $active;

    /**
     * @var string[]|null
     * @SerializedName("currencyRates")
     */
    public ?array $currencyRates;

    /**
     * @param PartnerInitialInfoPromotion[] $active
     * @param string[]|null $currencyRates
     */
    public function __construct(array $active, array $currencyRates)
    {
        $this->active = $active;
        $this->currencyRates = $currencyRates ?: null;
    }
}