<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification;

use Acme\SymfonyDb\Entity\PlayerProfile;
use CoreBundle\Utils\HashAlgorithm;

/**
 * Class UserHashGenerator
 */
final class UserHashGenerator
{
    /**
     * @param PlayerProfile $profile
     * @param string $envPrefix
     *
     * @return array
     */
    public function getProfileForSigning(PlayerProfile $profile, string $envPrefix): array
    {
        $profileForSigning = [
            'id' => $this->getProfileId($profile->getPlayer()->getId(), $envPrefix),
            'name' => $profile->getName(),
            'image' => $profile->getAvatarUrl(),
            'partner_code' => $envPrefix . ':' .$profile->getPlayer()->getPartner()->getApiCode(),
        ];

        return $profileForSigning;
    }

    /**
     * Hashing original player id to avoid exposing it to FE.
     * Using prefix 2 times:
     *  - as prefix for nice filtering/sorting users by infrastructure.
     *  - as part of hash to avoid rainbow tables or bruteforce lookup.
     *
     * @param int $playerId
     * @param string $envPrefix
     *
     * @return string
     */
    public function getProfileId(int $playerId, string $envPrefix): string
    {
        $id = $envPrefix . ':' . hash(
            HashAlgorithm::SHA_512,
            $envPrefix . ':' . $playerId
        );

        return $id;
    }

    /**
     * @param array $profileForSigning
     * @param string $secret
     *
     * @return string
     */
    public function generate(array $profileForSigning, string $secret): string
    {
        // Sort the array alphabetically by the key names.
        ksort($profileForSigning, SORT_LOCALE_STRING);
        // no encoding to avoid whitespace or special character changing.
        $signedUser = http_build_query($profileForSigning);
        $signedUser = urldecode($signedUser);
        $signedUser = hash_hmac(HashAlgorithm::SHA_512, $signedUser, $secret);
        $signedUser = base64_encode($signedUser);
        // remove new lines and and the '=' part at the end
        $signedUser = preg_replace('/(\n|=+\n?$)/', '', $signedUser);

        return $signedUser;
    }
}
