<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\InitialInfo\Component;

use GamesApiBundle\DataObject\Gamification\GamificationProfile;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class PartnerInitialInfoGamification
 *
 * @psalm-immutable
 * @deprecated frontend is migrating to initial API v3 - avoid modifying any initial API v2 code if possible
 */
final class PartnerInitialInfoGamification
{
    /** @SerializedName("profile") */
    public ?GamificationProfile $profile;
    /** @SerializedName("appKey") */
    public string $appKey;
    /** @SerializedName("clientToken") */
    public string $clientToken;
    /** @SerializedName("sdkUrl") */
    public string $sdkUrl;
    /** @SerializedName("envPrefix") */
    public string $envPrefix;

    /**
     * PartnerInitialInfoGamification constructor.
     *
     * @param GamificationProfile|null $profile
     * @param string $appKey
     * @param string $clientToken
     * @param string $sdkUrl
     * @param string $envPrefix
     */
    public function __construct(
        ?GamificationProfile $profile,
        string $appKey,
        string $clientToken,
        string $sdkUrl,
        string $envPrefix
    )
    {
        $this->profile = $profile;
        $this->appKey = $appKey;
        $this->clientToken = $clientToken;
        $this->sdkUrl = $sdkUrl;
        $this->envPrefix = $envPrefix;
    }
}