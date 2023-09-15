<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\Auth;

use GamesApiBundle\DataObject\PlayerBalance;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class PartnerAuthInfoPlayer
 *
 * @psalm-readonly
 */
final class PartnerAuthInfoPlayer
{
    /** @SerializedName("id") */
    public int $id;
    /** @SerializedName("balance") */
    public PlayerBalance $balance;
    /** @SerializedName("token") */
    public ?string $token;
    /** @SerializedName("tag") */
    public string $tag;

    /**
     * Player constructor.
     *
     * @param int $id
     * @param PlayerBalance $balance
     * @param string $tag
     * @param string|null $token
     */
    public function __construct(
        int $id,
        PlayerBalance $balance,
        string $tag,
        ?string $token
    )
    {
        $this->id = $id;
        $this->balance = $balance;
        $this->token = $token;
        $this->tag = $tag;
    }
}