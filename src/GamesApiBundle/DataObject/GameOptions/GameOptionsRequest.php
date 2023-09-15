<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\GameOptions;

/**
 * Class GameOptionsRequest
 *
 * @psalm-immutable
 */
final class GameOptionsRequest
{
    public const FIELD_KEY = 'game_ids';

    /** @var array<string> */
    public array $gameIds;

    /**
     * GameSettingsRequest constructor.
     *
     * @param array $input
     */
    public function __construct(array $input)
    {
        $this->gameIds = $input[self::FIELD_KEY] ?? [];
    }

    /**
     * @return array<string>
     */
    public function getGameIds(): array
    {
        return $this->gameIds;
    }
}