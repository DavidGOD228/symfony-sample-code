<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Initial\Component;

use Acme\SymfonyDb\Entity\PartnerEnabledGame;
use Acme\SymfonyDb\Type\StreamPlatformType;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoGameV3;
use GamesApiBundle\Service\GameStateService;
use StreamBundle\Service\StreamProvider;

/**
 * Class InitialGamesBuilderV3
 */
final class InitialGamesBuilderV3
{
    private GameStateService $gameStateService;
    private StreamProvider $streamProvider;

    /**
     * @param GameStateService $gameStateService
     * @param StreamProvider $streamProvider
     */
    public function __construct(
        GameStateService $gameStateService,
        StreamProvider $streamProvider
    )
    {
        $this->gameStateService = $gameStateService;
        $this->streamProvider = $streamProvider;
    }

    /**
     * @param PartnerEnabledGame[] $enabledGames
     * @param string $ip
     *
     * @return PartnerInitialInfoGameV3[] array<int, PartnerInitialInfoGameV3>
     */
    public function build(
        array $enabledGames,
        string $ip
    ): array
    {
        $games = [];

        foreach ($enabledGames as $enabledGame) {
            $game = $enabledGame->getGame();
            $gameId = $game->getId();

            $games[$gameId] = new PartnerInitialInfoGameV3(
                $gameId,
                $this->gameStateService->getGameState($gameId),
                $this->streamProvider->getStreamDelay($enabledGame, StreamPlatformType::IFRAME, $ip),
                $this->streamProvider->getStreamStartDelay($gameId),
            );
        }

        return $games;
    }
}
