<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\InitialInfo\Component;

use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyDb\Entity\OddGroup;
use Acme\SymfonyDb\Type\OddClassType;
use Acme\SymfonyDb\Type\OddGroupNameType;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class PartnerInitialInfoMatka
 *
 * @psalm-readonly
 * @deprecated frontend is migrating to initial API v3 - avoid modifying any initial API v2 code if possible
 */
final class PartnerInitialInfoMatka
{
    private const FIELD_GROUP_EXACT_PANA = 'exactPana';
    private const FIELD_GROUP_SINGLE_PANA = 'singlePana';
    private const FIELD_GROUP_DOUBLE_PANA = 'doublePana';
    private const FIELD_GROUP_TRIPLE_PANA = 'triplePana';

    public const FIELD_GROUP_BAZAAR_MAIN = 'bazaarMain';
    public const FIELD_GROUP_BAZAAR_OPEN = 'bazaarOpen';
    public const FIELD_GROUP_BAZAAR_CLOSE = 'bazaarClose';

    private const FIELD_ODD_EXACT_SINGLE_PANA = 'single';
    private const FIELD_ODD_EXACT_DOUBLE_PANA = 'double';
    private const FIELD_ODD_EXACT_TRIPLE_PANA = 'triple';

    private const FIELD_BAZAAR_ODDS_MAIN = 'mainOptionsIds';
    private const FIELD_BAZAAR_ODDS_OPEN = 'openOptionsIds';
    private const FIELD_BAZAAR_ODDS_CLOSE = 'closeOptionsIds';

    /** @SerializedName("groupsIds") */
    public array $groupsIds;
    /** @SerializedName("optionsIds") */
    public array $optionsIds;

    /**
     * @var array{mainOptionsIds: int[], openOptionsIds: int[], closeOptionsIds: int[]}
     *
     * @SerializedName("bazaar")
     */
    public array $bazaar = [];

    /**
     * PartnerInitialInfoMatka constructor.
     *
     * @param Odd[] $odds
     * @param OddGroup[] $oddGroups
     */
    public function __construct(array $odds, array $oddGroups)
    {
        foreach ($oddGroups as $oddGroup) {
            if ($oddGroup->getName() === OddGroupNameType::EXACT_PANA) {
                $this->groupsIds[self::FIELD_GROUP_EXACT_PANA] = $oddGroup->getId();
            }

            if ($oddGroup->getName() === OddGroupNameType::SINGLE_PANA) {
                $this->groupsIds[self::FIELD_GROUP_SINGLE_PANA] = $oddGroup->getId();
            }

            if ($oddGroup->getName() === OddGroupNameType::DOUBLE_PANA) {
                $this->groupsIds[self::FIELD_GROUP_DOUBLE_PANA] = $oddGroup->getId();
            }

            if ($oddGroup->getName() === OddGroupNameType::TRIPLE_PANA) {
                $this->groupsIds[self::FIELD_GROUP_TRIPLE_PANA] = $oddGroup->getId();
            }

            $this->setBazaarOptions($oddGroup);
        }

        foreach ($odds as $odd) {
            if ($odd->getClass() === OddClassType::EXACT_SINGLE_PANA) {
                $this->optionsIds[self::FIELD_ODD_EXACT_SINGLE_PANA] = $odd->getId();
            }

            if ($odd->getClass() === OddClassType::EXACT_DOUBLE_PANA) {
                $this->optionsIds[self::FIELD_ODD_EXACT_DOUBLE_PANA] = $odd->getId();
            }

            if ($odd->getClass() === OddClassType::EXACT_TRIPLE_PANA) {
                $this->optionsIds[self::FIELD_ODD_EXACT_TRIPLE_PANA] = $odd->getId();
            }
        }
    }

    /**
     * @param OddGroup $oddGroup
     */
    private function setBazaarOptions(OddGroup $oddGroup): void
    {
        if ($oddGroup->getName() === OddGroupNameType::BAZAAR_MAIN_BETS) {
            $this->groupsIds[self::FIELD_GROUP_BAZAAR_MAIN] = $oddGroup->getId();
        }

        if ($oddGroup->getName() === OddGroupNameType::BAZAAR_MAIN_BETS) {
            $groupingOdds = $oddGroup->getGroupingOdds();
            foreach ($groupingOdds as $groupingOdd) {
                $this->bazaar[self::FIELD_BAZAAR_ODDS_MAIN][] = $groupingOdd->getOdd()->getId();
            }
        }

        if ($oddGroup->getName() === OddGroupNameType::BAZAAR_OPEN) {
            $this->groupsIds[self::FIELD_GROUP_BAZAAR_OPEN] = $oddGroup->getId();

            $groupingOdds = $oddGroup->getGroupingOdds();
            foreach ($groupingOdds as $groupingOdd) {
                $this->bazaar[self::FIELD_BAZAAR_ODDS_OPEN][] = $groupingOdd->getOdd()->getId();
            }
        }

        if ($oddGroup->getName() === OddGroupNameType::BAZAAR_CLOSE) {
            $this->groupsIds[self::FIELD_GROUP_BAZAAR_CLOSE] = $oddGroup->getId();

            $groupingOdds = $oddGroup->getGroupingOdds();
            foreach ($groupingOdds as $groupingOdd) {
                $this->bazaar[self::FIELD_BAZAAR_ODDS_CLOSE][] = $groupingOdd->getOdd()->getId();
            }
        }
    }
}
