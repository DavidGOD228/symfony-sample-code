<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Initial\Component;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\PartnerEnabledGame;
use CoreBundle\Service\GameService;

/**
 * Class FilteredEnabledGamesProvider
 */
final class FilteredEnabledGamesProvider
{
    private const MIN_IOS_PACKAGE_FOR_WHEEL = '1.7.1';
    private const MIN_IOS_PACKAGE_FOR_MATKA = '1.7.1';

    private GameService $gameService;

    /**
     * FilteredEnabledGamesProvider constructor.
     *
     * @param GameService $gameService
     */
    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }


    /**
     * @param Partner $partner
     * @param string|null $iosAppVersion
     *
     * @return array|PartnerEnabledGame[]
     */
    public function getFilteredEnabledGames(Partner $partner, ?string $iosAppVersion) : array
    {
        $enabledGames = $this->gameService->getPartnerEnabledGames($partner);

        $this->filterGamesForOldIosPackages($enabledGames, $iosAppVersion);

        return $enabledGames;
    }

    /**
     * If this is a request from an IOS app with an old version,
     * remove wheel from the games list
     *
     * @param array $enabledGames
     * @param string|null $iosAppVersion
     */
    private function filterGamesForOldIosPackages(array &$enabledGames, ?string $iosAppVersion) : void
    {
        if (!$iosAppVersion) {
            return;
        }

        if (version_compare($iosAppVersion, self::MIN_IOS_PACKAGE_FOR_WHEEL, '<')) {
            unset($enabledGames[GameDefinition::WHEEL]);
        }

        if (version_compare($iosAppVersion, self::MIN_IOS_PACKAGE_FOR_MATKA, '<')) {
            unset($enabledGames[GameDefinition::MATKA]);
        }
    }
}
