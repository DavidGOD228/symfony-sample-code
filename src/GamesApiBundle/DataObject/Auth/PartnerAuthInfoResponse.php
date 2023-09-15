<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\Auth;

use Acme\SymfonyDb\Entity\CurrencyButtonAmount;
use GamesApiBundle\DataObject\CurrencyDto;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class PartnerAuthInfoResponse
 *
 * @psalm-immutable
 */
final class PartnerAuthInfoResponse
{
    /**  @SerializedName("auth") */
    public string $auth;
    /** @SerializedName("playerId") */
    public int $playerId;
    /** @SerializedName("playerTag") */
    public string $playerTag;
    /** @SerializedName("balance") */
    public string $balance;
    /**
     * @var array<string>
     * @SerializedName("betAmounts")
     */
    public array $betAmounts;
    /** @SerializedName("currency") */
    public CurrencyDto $currency;
    /**
     * @var array<int,array<int>>
     * @SerializedName("favoriteBettingOptionsIds")
     */
    public array $favoriteBettingOptionsIds;
    /**  @SerializedName("webSocketsToken") */
    public string $webSocketsToken;

    /**
     * PartnerAuthInfoResponse constructor.
     *
     * @param string $sessionId
     * @param PartnerAuthInfoPlayer $player
     * @param array<CurrencyButtonAmount> $currencyButtonAmounts
     * @param CurrencyDto $currency
     * @param array<int,array<int>> $favoriteOddsIds [gameId => favoriteOdds[]]
     * @param string $webSocketsToken
     */
    public function __construct(
        string $sessionId,
        PartnerAuthInfoPlayer $player,
        array $currencyButtonAmounts,
        CurrencyDto $currency,
        array $favoriteOddsIds,
        string $webSocketsToken
    )
    {
        foreach ($currencyButtonAmounts as $buttonAmount) {
            // Converting to string to get normal precision, not json serialize_precision like 1.0000000001.
            $this->betAmounts[] = (string) $buttonAmount->getValue();
        }

        $this->auth = $sessionId;
        $this->playerId = $player->id;
        $this->playerTag = $player->tag;
        $this->balance = $player->balance->getValue();
        $this->currency = $currency;
        $this->favoriteBettingOptionsIds = $favoriteOddsIds;
        $this->webSocketsToken = $webSocketsToken;
    }
}
