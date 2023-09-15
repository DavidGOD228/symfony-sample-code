<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\GameOptions;

use Acme\SymfonyDb\Entity\OddGroup;
use Acme\SymfonyDb\Type\OddGroupNameType;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class GameOptionsData
 *
 * @psalm-immutable
 */
final class GameOptionsDataMatka
{
    private const FIELD_GROUP_EXACT_PANA = 'exactPana';
    private const FIELD_GROUP_SINGLE_PANA = 'singlePana';
    private const FIELD_GROUP_DOUBLE_PANA = 'doublePana';
    private const FIELD_GROUP_TRIPLE_PANA = 'triplePana';

    /**
     * @SerializedName("groupIds")
     * @var array<string,int>
     */
    public array $groupIds;

    /**
     * Constructor.
     *
     * @param iterable<OddGroup> $matkaOddGroups
     */
    public function __construct(iterable $matkaOddGroups)
    {
        $this->groupIds = $this->extractGroupIds($matkaOddGroups);
    }

    /**
     * @param iterable<OddGroup> $matkaOddGroups
     *
     * @return array
     */
    private function extractGroupIds(iterable $matkaOddGroups): array
    {
        $groupIds = [];
        foreach ($matkaOddGroups as $oddGroup) {
            if ($oddGroup->getName() === OddGroupNameType::EXACT_PANA) {
                $groupIds[self::FIELD_GROUP_EXACT_PANA] = $oddGroup->getId();
            }

            if ($oddGroup->getName() === OddGroupNameType::SINGLE_PANA) {
                $groupIds[self::FIELD_GROUP_SINGLE_PANA] = $oddGroup->getId();
            }

            if ($oddGroup->getName() === OddGroupNameType::DOUBLE_PANA) {
                $groupIds[self::FIELD_GROUP_DOUBLE_PANA] = $oddGroup->getId();
            }

            if ($oddGroup->getName() === OddGroupNameType::TRIPLE_PANA) {
                $groupIds[self::FIELD_GROUP_TRIPLE_PANA] = $oddGroup->getId();
            }
        }

        return $groupIds;
    }
}
