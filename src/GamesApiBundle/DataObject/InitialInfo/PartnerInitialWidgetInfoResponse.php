<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\InitialInfo;

use Acme\SymfonyDb\Entity\Partner;
use GamesApiBundle\DataObject\CurrencyDto;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoTaxes;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class PartnerInitialWidgetInfoResponse
 *
 * @psalm-immutable
 */
class PartnerInitialWidgetInfoResponse
{
    /**
     * @var string[]
     * @SerializedName("betAmounts")
     */
    public array $betAmounts;

    /** @SerializedName("cssCustom") */
    public ?string $cssCustom;

    /** @SerializedName("currency") */
    public CurrencyDto $currency;

    /** @SerializedName("isGeoBlocked") */
    public bool $isGeoBlocked;

    /** @SerializedName("availableGamesIds") */
    public array $availableGamesIds;

    /** @SerializedName("playerId") */
    public ?int $playerId;

    /** @SerializedName("rememberLastStake") */
    public bool $rememberLastStake;

    /** @SerializedName("showClock") */
    public bool $showClock;

    /** @SerializedName("sockets") */
    public array $sockets;

    /** @SerializedName("taxes") */
    public ?PartnerInitialInfoTaxes $taxes;

    /**
     * PartnerInitialInfoWidgetResponse constructor.
     *
     * @param array $buttonAmounts
     * @param Partner $partner
     * @param int|null $playerId
     * @param \GamesApiBundle\DataObject\CurrencyDto $currency
     * @param PartnerInitialInfoTaxes|null $taxes
     * @param array $games
     * @param bool $isGeoBlocked
     * @param array $sockets
     */
    public function __construct(
        array $buttonAmounts,
        Partner $partner,
        ?int $playerId,
        CurrencyDto $currency,
        ?PartnerInitialInfoTaxes $taxes,
        array $games,
        bool $isGeoBlocked,
        array $sockets
    )
    {
        foreach ($buttonAmounts as $buttonAmount) {
            // Converting to string to get normal precision, not json serialize_precision like 1.0000000001.
            $this->betAmounts[] = (string) $buttonAmount->getValue();
        }

        $availableGamesIds = [];
        foreach ($games as $gameId => $game) {
            $availableGamesIds[] = $gameId;
        }

        $this->cssCustom = $partner->getCssCustomWidget();
        $this->currency = $currency;
        $this->isGeoBlocked = $isGeoBlocked;
        $this->availableGamesIds = $availableGamesIds;
        $this->playerId = $playerId;
        $this->rememberLastStake =  $partner->getRememberLastStake();
        $this->showClock = $partner->getShowClock();
        $this->sockets = $sockets;
        $this->taxes = $taxes;
    }
}
