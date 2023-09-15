<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Initial\Component;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\PartnerEnabledGame;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Type\OddGroupNameType;
use Acme\SymfonyDb\Type\StreamPlatformType;
use CoreBundle\Service\OddService;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoGame;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoGameItem;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoOddGroup;
use GamesApiBundle\Service\GameStateService;
use StreamBundle\Service\StreamProvider;

/**
 * Class InitialGamesBuilder
 */
final class InitialGamesBuilder
{
    private const BAZAAR_GROUPS = [
        OddGroupNameType::BAZAAR_MAIN_BETS,
        OddGroupNameType::BAZAAR_OPEN,
        OddGroupNameType::BAZAAR_CLOSE,
    ];

    private OddService $oddService;
    private GameStateService $gameStateService;
    private StreamProvider $streamProvider;
    private MatkaInitialInfoBuilder $matkaInitialInfoBuilder;

    /**
     * @param OddService $oddService
     * @param GameStateService $gameStateService
     * @param StreamProvider $streamProvider
     * @param MatkaInitialInfoBuilder $matkaInitialInfoBuilder
     */
    public function __construct(
        OddService $oddService,
        GameStateService $gameStateService,
        StreamProvider $streamProvider,
        MatkaInitialInfoBuilder $matkaInitialInfoBuilder
    )
    {
        $this->oddService = $oddService;
        $this->gameStateService = $gameStateService;
        $this->streamProvider = $streamProvider;
        $this->matkaInitialInfoBuilder = $matkaInitialInfoBuilder;
    }

    /**
     * @param Partner $partner
     * @param PartnerEnabledGame[] $enabledGames
     * @param Player|null $player
     * @param string $ip
     *
     * @return PartnerInitialInfoGame[] [$gameId => PartnerInitialInfoGame]
     */
    public function build(
        Partner $partner,
        array $enabledGames,
        ?Player $player,
        string $ip
    ): array
    {
        $oddIdsByGame = $this->getOddIdsByGame($partner);
        $oddGroupsByGame = $this->getOddGroupsByGame($enabledGames);

        if ($player) {
            $playerFavoriteOddIds = $this->getPlayerFavoriteOddsIds($player);
        } else {
            $playerFavoriteOddIds = [];
        }

        $games = [];

        foreach ($enabledGames as $enabledGame) {
            $game = $enabledGame->getGame();

            $gameId = $game->getId();
            $oddIds = $oddIdsByGame[$gameId] ?? [];

            $favoriteOddIds = array_intersect($oddIds, $playerFavoriteOddIds);

            $gameItems = [];

            foreach ($game->getGameItems() as $gameItem) {
                $gameItems[] = new PartnerInitialInfoGameItem($gameItem);
            }

            $oddGroups = $oddGroupsByGame[$gameId];

            if ($partner->isCustomGameOrderEnabled()) {
                $order = $enabledGame->getOrder();
            } else {
                $order = $enabledGame->getGame()->getOrder();
            }

            $matkaInfo = null;
            if ($gameId === GameDefinition::MATKA) {
                $matkaInfo = $this->matkaInitialInfoBuilder->getInfo();
            }

            $games[$gameId] = new PartnerInitialInfoGame(
                $this->gameStateService->getGameState($gameId),
                $this->streamProvider->getStreamDelay($enabledGame, StreamPlatformType::IFRAME, $ip),
                $this->streamProvider->getStreamStartDelay($gameId),
                $order,
                $enabledGame->getOddPresetId(),
                $oddGroups,
                $oddIds,
                $favoriteOddIds,
                $gameItems,
                $matkaInfo
            );
        }

        return $games;
    }

    /**
     * @param PartnerEnabledGame[] $enabledGames
     *
     * @return PartnerInitialInfoOddGroup[]
     */
    private function getOddGroupsByGame(array $enabledGames) : array
    {
        $oddGroups = $this->oddService->getEnabledOddGroupsForAllowedGames($enabledGames);

        $groupsByGame = [];

        foreach ($oddGroups as $oddGroup) {
            if (in_array($oddGroup->getName(), self::BAZAAR_GROUPS, true)) {
                //Bazaar odds are very specific, decided to move it in optionsData
                continue;
            }

            $groupsByGame[$oddGroup->getGameId()][] = new PartnerInitialInfoOddGroup($oddGroup);
        }

        return $groupsByGame;
    }

    /**
     * @param Partner $partner
     *
     * @return array
     */
    private function getOddIdsByGame(Partner $partner): array
    {
        $odds = $this->oddService->getOdds($partner, []);

        $oddIdsByGame = [];

        foreach ($odds as $odd) {
            $oddIdsByGame[$odd->getGame()->getId()][] = $odd->getId();
        }

        return $oddIdsByGame;
    }

    /**
     * @param Player $player
     *
     * @return int[]
     */
    private function getPlayerFavoriteOddsIds(Player $player): array
    {
        $favoriteOdds = $player->getFavoriteOdds();

        $favoriteOddsIds = [];
        foreach ($favoriteOdds as $favoriteOdd) {
            $favoriteOddsIds[] = $favoriteOdd->getOdd()->getId();
        }

        return $favoriteOddsIds;
    }
}
