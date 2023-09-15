<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification;

use CoreBundle\Exception\ValidationException;

/**
 * Class AvatarValidator
 */
final class AvatarValidator
{
    /**
     * @param string $requestAvatar
     * @param int $currentLevel
     *
     * @throws ValidationException
     */
    public function validateLevelAvailability(string $requestAvatar, int $currentLevel): void
    {
        $avatarsByLevel = AvatarProvider::getAvatarsByLevel();

        foreach ($avatarsByLevel as $requiredLevel => $availableAvatars) {
            if (array_key_exists($requestAvatar, $availableAvatars) && $currentLevel >= $requiredLevel) {
                return;
            }
        }

        throw new ValidationException('AVATAR_NOT_ALLOWED');
    }
}