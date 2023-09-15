<?php

declare(strict_types = 1);

namespace GamesApiBundle\Pagination;

/**
 * Interface PaginatorInterface
 */
interface PaginatorInterface
{
    /**
     * @param int $page
     *
     * @return $this
     * @throws \Exception
     */
    public function paginate(int $page = 1): self;

    /**
     * @return int
     */
    public function getCurrentPage(): int;

    /**
     * @return int
     */
    public function getLastPage(): int;

    /**
     * @return int
     */
    public function getPageSize(): int;

    /**
     * @return bool
     */
    public function hasPreviousPage(): bool;

    /**
     * @return int
     */
    public function getPreviousPage(): int;

    /**
     * @return bool
     */
    public function hasNextPage(): bool;

    /**
     * @return int
     */
    public function getNextPage(): int;

    /**
     * @return bool
     */
    public function hasToPaginate(): bool;

    /**
     * @return int
     */
    public function getNumResults(): int;

    /**
     * @return iterable
     */
    public function getResults(): iterable;
}