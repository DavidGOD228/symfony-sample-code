<?php

namespace GamesApiBundle\Service\Statistics\Repository;

use Acme\SymfonyDb\Entity\RpsRunRoundCard;
use Acme\SymfonyDb\Type\RpsDealtToType;
use CoreBundle\Repository\AbstractBaseRepository;

/**
 * Class RPSStatisticsRepository
 */
class RPSStatisticsRepository extends AbstractBaseRepository
{
    protected const ENTITY_CLASS = RpsRunRoundCard::class;

    /**
     * @param int $numberOfRuns
     *
     * @return string[]
     */
    public function getLastZone1Cards(int $numberOfRuns): array
    {
        return $this->getLastCards($numberOfRuns, RpsDealtToType::ZONE_1);
    }

    /**
     * @param int $numberOfRuns
     *
     * @return string[]
     */
    public function getLastZone2Cards(int $numberOfRuns): array
    {
        return $this->getLastCards($numberOfRuns, RpsDealtToType::ZONE_2);
    }

    /**
     * @param int $numberOfRuns
     * @param string $zone
     *
     * @return string[]
     */
    private function getLastCards(int $numberOfRuns, string $zone): array
    {
        $result = $this->entityManager->createQueryBuilder()
            ->select('card')
            ->from(RpsRunRoundCard::class, 'card')
            ->join('card.gameRun', 'gameRun')
            ->where('card.dealtTo = :dealtTo')
            ->andWhere('gameRun.isReturned = :isReturned')
            ->orderBy('card.gameRun', 'DESC')
            ->setMaxResults($numberOfRuns)
            ->setParameter('dealtTo', $zone)
            ->setParameter('isReturned', false)
            ->getQuery()->getResult();

        return array_map(static function (RpsRunRoundCard $card) {
            return $card->getCard();
        }, $result);
    }
}
