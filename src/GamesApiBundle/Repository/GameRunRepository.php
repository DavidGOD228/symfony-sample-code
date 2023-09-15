<?php

namespace GamesApiBundle\Repository;

use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameRun;
use CoreBundle\Repository\AbstractBaseRepository;
use DateTimeInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Class GameRunRepository
 */
class GameRunRepository extends AbstractBaseRepository
{
    protected const ENTITY_CLASS = GameRun::class;

    /**
     * @param Game[] $games
     * @param DateTimeInterface $timeFrom
     * @param DateTimeInterface $timeTo
     *
     * @return QueryBuilder
     */
    public function getResultedGameRunsQuery(
        array $games,
        DateTimeInterface $timeFrom,
        DateTimeInterface $timeTo
    ): QueryBuilder
    {
        $query = $this->createQueryBuilder('gr')
            ->where('gr.time >= :timeFrom')
            ->andWhere('gr.time < :timeTo')
            ->andWhere('gr.isReturned = :isReturned OR gr.resultsEntered = :resultsEntered')
            ->andWhere('gr.game IN (:games)')
            ->setParameter('isReturned', true)
            ->setParameter('resultsEntered', true)
            ->setParameter('games', $games)
            ->setParameter('timeFrom', $timeFrom)
            ->setParameter('timeTo', $timeTo)
            ->orderBy('gr.time', 'DESC');

        return $query;
    }

    /**
     * @param string $runCode
     * @param Game[] $games
     *
     * @return GameRun|null
     *
     * @noinspection PhpDocMissingThrowsInspection - run code uniq in DB, no exception would be.
     */
    public function getResultedGameRun(string $runCode, array $games): ?GameRun
    {
        $query = $this->createQueryBuilder('gr')
            ->where('gr.code = :code')
            ->andWhere('gr.game IN (:games)')
            ->andWhere('gr.isReturned = :isReturned OR gr.resultsEntered = :resultsEntered')
            ->setParameter('isReturned', true)
            ->setParameter('resultsEntered', true)
            ->setParameter('games', $games)
            ->setParameter('code', $runCode)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $query;
    }
}
