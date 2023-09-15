<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\InitialInfo\Component;

use GamesApiBundle\DataObject\PlayerBalance;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class PartnerInitialInfoPlayer
 *
 * @psalm-readonly
 * @deprecated frontend is migrating to initial API v3 - avoid modifying any initial API v2 code if possible
 */
final class PartnerInitialInfoPlayer
{
    /** @SerializedName("id") */
    public int $id;
    /** @SerializedName("balance") */
    public PlayerBalance $balance;
    /** @SerializedName("token") */
    public ?string $token;
    /** @SerializedName("gamification") */
    public ?PartnerInitialInfoGamification $gamification;

    /**
     * Player constructor.
     *
     * @param int $id
     * @param PlayerBalance $balance
     * @param string|null $token
     * @param PartnerInitialInfoGamification|null $gamification
     */
    public function __construct(
        int $id,
        PlayerBalance $balance,
        ?string $token,
        ?PartnerInitialInfoGamification $gamification
    )
    {
        $this->id = $id;
        $this->balance = $balance;
        $this->token = $token;
        $this->gamification = $gamification;
    }
}