<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Initial\Component;

use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\Partner;
use Doctrine\ORM\NonUniqueResultException;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoPromotion;
use GamesApiBundle\DataObject\InitialInfo\PartnerInitialInfoPromotionData;
use PromotionBundle\Service\PromotionService;

/**
 * Class InitialPromotionBuilder
 */
final class InitialPromotionBuilder
{
    private PromotionService $promotionService;

    /**
     * @param PromotionService $promotionService
     */
    public function __construct(
        PromotionService $promotionService
    )
    {
        $this->promotionService = $promotionService;
    }

    /**
     * @param Partner $partner
     * @param Currency $currencyTo
     *
     * @return PartnerInitialInfoPromotionData
     * @throws NonUniqueResultException
     */
    public function build(Partner $partner, Currency $currencyTo): PartnerInitialInfoPromotionData
    {
        $rawPromotions = $this->promotionService->getNonPendingActivePromotionsForPartner($partner);
        $promotions = [];
        $promotionRates = [];

        foreach ($rawPromotions as $promotion) {
            $promotions[] = new PartnerInitialInfoPromotion($promotion);
            // From promotion -> to players currency.
            $currencyFrom = $promotion->getCurrency();
            if (!isset($promotionRates[$currencyFrom->getCode()])) {
                // Converting to string to get normal precision, not json serialize_precision like 1.0000000001.
                // Not rounding, because this used as multiplier for some amount and decimals could be important.
                $rate = (string) ($currencyTo->getApproximateRate() / $currencyFrom->getApproximateRate());
                $promotionRates[$currencyFrom->getCode()] = $rate;
            }
        }

        return new PartnerInitialInfoPromotionData($promotions, $promotionRates);
    }
}
