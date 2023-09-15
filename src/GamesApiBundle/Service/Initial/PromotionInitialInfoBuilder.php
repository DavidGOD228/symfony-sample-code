<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Initial;

use CoreBundle\Exception\ValidationException;
use CoreBundle\Service\PartnerService;
use Doctrine\ORM\NonUniqueResultException;
use GamesApiBundle\DataObject\InitialInfo\PartnerInitialInfoPromotionData;
use GamesApiBundle\Service\Initial\Component\CurrencyButtonAmountService;
use GamesApiBundle\Service\Initial\Component\InitialPromotionBuilder;
use GamesApiBundle\Service\PlayerService;

/**
 * Class PromotionInitialInfoBuilder
 */
final class PromotionInitialInfoBuilder
{
    protected CurrencyButtonAmountService $currencyButtonAmountService;
    private PartnerService $partnerService;
    private PlayerService $playerService;

    private InitialPromotionBuilder $initialPromotionBuilder;

    /**
     * @param CurrencyButtonAmountService $currencyButtonAmountService
     * @param PartnerService $partnerService
     * @param PlayerService $playerService
     * @param InitialPromotionBuilder $initialPromotionBuilder
     */
    public function __construct(
        CurrencyButtonAmountService $currencyButtonAmountService,
        PartnerService $partnerService,
        PlayerService $playerService,
        InitialPromotionBuilder $initialPromotionBuilder
    )
    {
        $this->currencyButtonAmountService = $currencyButtonAmountService;
        $this->partnerService = $partnerService;
        $this->playerService = $playerService;
        $this->initialPromotionBuilder = $initialPromotionBuilder;
    }

    /**
     * @param string $partnerCode
     *
     * @return PartnerInitialInfoPromotionData
     * @throws NonUniqueResultException
     * @throws ValidationException
     */
    public function getPromotionInfo(
        string $partnerCode
    ): PartnerInitialInfoPromotionData
    {
        $partner = $this->partnerService->getPartnerByPartnerApiCodeStrict($partnerCode);

        $player = $this->playerService->getOptionalPlayerFromSession();
        if ($player) {
            $currency = $player->getCurrency();
        } else {
            $currency = $partner->getCurrency();
        }

        return $this->initialPromotionBuilder->build($partner, $currency);
    }
}