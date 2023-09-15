<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification;

use CoreBundle\Service\Utility\RequestService;

/**
 * Class AvatarProvider
 */
final class AvatarProvider
{
    private const STATIC_PATH = '/gamification/player_profile_avatars/';
    private const DEFAULT_AVATAR_NAME = 'avatar_1.jpg';

    private const PATH_FIELD = 'path';
    private const LEVELS_FIELD = 'levels';

    private const LEVEL_1 = 1;
    private const LEVEL_5 = 5;
    private const LEVEL_11 = 11;

    /*
     * Temporar const until we will implement avatars presets for partners
     * ToDo: Remove after - https://jira.Acme.tv/browse/CORE-2602
     */
    private const AVATAR_PRESET = [
        self::LEVELS_FIELD => [
            // level => list of avatars
            self::LEVEL_1 => [
                'avatar_1' => self::DEFAULT_AVATAR_NAME,
                'avatar_2' => 'avatar_2.jpg',
                'avatar_3' => 'avatar_3.jpg',
                'avatar_4' => 'avatar_4.jpg',
                'avatar_5' => 'avatar_5.jpg',
            ],
            self::LEVEL_5 => [
                'avatar_6' => 'avatar_6.jpg',
                'avatar_7' => 'avatar_7.jpg',
                'avatar_8' => 'avatar_8.jpg',
                'avatar_9' => 'avatar_9.jpg',
                'avatar_10' => 'avatar_10.jpg',
            ],
            self::LEVEL_11 => [
                'avatar_11' => 'avatar_11.jpg',
                'avatar_12' => 'avatar_12.jpg',
                'avatar_13' => 'avatar_13.jpg',
                'avatar_14' => 'avatar_14.jpg',
            ],
        ],
    ];

    private RequestService $requestService;

    /**
     * AvatarProvider constructor.
     *
     * @param RequestService $requestService
     */
    public function __construct(RequestService $requestService)
    {
        $this->requestService = $requestService;
    }

    /**
     * @return string[]
     */
    public static function getAvatarsByLevel(): array
    {
        return self::AVATAR_PRESET[self::LEVELS_FIELD];
    }

    /**
     * @param string $rootDomain
     *
     * @return array
     */
    public function getPreset(string $rootDomain): array
    {
        $preset = self::AVATAR_PRESET;

        $url = $this->requestService->getStaticUrl($rootDomain);
        $preset[self::PATH_FIELD] = $url . self::STATIC_PATH;

        return $preset;
    }

    /**
     * @param string $rootDomain
     *
     * @return string
     */
    public function getDefaultUrl(string $rootDomain): string
    {
        $url = $this->requestService->getStaticUrl($rootDomain);

        return $url . self::STATIC_PATH . self::DEFAULT_AVATAR_NAME;
    }

    /**
     * @param string $rootDomain
     * @param string $fileName
     *
     * @return string
     */
    public function getAvatarUrl(string $rootDomain, string $fileName): string
    {
        $urls = [];

        foreach (self::AVATAR_PRESET[self::LEVELS_FIELD] as $avatarsByLevel) {
            foreach ($avatarsByLevel as $name => $url) {
                $urls[$name] = $url;
            }
        }

        $fileUrl = $urls[$fileName];

        $staticUrl = $this->requestService->getStaticUrl($rootDomain);
        $url = $staticUrl . self::STATIC_PATH . $fileUrl;

        return $url;
    }

    /**
     * @return array
     */
    public function getAvailableAvatars(): array
    {
        $avatars = [];

        foreach (self::AVATAR_PRESET[self::LEVELS_FIELD] as $avatarsByLevel) {
            foreach ($avatarsByLevel as $name => $url) {
                $avatars[] = $name;
            }
        }

        return $avatars;
    }
}
