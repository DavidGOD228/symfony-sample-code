<?php

namespace SymfonyTests\Unit\GamesApiBundle\Controller;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\GroupingOdd;
use Acme\SymfonyDb\Entity\OddValue;
use Acme\SymfonyDb\Entity\Partner;
use CoreBundle\Exception\ValidationException;
use Doctrine\ORM\Tools\ToolsException;
use GamesApiBundle\Controller\GameOptionsController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\GamesApiBundle\Fixture\GameOptions\PartnerFixture;
use SymfonyTests\UnitTester;

/**
 * Class GameOptionsControllerCest
 */
final class GameOptionsControllerCest extends AbstractUnitTest
{
    protected array $tables
        = [
            OddValue::class,
            GroupingOdd::class,
            Partner::class,
        ];

    protected array $fixtures
        = [
            PartnerFixture::class,
        ];
    private GameOptionsController $controller;

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->controller = $I->getService(GameOptionsController::class);
    }

    /**
     * Set up fixtures
     */
    protected function setUpFixtures(): void
    {
        $games = [
            GameDefinition::LUCKY_7,
            GameDefinition::POKER,
            GameDefinition::MATKA,
        ];
        $this->fixtureBoostrapper->setGameIds($games);

        $this->fixtureBoostrapper->addGames($games, true);
        $this->fixtureBoostrapper->addOdds($games, true, 1);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testMultipleGamesWithoutDataField(UnitTester $I): void
    {
        $request = new Request(
            [
                'game_ids' => [
                    GameDefinition::LUCKY_7,
                    GameDefinition::POKER,
                ],
            ]
        );
        $response = $this->controller->gameOptionsAction($request, 'test1');

        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../Fixture/GameOptions/response-success-without-data.json',
            $response->getContent(),
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testMatkaShouldHaveGroupIdsInDataField(UnitTester $I): void
    {
        $request = new Request(['game_ids' => [GameDefinition::MATKA]]);
        $response = $this->controller->gameOptionsAction($request, 'test1');

        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../Fixture/GameOptions/response-success-with-data-matka.json',
            $response->getContent(),
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testOddsAndGameItemsShouldBeCachedOnSubsequentRequests(UnitTester $I): void
    {
        $request = new Request(['game_ids' => [GameDefinition::MATKA]]);

        $this->controller->gameOptionsAction($request, 'test1');
        $I->assertExecutedSqlContains('FROM grouping_odd_groups', 2);
        $I->assertExecutedSqlContains('FROM lottery_items', 1);
        $I->assertExecutedSqlContains('FROM odd_values', 1);

        $I->getSqlLogger()->queries = [];
        $this->controller->gameOptionsAction($request, 'test1');

        $I->assertExecutedSqlContains('FROM grouping_odd_groups', 0);
        $I->assertExecutedSqlContains('FROM lottery_items', 0);
        $I->assertExecutedSqlContains('FROM odd_values', 0);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testShouldAcceptStringGameIds(UnitTester $I): void
    {
        $response = $this->controller->gameOptionsAction(
            new Request(['game_ids' => ['1']]),
            'test1'
        );

        $I->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testShouldReturnEmptyResponseWhenNoGamesEnabled(UnitTester $I): void
    {
        $request = new Request(['game_ids' => [GameDefinition::MATKA]]);
        $response = $this->controller->gameOptionsAction($request, 'no-games-token');

        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../Fixture/GameOptions/response-success-no-games.json',
            $response->getContent(),
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testShouldThrowOnInvalidRequests(UnitTester $I): void
    {
        $I->expectThrowable(
            new ValidationException('[game_ids][0] This value should be of type numeric.'),
            fn() => $this->controller->gameOptionsAction(
                new Request(['game_ids' => ['aaa']]),
                'test1'
            )
        );

        $I->expectThrowable(
            new ValidationException('[game_ids] This value should not be blank.'),
            fn() => $this->controller->gameOptionsAction(
                new Request(['game_ids' => []]),
                'test1'
            )
        );

        $I->expectThrowable(
            new ValidationException('[game_ids] This field is missing.'),
            fn() => $this->controller->gameOptionsAction(
                new Request([]),
                'test1'
            )
        );
    }
}
