<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification;

use Acme\SymfonyDb\Entity\Player;
use GamesApiBundle\DataObject\Gamification\GamificationProfile;
use GamesApiBundle\DataObject\Gamification\GamificationSettingsResponse;
use GamesApiBundle\Exception\Gamification\GamificationNotEnabledException;

/**
 * Class GamificationSettingsBuilder
 */
final class GamificationSettingsResponseBuilder
{
    private CredentialsProvider $credentialsProvider;
    private UserHashGenerator $hashGenerator;

    /**
     * Constructor.
     *
     * @param CredentialsProvider $credentialsProvider
     * @param UserHashGenerator $hashGenerator
     */
    public function __construct(CredentialsProvider $credentialsProvider, UserHashGenerator $hashGenerator)
    {
        $this->credentialsProvider = $credentialsProvider;
        $this->hashGenerator = $hashGenerator;
    }

    /**
     * @param Player $player
     *
     * @return GamificationSettingsResponse
     *
     * @throws GamificationNotEnabledException
     */
    public function build(Player $player): GamificationSettingsResponse
    {
        if (!$player->getPartner()->getGamificationEnabled()) {
            throw new GamificationNotEnabledException();
        }

        $appKey = $this->credentialsProvider->getAppKey();
        $clientToken = $this->credentialsProvider->getClientToken();
        $sdkUrl = $this->credentialsProvider->getSdkUrl();
        $envPrefix = $this->credentialsProvider->getEnvPrefix();

        $profile = $this->getGamificationProfile($player, $envPrefix);

        return new GamificationSettingsResponse(
            $appKey,
            $clientToken,
            $envPrefix,
            $sdkUrl,
            $profile
        );
    }

    /**
     * @param Player $player
     * @param string $envPrefix
     *
     * @return GamificationProfile|null
     */
    private function getGamificationProfile(Player $player, string $envPrefix): ?GamificationProfile
    {
        $internalProfile = $player->getProfile();
        if (!$internalProfile) {
            return null;
        }

        $profileForSigning = $this->hashGenerator->getProfileForSigning(
            $internalProfile,
            $envPrefix
        );

        $signedUser = $this->hashGenerator->generate(
            $profileForSigning,
            $this->credentialsProvider->getAppSecret()
        );

        return new GamificationProfile(
            $this->hashGenerator->getProfileId($player->getId(), $envPrefix),
            $internalProfile,
            $signedUser
        );
    }
}
