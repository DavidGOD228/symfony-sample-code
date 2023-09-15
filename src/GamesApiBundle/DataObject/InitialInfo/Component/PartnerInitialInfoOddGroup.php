<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\InitialInfo\Component;

use Acme\SymfonyDb\Entity\GroupingOdd;
use Acme\SymfonyDb\Entity\OddGroup;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * Class PartnerInitialInfoOddGroup
 *
 * @psalm-readonly
 */
final class PartnerInitialInfoOddGroup
{
    /** @SerializedName("id") */
    public int $id;
    /**
     * @var int[]
     * @SerializedName("ids")
     */
    public array $ids;

    /**
     * PartnerInitialInfoOddGroup constructor.
     * @param OddGroup $oddGroup
     */
    public function __construct(OddGroup $oddGroup)
    {
        $this->id = $oddGroup->getId();

        $ids = [];

        /** @var GroupingOdd $groupingOdd */
        foreach ($oddGroup->getGroupingOdds() as $groupingOdd) {
            $ids[] = $groupingOdd->getOdd()->getId();
        }
        $this->ids = $ids;
    }
}
