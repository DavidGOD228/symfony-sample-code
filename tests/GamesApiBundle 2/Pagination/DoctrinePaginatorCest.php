<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Pagination;

use Acme\SymfonyDb\Entity\Game;
use Doctrine\ORM\QueryBuilder;
use GamesApiBundle\Pagination\DoctrinePaginator;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class PaginatorCest
 */
final class DoctrinePaginatorCest extends AbstractUnitTest
{
    private const GAME_IDS = [1, 2, 3, 5, 6, 7, 8, 9, 10, 11];

    /**
     * {@inheritDoc}
     */
    protected function setUpFixtures(): void
    {
        parent::setUpFixtures();

        $this->fixtureBoostrapper->addGames(self::GAME_IDS);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testPaginationFirstPage(UnitTester $I): void
    {
        // Checking games length to use correct test cases.
        $I->assertCount(10, self::GAME_IDS);

        $query = $this->getQueryBuild();

        $paginator = new DoctrinePaginator($query, 2);
        $I->assertEquals(2, $paginator->getPageSize());
        $paginator->paginate(1);

        $I->assertEquals(1, $paginator->getCurrentPage());
        $I->assertEquals(5, $paginator->getLastPage());
        $I->assertEquals(2, $paginator->getNextPage());
        $I->assertEquals(10, $paginator->getNumResults());
        $I->assertFalse($paginator->hasPreviousPage());
        $I->assertTrue($paginator->hasToPaginate());
        $I->assertTrue($paginator->hasNextPage());

        $gamesOnPage = $paginator->getResults();
        $I->assertEquals(1, $gamesOnPage[0]->getId());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testPaginationLastPage(UnitTester $I): void
    {
        // Checking games length to use correct test cases.
        $I->assertCount(10, self::GAME_IDS);

        $query = $this->getQueryBuild();

        $paginator = new DoctrinePaginator($query, 2);
        $I->assertEquals(2, $paginator->getPageSize());

        $paginator->paginate(5);
        $I->assertEquals(5, $paginator->getCurrentPage());
        $I->assertEquals(5, $paginator->getNextPage());
        $I->assertEquals(4, $paginator->getPreviousPage());
        $I->assertTrue($paginator->hasPreviousPage());
        $I->assertFalse($paginator->hasNextPage());

        $gamesOnPage = $paginator->getResults();
        $I->assertEquals(10, $gamesOnPage[0]->getId());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testPaginationSinglePage(UnitTester $I): void
    {
        // Checking games length to use correct test cases.
        $I->assertCount(10, self::GAME_IDS);

        $query = $this->getQueryBuild();

        $paginator = new DoctrinePaginator($query);
        $I->assertEquals(10, $paginator->getPageSize());

        $paginator->paginate(1);
        $I->assertEquals(1, $paginator->getCurrentPage());
        $I->assertEquals(1, $paginator->getNextPage());
        $I->assertEquals(1, $paginator->getPreviousPage());
        $I->assertFalse($paginator->hasPreviousPage());
        $I->assertFalse($paginator->hasNextPage());

        $gamesOnPage = $paginator->getResults();
        $I->assertEquals(1, $gamesOnPage[0]->getId());
    }

    /**
     * @return QueryBuilder
     */
    private function getQueryBuild(): QueryBuilder
    {
        $query = $this->getRepositoryProvider()
                      ->getSlaveEntityManager()
                      ->getRepository(Game::class)
                      ->createQueryBuilder('g');
        return $query;
    }
}
