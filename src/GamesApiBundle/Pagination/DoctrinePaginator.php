<?php

namespace GamesApiBundle\Pagination;

use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class Paginator
 *
 * @see https://github.com/symfony/demo/blob/master/src/Pagination/Paginator.php
 * @see https://symfony.com/doc/current/best_practices.html#use-constants-to-define-options-that-rarely-change
 */
final class DoctrinePaginator implements PaginatorInterface
{
    public const PAGE_SIZE = 10;

    private DoctrineQueryBuilder $queryBuilder;
    private int $currentPage;
    private int $pageSize;
    private iterable $results;
    private int $numResults;

    /**
     * Paginator constructor.
     *
     * @param DoctrineQueryBuilder $queryBuilder
     * @param int $pageSize
     */
    public function __construct(DoctrineQueryBuilder $queryBuilder, int $pageSize = self::PAGE_SIZE)
    {
        $this->queryBuilder = $queryBuilder;
        $this->pageSize = $pageSize;
    }

    /**
     * @param int $page
     *
     * @return $this
     * @throws \Exception
     */
    public function paginate(int $page = 1): self
    {
        $this->currentPage = max(1, $page);
        $firstResult = ($this->currentPage - 1) * $this->pageSize;

        $query = $this->queryBuilder
            ->setFirstResult($firstResult)
            ->setMaxResults($this->pageSize)
            ->getQuery();

        if (count($this->queryBuilder->getDQLPart('join')) === 0) {
            $query->setHint(CountWalker::HINT_DISTINCT, false);
        }

        $paginator = new Paginator($query, true);

        $useOutputWalkers = count($this->queryBuilder->getDQLPart('having') ?: []) > 0;
        $paginator->setUseOutputWalkers($useOutputWalkers);

        $this->results = $paginator->getIterator();
        $this->numResults = $paginator->count();

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @return int
     */
    public function getLastPage(): int
    {
        return (int) ceil($this->numResults / $this->pageSize);
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @return bool
     */
    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    /**
     * @return int
     */
    public function getPreviousPage(): int
    {
        return max(1, $this->currentPage - 1);
    }

    /**
     * @return bool
     */
    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->getLastPage();
    }

    /**
     * @return int
     */
    public function getNextPage(): int
    {
        return min($this->getLastPage(), $this->currentPage + 1);
    }

    /**
     * @return bool
     */
    public function hasToPaginate(): bool
    {
        return $this->numResults > $this->pageSize;
    }

    /**
     * @return int
     */
    public function getNumResults(): int
    {
        return $this->numResults;
    }

    /**
     * @return iterable
     */
    public function getResults(): iterable
    {
        return $this->results;
    }
}