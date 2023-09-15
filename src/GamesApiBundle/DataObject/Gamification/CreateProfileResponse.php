<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\Gamification;

/**
 * Class CreateProfileResponse
 */
final class CreateProfileResponse
{
    private string $id;
    private string $externalId;
    private string $signedUser;

    /**
     * GamificationProfile constructor.
     *
     * @param string $id
     * @param string $externalId
     * @param string $signedUser
     */
    public function __construct(
        string $id,
        string $externalId,
        string $signedUser
    )
    {
        $this->id = $id;
        $this->externalId = $externalId;
        $this->signedUser = $signedUser;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getExternalId(): string
    {
        return $this->externalId;
    }

    /**
     * @return string
     */
    public function getSignedUser(): string
    {
        return $this->signedUser;
    }
}