<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\Gamification;

use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class GamificationSettingsResponse
 *
 * @psalm-immutable
 */
final class GamificationSettingsResponse
{
    /** @SerializedName("appKey") */
    public string $appKey;
    /** @SerializedName("clientToken") */
    public string $clientToken;
    /** @SerializedName("envPrefix") */
    public string $envPrefix;
    /** @SerializedName("sdkUrl") */
    public string $sdkUrl;
    /** @SerializedName("profile") */
    public ?GamificationProfile $profile;

    /**
     * Constructor.
     *
     * @param string $appKey
     * @param string $clientToken
     * @param string $envPrefix
     * @param string $sdkUrl
     * @param GamificationProfile|null $profile
     */
    public function __construct(
        string $appKey,
        string $clientToken,
        string $envPrefix,
        string $sdkUrl,
        ?GamificationProfile $profile
    )
    {
        $this->appKey = $appKey;
        $this->clientToken = $clientToken;
        $this->envPrefix = $envPrefix;
        $this->sdkUrl = $sdkUrl;
        $this->profile = $profile;
    }
}