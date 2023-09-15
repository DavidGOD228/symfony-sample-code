<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Initial\Component;

use Acme\SymfonyDb\Entity\Player;
use GamesApiBundle\DataObject\Gamification\GamificationProfile;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoGamification;
use GamesApiBundle\Service\Gamification\CredentialsProvider;
use GamesApiBundle\Service\Gamification\UserHashGenerator;

/**
 * Class InitialGamificationBuilder
 * @deprecated frontend is migrating to initial API v3 - avoid modifying any initial API v2 code if possible
 */
final class InitialGamificationBuilder
{
    private const MIN_IOS_PACKAGE_VERSION = '1.7.0';

    private CredentialsProvider $credentialsProvider;
    private UserHashGenerator $hashGenerator;

    /**
     * @param CredentialsProvider $credentialsProvider
     * @param UserHashGenerator $hashGenerator
     */
    public function __construct(
        CredentialsProvider $credentialsProvider,
        UserHashGenerator $hashGenerator
    )
    {
        $this->credentialsProvider = $credentialsProvider;
        $this->hashGenerator = $hashGenerator;
    }

    /**
     * @param Player $player
     * @param string|null $iosAppVersion
     *
     * @return PartnerInitialInfoGamification|null
     */
    public function build(
        Player $player,
        ?string $iosAppVersion
    ): ?PartnerInitialInfoGamification
    {
        if (!$player->getPartner()->getGamificationEnabled()) {
            return null;
        }
        if (!$this->isAvailableInCurrentApp($iosAppVersion)) {
            return null;
        }

        $internalProfile = $player->getProfile();

        $appKey = $this->credentialsProvider->getAppKey();
        $clientToken = $this->credentialsProvider->getClientToken();
        $sdkUrl = $this->credentialsProvider->getSdkUrl();
        $envPrefix = $this->credentialsProvider->getEnvPrefix();

        if (!$internalProfile) {
            return new PartnerInitialInfoGamification(
                null,
                $appKey,
                $clientToken,
                $sdkUrl,
                $envPrefix
            );
        }

        $profileForSigning = $this->hashGenerator->getProfileForSigning(
            $internalProfile,
            $envPrefix
        );

        $signedUser = $this->hashGenerator->generate(
            $profileForSigning,
            $this->credentialsProvider->getAppSecret()
        );

        $profile = new GamificationProfile(
            $this->hashGenerator->getProfileId($player->getId(), $envPrefix),
            $internalProfile,
            $signedUser
        );

        return new PartnerInitialInfoGamification(
            $profile,
            $appKey,
            $clientToken,
            $sdkUrl,
            $envPrefix
        );
    }

    /**
     * When request comes from old iOs app, should be no gamification.
     *
     * @param string|null $iosAppVersion
     *
     * @return bool
     */
    private function isAvailableInCurrentApp(?string $iosAppVersion) : bool
    {
        if (!$iosAppVersion) {
            return true;
        }

        if (version_compare($iosAppVersion, self::MIN_IOS_PACKAGE_VERSION, '<')) {
            return false;
        }

        return true;
    }
}
