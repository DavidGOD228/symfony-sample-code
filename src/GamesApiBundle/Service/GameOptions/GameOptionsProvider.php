<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\GameOptions;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GroupingOdd;
use Acme\SymfonyDb\Entity\Partner;
use CoreBundle\Service\CacheServiceInterface;
use CoreBundle\Service\GameService;
use CoreBundle\Service\OddService;
use Acme\Time\Time;
use GamesApiBundle\DataObject\GameOptions\GameOptionsBettingOption;
use GamesApiBundle\DataObject\GameOptions\GameOptionsBettingOptionsGroup;
use GamesApiBundle\DataObject\GameOptions\GameOptionsGameItem;
use GamesApiBundle\DataObject\GameOptions\GameOptions;
use GamesApiBundle\DataObject\GameOptions\GameOptionsRequest;
use GamesApiBundle\Service\GameItemService;

/**
 * Class GameOptionsProvider
 */
final class GameOptionsProvider
{
    private const GAME_ITEMS_CACHE_PREFIX = 'game-items';
    private const ODDS_GROUPS_CACHE_PREFIX = 'odds-groups';

    private GameService $gameService;
    private GameItemService $gameItemService;
    private OddService $oddsService;
    private CacheServiceInterface $cacheService;
    private GameOptionsDataMatkaBuilder $matkaBuilder;

    /**
     * GameSettingsProvider constructor.
     *
     * @param GameService $gameService
     * @param GameItemService $gameItemService
     * @param OddService $oddsService
     * @param CacheServiceInterface $cacheService
     * @param GameOptionsDataMatkaBuilder $matkaBuilder
     */
    public function __construct(
        GameService $gameService,
        GameItemService $gameItemService,
        OddService $oddsService,
        CacheServiceInterface $cacheService,
        GameOptionsDataMatkaBuilder $matkaBuilder
    )
    {
        $this->gameService = $gameService;
        $this->gameItemService = $gameItemService;
        $this->oddsService = $oddsService;
        $this->cacheService = $cacheService;
        $this->matkaBuilder = $matkaBuilder;
    }

    /**
     * @param GameOptionsRequest $request
     * @param Partner $partner
     *
     * @return GameOptions[]
     */
    public function get(GameOptionsRequest $request, Partner $partner): array
    {
        $enabledGames = $this->gameService->getPartnerEnabledGamesByIds(
            $partner->getId(),
            $request->getGameIds()
        );
        $enabledOddsIds = $this->getEnabledOddsIds($partner);
        $gameOptions = [];

        foreach ($enabledGames as $enabledGame) {
            $gameId = $enabledGame->getGame()->getId();

            $data = $gameId === GameDefinition::MATKA ? $this->matkaBuilder->buildMatkaData() : null;

            $gameOptions[] = new GameOptions(
                $gameId,
                $enabledGame->getOddPresetId(),
                $this->getBettingOptionsGroups($enabledGame->getGame(), $enabledOddsIds),
                $this->getCachedGameItems($enabledGame->getGame()),
                $data
            );
        }

        return $gameOptions;
    }

    /**
     * @param Partner $partner
     *
     * @return int[]
     */
    private function getEnabledOddsIds(Partner $partner): array
    {
        $enabledOdds = $this->oddsService->getOdds($partner, []);
        $enabledOddsIds = [];
        foreach ($enabledOdds as $odds) {
            $enabledOddsIds[] = $odds->getId();
        }

        return $enabledOddsIds;
    }

    /**
     * @param Game $game
     *
     * @return GameOptionsGameItem[]
     *
     * Constructing cache key out of valid string & integer will not throw LogicException or InvalidArgumentException.
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function getCachedGameItems(Game $game): array
    {
        $gameItemsCacheKey = $this->cacheService->getCacheKey(
            [
                self::GAME_ITEMS_CACHE_PREFIX,
                $game->getId(),
            ]
        );
        $cachedGameItems = $this->cacheService->getUnserialized($gameItemsCacheKey);

        if ($cachedGameItems) {
            return $cachedGameItems;
        }

        $gameItems = [];
        foreach ($game->getGameItems() as $gameItemData) {
            $gameItems[] = new GameOptionsGameItem($gameItemData, $this->gameItemService->getType($gameItemData));
        }

        $this->cacheService->set($gameItemsCacheKey, $gameItems, Time::SECONDS_IN_MINUTE * 10);

        return $gameItems;
    }

    /**
     * @param Game $game
     * @param int[] $enabledOddsIds
     *
     * @return GameOptionsBettingOptionsGroup[]
     */
    private function getBettingOptionsGroups(Game $game, array $enabledOddsIds): array
    {
        $oddsGroups = $this->getCachedOddsGroups($game);
        $bettingOptionsGroups = [];
        foreach ($oddsGroups as $oddsGroup) {
            $bettingOptions = [];
            /** @var GroupingOdd $groupingOdd */
            foreach ($oddsGroup->getGroupingOdds() as $groupingOdd) {
                $optionDataProvider = $groupingOdd->getOdd();
                if (in_array($optionDataProvider->getId(), $enabledOddsIds)) {
                    $bettingOptions[] = new GameOptionsBettingOption($optionDataProvider);
                }
            }
            $bettingOptionsGroups[] = new GameOptionsBettingOptionsGroup($oddsGroup->getId(), $bettingOptions);
        }

        return $bettingOptionsGroups;
    }

    /**
     * @param Game $game
     *
     * @return array
     *
     * Constructing cache key out of valid string & integer will not throw LogicException or InvalidArgumentException.
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function getCachedOddsGroups(Game $game): array
    {
        $oddsGroupsCacheKey = $this->cacheService->getCacheKey(
            [
                self::ODDS_GROUPS_CACHE_PREFIX,
                $game->getId(),
            ]
        );
        $cachedOddsGroups = $this->cacheService->getUnserialized($oddsGroupsCacheKey);

        if ($cachedOddsGroups) {
            return $cachedOddsGroups;
        }

        $oddsGroups = $this->oddsService->getEnabledOddGroups($game);
        $this->cacheService->set($oddsGroupsCacheKey, $oddsGroups, Time::SECONDS_IN_MINUTE * 10);

        return $oddsGroups;
    }
}
