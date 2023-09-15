<?php

declare(strict_types = 1);

namespace GamesApiBundle\Pagination;

/**
 * Class ArrayPaginator
 */
final class ArrayPaginator implements PaginatorInterface
{
    public const PAGE_SIZE = 10;

    private int $currentPage;
    private int $pageSize;
    private iterable $dataset;
    private iterable $results;
    private int $numResults;

    /**
     * ArrayPaginator constructor.
     *
     * @param array $records
     * @param int $pageSize
     */
    public function __construct(array $records, int $pageSize = self::PAGE_SIZE)
    {
        $this->dataset = $records;
        $this->pageSize = $pageSize;
    }

    /**
     * @param int $page
     *
     * @return $this
     */
    public function paginate(int $page = 1): self
    {
        $this->currentPage = max(1, $page);
        $this->results = array_slice(
            $this->dataset,
            ($this->currentPage - 1) * $this->pageSize,
            $this->pageSize,
            false
        );
        $this->numResults = count($this->dataset);

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