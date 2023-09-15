<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults;

use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\Partner;
use CoreBundle\Exception\ValidationException;
use CoreBundle\Service\GameService;
use CoreBundle\Service\RepositoryProviderInterface;
use Acme\Time\Time;
use GamesApiBundle\DataObject\GameRunResults\ResultDTO;
use GamesApiBundle\DataObject\GameRunResults\ResultsParams;
use GamesApiBundle\Pagination\DoctrinePaginator;
use GamesApiBundle\Repository\GameRunRepository;

/**
 * Class ResultsProvider
 */
final class ResultsProvider
{
    private GameService $gameService;
    private ResultsBuilder $builder;
    private GameRunRepository $gameRunRepository;

    /**
     * ResultsProvider constructor.
     *
     * @param GameService $gameService
     * @param ResultsBuilder $formatter
     * @param RepositoryProviderInterface $repository
     */
    public function __construct(
        GameService $gameService,
        ResultsBuilder $formatter,
        RepositoryProviderInterface $repository
    )
    {
        $this->gameService = $gameService;
        $this->builder = $formatter;
        $this->gameRunRepository = $repository->getSlaveRepository(GameRunRepository::class);
    }

    /**
     * @param ResultsParams $params
     * @param Partner $partner
     * @param string $rootDomain
     * @param int $pageSize
     *
     * @return array
     *
     * @throws ValidationException
     */
    public function get(ResultsParams $params, Partner $partner, string $rootDomain, int $pageSize): array
    {
        $userDate = Time::applyTimezone($params->getDate(), $params->getTimezoneOffset());

        $games = $this->getGames($partner, $params);

        $query = $this->gameRunRepository->getResultedGameRunsQuery(
            $games,
            $userDate,
            $userDate->modify('+1 day')
        );

        $paginator = new DoctrinePaginator($query, $pageSize);
        $paginator->paginate($params->getPage());
        /** @var GameRun[] $gameRuns */
        $gameRuns = $paginator->getResults();
        $pagesCount = $paginator->getLastPage();

        $response = [
            'pages' => $pagesCount,
            'runs' => $this->builder->build($gameRuns, $rootDomain),
        ];

        return $response;
    }

    /**
     * @param Partner $partner
     * @param string $runCode
     * @param string $rootDomain
     *
     * @return ResultDTO|null
     *
     * @throws ValidationException
     */
    public function getByGameRunCode(Partner $partner, string $runCode, string $rootDomain): ?ResultDTO
    {
        $games = $this->getGames($partner, null);
        $gameRun = $this->gameRunRepository->getResultedGameRun($runCode, $games);
        if (!$gameRun) {
            return null;
        }

        $gameRunFormatted = $this->builder->build([$gameRun], $rootDomain);

        return $gameRunFormatted[0];
    }

    /**
     * @param Partner $partner
     * @param ResultsParams|null $params
     *
     * @return Game[]
     *
     * @throws ValidationException
     */
    private function getGames(Partner $partner, ?ResultsParams $params): array
    {
        $enabledGames = [];

        if ($params) {
            if ($params->getGamesIds()) {
                foreach ($params->getGamesIds() as $gamesID) {
                    $enabledGame = $this->gameService->getEnabledGameStrict($partner, (int) $gamesID);
                    $enabledGames[] = $enabledGame;
                }
            }

            if ($params->getGameId()) {
                $enabledGame = $this->gameService->getEnabledGameStrict($partner, $params->getGameId());
                $enabledGames = [$enabledGame];
            }
        }

        if (!$enabledGames) {
            $enabledGames = $this->gameService->getPartnerEnabledGames($partner);
        }

        $games = [];
        foreach ($enabledGames as $enabledGame) {
            $games[] = $enabledGame->getGame();
        }

        return $games;
    }
}
