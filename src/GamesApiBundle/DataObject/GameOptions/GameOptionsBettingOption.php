<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\GameOptions;

use Acme\SymfonyDb\Entity\Odd;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

/**
 * Class GameOptionsBettingOption
 * @psalm-readonly
 */
final class GameOptionsBettingOption
{
    /** @SerializedName("id") */
    public int $id;
    /** @SerializedName("optionClass") */
    public string $optionClass;
    /** @SerializedName("itemsCount") */
    public int $itemsCount;
    /**
     * @SerializedName("suits")
     * @var array<string>
     */
    public array $suits;

    /**
     * BettingOption constructor.
     *
     * @param Odd $odd
     */
    public function __construct(Odd $odd)
    {
        $this->id = $odd->getId();
        $this->optionClass = $odd->getClass();
        $this->itemsCount = $odd->getItemsCount();
        $this->suits = $this->getSuitsFromOdd($odd);
    }

    /**
     * @param Odd $odd
     *
     * @return array<string>
     * @throws NotEncodableValueException
     */
    private function getSuitsFromOdd(Odd $odd) : array
    {
        $suitsJson = $odd->getSuits();

        if (!$suitsJson) {
            return [];
        }
        $decoder = (new JsonDecode([JsonDecode::ASSOCIATIVE => true]));
        $suits = $decoder->decode($suitsJson, JsonEncoder::FORMAT);

        return $suits;
    }
}
