<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\Gamification;

use Acme\SymfonyDb\Entity\PlayerProfile;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class GamificationProfile
 */
final class GamificationProfile
{
    private string $id;
    private string $externalId;
    private string $name;
    private bool $blocked;
    private string $signedUser;
    private string $image;

    /**
     * GamificationProfile constructor.
     *
     * @param string $id
     * @param PlayerProfile $profile
     * @param string $signedUser
     */
    public function __construct(
        string $id,
        PlayerProfile $profile,
        string $signedUser
    )
    {
        $this->id = $id;
        $this->externalId = $profile->getExternalId();
        $this->name = $profile->getName();
        $this->blocked = $profile->isBlocked();
        $this->signedUser = $signedUser;
        $this->image = $profile->getAvatarUrl();
    }

    /**
     * @SerializedName("id")
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @SerializedName("externalId")
     *
     * @return string
     */
    public function getExternalId(): string
    {
        return $this->externalId;
    }

    /**
     * @SerializedName("name")
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @SerializedName("blocked")
     *
     * @return bool
     */
    public function isBlocked(): bool
    {
        return $this->blocked;
    }

    /**
     * @SerializedName("signedUser")
     *
     * @return string
     */
    public function getSignedUser(): string
    {
        return $this->signedUser;
    }

    /**
     * @SerializedName ("image")
     *
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }
}
