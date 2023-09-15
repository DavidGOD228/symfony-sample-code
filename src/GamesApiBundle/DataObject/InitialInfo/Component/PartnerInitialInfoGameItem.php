<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\InitialInfo\Component;

use Acme\SymfonyDb\Entity\GameItem;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class PartnerInitialInfoGameItem
 *
 * @psalm-immutable
 */
final class PartnerInitialInfoGameItem
{
    /** @SerializedName("id") */
    public int $id;
    /** @SerializedName("number") */
    public int $number;
    /** @SerializedName("color") */
    public ?string $color;

    /**
     * @param GameItem $gameItem
     */
    public function __construct(GameItem $gameItem)
    {
        $this->id = $gameItem->getId();
        $this->number = $gameItem->getNumber();
        $this->color = $gameItem->getColor();
    }
}
