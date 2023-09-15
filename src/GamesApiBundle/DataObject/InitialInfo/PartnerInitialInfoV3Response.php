<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\InitialInfo;

use Acme\SymfonyDb\Entity\CurrencyButtonAmount;
use Acme\SymfonyDb\Entity\Partner;
use Acme\Time\Time;
use GamesApiBundle\DataObject\CurrencyDto;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoGameV3;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoTaxes;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class PartnerInitialInfoV3Response
 *
 * @psalm-immutable
 */
class PartnerInitialInfoV3Response
{
    /** @SerializedName("betAmounts") */
    public array $betAmounts = [];
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
    /** @SerializedName("isGamificationEnabled") */
    public bool $isGamificationEnabled;
    /** @SerializedName("isFullscreenEnabled") */
    public bool $isFullscreenEnabled;
    /** @SerializedName("isTerminalView") */
    public bool $isTerminalView;
    /** @SerializedName("disableVideoOnInactivity") */
    public bool $disableVideoOnInactivity;
    /** @SerializedName("isGeoBlocked") */
    public bool $isGeoBlocked;
    /** @SerializedName("possibleRunsCount") */
    public array $possibleRunsCount;
    /** @SerializedName("rememberLastStake") */
    public bool $rememberLastStake;
    /** @SerializedName("reinitSessionOnGameNavigation") */
    public bool $reinitSessionOnGameNavigation;
    /**
     * @var array<PartnerInitialInfoGameV3>
     * @SerializedName("enabledGames")
     */
    public array $enabledGames;
    /** @SerializedName("showMessagesOnChangedOdds") */
    public bool $showMessagesOnChangedOdds;
    /** @SerializedName("showClock") */
    public bool $showClock;
    /** @SerializedName("showGuiTip") */
    public bool $showGuiTip;
    /** @SerializedName("showPlayersBetsSum") */
    public bool $showPlayersBetsSum;
    /** @SerializedName("showBalance") */
    public bool $showBalance;
    /** @SerializedName("isSurveillanceEnabled") */
    public bool $isSurveillanceEnabled;
    /** @SerializedName("videoLogoUrl") */
    public string $videoLogoUrl;
    /** @SerializedName("cssCustom") */
    public ?string $cssCustom;
    /** @SerializedName("currency") */
    public CurrencyDto $currency;
    /** @SerializedName("topWonAmountsCurrency") */
    public $topWonAmountsCurrency;
    /** @SerializedName("taxes") */
    public ?PartnerInitialInfoTaxes $taxes;
    /** @SerializedName("webSocketUrl") */
    public string $webSocketUrl;
    /** @SerializedName("gaCode") */
    public string $gaCode;
    /** @SerializedName("refreshTokenFrequency") */
    public int $refreshTokenFrequency;

    /**
     * @param CurrencyButtonAmount[] $buttonAmounts
     * @param Partner $partner
     * @param array $possibleRunsCount
     * @param bool $reinitSessionOnGameNavigation
     * @param int[] $enabledGames
     * @param string $videoLogoUrl
     * @param CurrencyDto $currency
     * @param CurrencyDto $topWonAmountsCurrency
     * @param PartnerInitialInfoTaxes|null $taxes
     * @param bool $isGeoBlocked
     * @param string $webSocketUrl
     * @param string $gaCode
     */
    public function __construct(
        array $buttonAmounts,
        Partner $partner,
        array $possibleRunsCount,
        bool $reinitSessionOnGameNavigation,
        array $enabledGames,
        string $videoLogoUrl,
        CurrencyDto $currency,
        CurrencyDto $topWonAmountsCurrency,
        ?PartnerInitialInfoTaxes $taxes,
        bool $isGeoBlocked,
        string $webSocketUrl,
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
        $this->isGamificationEnabled = $partner->getGamificationEnabled();
        $this->isFullscreenEnabled = $partner->getIsFullscreenEnabled();
        $this->isNotificationEnabled = $partner->getNotificationEnabled();
        $this->isTerminalView = $partner->getIsTerminalView();
        $this->isSurveillanceEnabled = $partner->getSurveillanceStreamEnabled();
        $this->disableVideoOnInactivity = $partner->getDisableVideoOnInactivity();
        $this->possibleRunsCount = $possibleRunsCount;
        $this->rememberLastStake =  $partner->getRememberLastStake();
        $this->reinitSessionOnGameNavigation = $reinitSessionOnGameNavigation;
        $this->enabledGames = $enabledGames;
        $this->showMessagesOnChangedOdds = $partner->getShowMessageOnChangedOdds();
        $this->showPlayersBetsSum = $partner->getShowBetsSum();
        $this->showBalance = $partner->getApiShowBalance();
        $this->showClock = $partner->getShowClock();
        $this->showGuiTip = $partner->getShowGuiTip();
        $this->videoLogoUrl = $videoLogoUrl;
        $this->cssCustom = $partner->getCssCustom();
        $this->currency = $currency;
        $this->topWonAmountsCurrency = $topWonAmountsCurrency;
        $this->taxes = $taxes;
        $this->isGeoBlocked = $isGeoBlocked;
        $this->webSocketUrl = $webSocketUrl;
        $this->gaCode = $gaCode;
        $this->refreshTokenFrequency = $partner->getWebApiRefreshTokenFrequency() * Time::MILLISECONDS_IN_SECOND;
    }
}
