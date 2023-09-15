<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\GameRunResults;

use Carbon\CarbonImmutable;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ResultsParams
 */
final class ResultsParams
{
    public const FIELD_TIMEZONE = 'timezone';
    public const FIELD_DATE = 'date';
    public const FIELD_PAGE = 'page';
    public const FIELD_GAME_ID = 'game_id';
    public const FIELD_GAMES_IDS = 'games_ids';

    private \DateTimeImmutable $date;
    private int $timezoneOffset;
    private int $page;
    private ?int $gameId;
    private ?array $gamesIds;

    /**
     * ResultsParams constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->date = new CarbonImmutable($request->get(self::FIELD_DATE) ?? 'today');
        $this->timezoneOffset = (int) $request->get(self::FIELD_TIMEZONE);
        $this->page = (int) $request->get(self::FIELD_PAGE);
        $this->gameId = (int) $request->get(self::FIELD_GAME_ID);
        $this->gamesIds = $request->get(self::FIELD_GAMES_IDS)
            ? explode(',', $request->get(self::FIELD_GAMES_IDS))
            : null;
    }

    /**
     * @return CarbonImmutable
     */
    public function getDate(): CarbonImmutable
    {
        return $this->date;
    }

    /**
     * @return int
     */
    public function getTimezoneOffset(): int
    {
        return $this->timezoneOffset;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int|null
     */
    public function getGameId(): ?int
    {
        return $this->gameId;
    }

    /**
     * @return array|null
     */
    public function getGamesIds(): ?array
    {
        return $this->gamesIds;
    }
}