<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\InitialInfo;

use Acme\SymfonyDb\Entity\CurrencyButtonAmount;
use Acme\SymfonyDb\Entity\Partner;
use Acme\Time\Time;
use GamesApiBundle\DataObject\CurrencyDto;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoGame;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoPlayer;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoTaxes;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class PartnerInitialInfoResponse
 *
 * @psalm-immutable
 * @deprecated frontend is migrating to initial API v3 - avoid modifying any initial API v2 code if possible
 */
final class PartnerInitialInfoResponse
{
    /**
     * @var string[]
     * @SerializedName("betAmounts")
     */
    public array $betAmounts;

    /**
     * @var int[]
     * @SerializedName("possibleRunsCount")
     */
    public array $possibleRunsCount;

    /**
     * @var PartnerInitialInfoGame[]
     * @SerializedName("games")
     */
    public array $games;

    /**
     * @var array{url: string, token: string}
     * @SerializedName("sockets")
     */
    public array $sockets;

    /** @SerializedName("isNotificationEnabled") */
    public bool $isNotificationEnabled;
    /** @SerializedName("isCombinationEnabled") */
    public bool $isCombinationEnabled;
    /** @SerializedName("isItalianSpeech") */
    public bool $isItalianSpeech;
    /** @SerializedName("isNumpadEnabled") */
    public bool $isNumpadEnabled;
    /** @SerializedName("isSubscriptionsEnabled") */
    public bool $isSubscriptionsEnabled;
    /** @SerializedName("isFullscreenEnabled") */
    public bool $isFullscreenEnabled;
    /** @SerializedName("isTerminalView") */
    public bool $isTerminalView;
    /** @SerializedName("disableVideoOnInactivity") */
    public bool $disableVideoOnInactivity;
    /** @SerializedName("allowExperimentalUI") */
    public bool $allowExperimentalUI;
    /** @SerializedName("isRtl") */
    public bool $isRtl;
    /** @SerializedName("isGeoBlocked") */
    public bool $isGeoBlocked;
    /** @SerializedName("rememberLastStake") */
    public bool $rememberLastStake;
    /** @SerializedName("reinitSessionOnGameNavigation") */
    public bool $reinitSessionOnGameNavigation;
    /** @SerializedName("showMessagesOnChangedOdds") */
    public bool $showMessagesOnChangedOdds;
    /** @SerializedName("showClock") */
    public bool $showClock;
    /** @SerializedName("showGuiTip") */
    public bool $showGuiTip;
    /** @SerializedName("showPlayersBetsSum") */
    public bool $showPlayersBetsSum;
    /** @SerializedName("isSurveillanceEnabled") */
    public bool $isSurveillanceEnabled;

    /** @SerializedName("videoLogoUrl") */
    public string $videoLogoUrl;
    /** @SerializedName("cssCustom") */
    public ?string $cssCustom;

    /** @SerializedName("gaCode") */
    public string $gaCode;
    /** @SerializedName("refreshTokenFrequency") */
    public int $refreshTokenFrequency;

    /** @SerializedName("player") */
    public ?PartnerInitialInfoPlayer $player;

    /** @SerializedName("currency") */
    public CurrencyDto $currency;
    /** @SerializedName("topWonAmountsCurrency") */
    public CurrencyDto $topWonAmountsCurrency;

    /** @SerializedName("promotions") */
    public PartnerInitialInfoPromotionData $promotions;
    /** @SerializedName("taxes") */
    public ?PartnerInitialInfoTaxes $taxes;

    /**
     * PartnerInitialInfoV2 constructor.
     *
     * @param CurrencyButtonAmount[] $buttonAmounts
     * @param Partner $partner
     * @param bool $isRtl
     * @param array $possibleRunsCount
     * @param bool $reinitSessionOnGameNavigation
     * @param string $videoLogoUrl
     * @param PartnerInitialInfoPlayer|null $player
     * @param \GamesApiBundle\DataObject\CurrencyDto $currency
     * @param \GamesApiBundle\DataObject\CurrencyDto $topWonAmountsCurrency
     * @param PartnerInitialInfoPromotionData $promotionData
     * @param PartnerInitialInfoTaxes|null $taxes
     * @param array $games
     * @param bool $isGeoBlocked
     * @param array $sockets
     * @param string $gaCode
     */
    public function __construct(
        array $buttonAmounts,
        Partner $partner,
        bool $isRtl,
        array $possibleRunsCount,
        bool $reinitSessionOnGameNavigation,
        string $videoLogoUrl,
        ?PartnerInitialInfoPlayer $player,
        CurrencyDto $currency,
        CurrencyDto $topWonAmountsCurrency,
        PartnerInitialInfoPromotionData $promotionData,
        ?PartnerInitialInfoTaxes $taxes,
        array $games,
        bool $isGeoBlocked,
        array $sockets,
        string $gaCode
    )
    {
        foreach ($buttonAmounts as $buttonAmount) {
            // Converting to string to get normal precision, not json serialize_precision like 1.0000000001.
            $this->betAmounts[] = (string) $buttonAmount->getValue();
        }

        $this->isCombinationEnabled = $partner->getCombinationEnabled();
        $this->isItalianSpeech = $partner->getItalianSpeech();
        $this->isNumpadEnabled = $partner->getApiShowNumpad();
        $this->isSubscriptionsEnabled = $partner->getSubscriptionEnabled();
        $this->isFullscreenEnabled = $partner->getIsFullscreenEnabled();
        $this->isNotificationEnabled = $partner->getNotificationEnabled();
        $this->isTerminalView = $partner->getIsTerminalView();
        $this->isSurveillanceEnabled = $partner->getSurveillanceStreamEnabled();
        $this->isRtl = $isRtl;
        $this->disableVideoOnInactivity = $partner->getDisableVideoOnInactivity();
        $this->allowExperimentalUI = $partner->getAllowExperimentalUi();
        $this->possibleRunsCount = $possibleRunsCount;
        $this->rememberLastStake =  $partner->getRememberLastStake();
        $this->reinitSessionOnGameNavigation = $reinitSessionOnGameNavigation;
        $this->showMessagesOnChangedOdds = $partner->getShowMessageOnChangedOdds();
        $this->showPlayersBetsSum = $partner->getShowBetsSum();
        $this->showClock = $partner->getShowClock();
        $this->showGuiTip = $partner->getShowGuiTip();
        $this->videoLogoUrl = $videoLogoUrl;
        $this->cssCustom = $partner->getCssCustom();
        $this->player = $player;
        $this->currency = $currency;
        $this->topWonAmountsCurrency = $topWonAmountsCurrency;
        $this->promotions = $promotionData;
        $this->taxes = $taxes;
        $this->games = $games;
        $this->isGeoBlocked = $isGeoBlocked;
        $this->sockets = $sockets;
        $this->gaCode = $gaCode;
        $this->refreshTokenFrequency = $partner->getWebApiRefreshTokenFrequency() * Time::MILLISECONDS_IN_SECOND;
    }
}
