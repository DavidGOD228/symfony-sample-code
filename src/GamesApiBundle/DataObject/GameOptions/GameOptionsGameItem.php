<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\GameOptions;

use Acme\SymfonyDb\Entity\GameItem;

/**
 * Class GameOptionsGameItem
 *
 * @psalm-immutable
 */
final class GameOptionsGameItem
{
    public int $id;
    public int $type;
    public int $number;
    public ?string $color;

    /**
     * GameItem constructor.
     *
     * @param GameItem $gameItem
     * @param int $type
     */
    public function __construct(GameItem $gameItem, int $type)
    {
        $this->id = $gameItem->getId();
        $this->number = $gameItem->getNumber();
        $this->color = $gameItem->getColor();
        $this->type = $type;
    }
}
