<?php

declare(strict_types=1);
// phpcs:disable Generic.Metrics.NestingLevel.TooHigh
namespace GamesApiBundle\DataObject\HowToPlay;

use Acme\SymfonyDb\Entity\PartnerEnabledGame;
use Acme\SymfonyDb\Type\PromotionType;
use CoreBundle\Service\GameService;

/**
 * Class RequiredBlockTypes
 */
class RequiredBlockTypes
{
    private bool $subscriptionRequired;
    private bool $combinationRequired;
    private bool $rtpRequired;
    private bool $jackpotRequired;
    private bool $cashbackRequired;
    private bool $gamificationRequired;

    /**
     * RequiredBlockTypes constructor.
     *
     * @param PartnerEnabledGame $enabledGame
     */
    public function __construct(PartnerEnabledGame $enabledGame)
    {
        $partner = $enabledGame->getPartner();
        $game = $enabledGame->getGame();

        $this->subscriptionRequired = $partner->getSubscriptionEnabled();
        $this->combinationRequired = $partner->getCombinationEnabled();
        $this->rtpRequired = $partner->getShowRtpInHtp();

        $hasJackpot = false;
        $hasCashback = false;

        $partnerPromotions = $partner->getPromotionsEnabledFor();

        foreach ($partnerPromotions as $promotionEnabledFor) {
            $promotionType = $promotionEnabledFor->getPromotion()->getType();

            switch ($promotionType) {
                case PromotionType::CASHBACK:
                    $hasCashback = true;
                    break;
                case PromotionType::JACKPOT_SPEEDY7:
                    if ($game->getId() === GameService::GAME_SPEEDY7) {
                        $hasJackpot = true;
                    }
                    break;
                case PromotionType::JACKPOT_HEADSUP:
                    if ($game->getId() === GameService::GAME_HEADSUP) {
                        $hasJackpot = true;
                    }
                    break;
            }
        }

        $this->jackpotRequired = $hasJackpot;
        $this->cashbackRequired = $hasCashback;
        $this->gamificationRequired = $partner->getGamificationEnabled();
    }

    /**
     * @return bool
     */
    public function isSubscriptionRequired(): bool
    {
        return $this->subscriptionRequired;
    }

    /**
     * @return bool
     */
    public function isCombinationRequired(): bool
    {
        return $this->combinationRequired;
    }

    /**
     * @return bool
     */
    public function isRtpRequired(): bool
    {
        return $this->rtpRequired;
    }

    /**
     * @return bool
     */
    public function isJackpotRequired(): bool
    {
        return $this->jackpotRequired;
    }

    /**
     * @return bool
     */
    public function isCashbackRequired(): bool
    {
        return $this->cashbackRequired;
    }

    /**
     * @return bool
     */
    public function isGamificationRequired(): bool
    {
        return $this->gamificationRequired;
    }
}
