<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Pagination;

use GamesApiBundle\Pagination\ArrayPaginator;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class ArrayPaginatorCest
 */
final class ArrayPaginatorCest extends AbstractUnitTest
{
    private const SOME_LIST = [1, 2, 3, 5, 6, 7, 8, 9, 10, 11];

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testPaginationFirstPage(UnitTester $I): void
    {
        // Checking games length to use correct test cases.
        $I->assertCount(10, self::SOME_LIST);

        $paginator = new ArrayPaginator(self::SOME_LIST, 2);
        $I->assertEquals(2, $paginator->getPageSize());
        $paginator->paginate(1);

        $I->assertEquals(1, $paginator->getCurrentPage());
        $I->assertEquals(5, $paginator->getLastPage());
        $I->assertEquals(2, $paginator->getNextPage());
        $I->assertEquals(10, $paginator->getNumResults());
        $I->assertFalse($paginator->hasPreviousPage());
        $I->assertTrue($paginator->hasToPaginate());
        $I->assertTrue($paginator->hasNextPage());

        $onPage = $paginator->getResults();
        $I->assertEquals(1, $onPage[0]);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testPaginationLastPage(UnitTester $I): void
    {
        // Checking games length to use correct test cases.
        $I->assertCount(10, self::SOME_LIST);

        $paginator = new ArrayPaginator(self::SOME_LIST, 2);
        $I->assertEquals(2, $paginator->getPageSize());

        $paginator->paginate(5);
        $I->assertEquals(5, $paginator->getCurrentPage());
        $I->assertEquals(5, $paginator->getNextPage());
        $I->assertEquals(4, $paginator->getPreviousPage());
        $I->assertTrue($paginator->hasPreviousPage());
        $I->assertFalse($paginator->hasNextPage());

        $onPage = $paginator->getResults();
        $I->assertEquals(10, $onPage[0]);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testPaginationSinglePage(UnitTester $I): void
    {
        // Checking games length to use correct test cases.
        $I->assertCount(10, self::SOME_LIST);

        $paginator = new ArrayPaginator(self::SOME_LIST);
        $I->assertEquals(10, $paginator->getPageSize());

        $paginator->paginate(1);
        $I->assertEquals(1, $paginator->getCurrentPage());
        $I->assertEquals(1, $paginator->getNextPage());
        $I->assertEquals(1, $paginator->getPreviousPage());
        $I->assertFalse($paginator->hasPreviousPage());
        $I->assertFalse($paginator->hasNextPage());

        $onPage = $paginator->getResults();
        $I->assertEquals(1, $onPage[0]);
    }
}