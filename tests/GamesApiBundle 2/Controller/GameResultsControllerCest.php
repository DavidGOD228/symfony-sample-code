<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Controller;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\GameRunResultItem;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyRequest\Request;
use Carbon\CarbonImmutable;
use CoreBundle\Exception\ValidationException;
use Doctrine\ORM\Tools\ToolsException;
use Eastwest\Json\Json;
use GamesApiBundle\Controller\GameResultsController;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\GamesApiBundle\Fixture\GameRunResults\ListLucky5Lucky7Fixture;
use SymfonyTests\UnitTester;

/**
 * Class GameResultsControllerCest
 */
final class GameResultsControllerCest extends AbstractUnitTest
{
    protected array $tables = [
        GameRunResultItem::class,
    ];

    protected array $fixtures = [
        ListLucky5Lucky7Fixture::class,
    ];

    private GameResultsController $controller;

    /**
     * @inheritDoc
     */
    protected function setUpFixtures(): void
    {
        parent::setUpFixtures();
        CarbonImmutable::setTestNow('2020-01-01');
        $gameIds = [GameDefinition::LUCKY_7, GameDefinition::LUCKY_5];
        $this->fixtureBoostrapper->addRunRounds($gameIds);
        $this->fixtureBoostrapper->addGameItemFixture($gameIds, true);
        $this->fixtureBoostrapper->addPartners(1, true);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->controller = $I->getContainer()->get(GameResultsController::class);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testResults(UnitTester $I): void
    {
        /** @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        $request = new Request(
            ['timezone' => '0', 'date' => '2019-12-30', 'page' => '1'],
            [],
            [],
            [],
            [],
            ['SERVER_NAME' => 'mono.Acme.local']
        );

        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../Response/game-results.json',
            $this->controller->resultsAction($request, $partner->getApiCode())->getContent()
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testResultsFilteredByGame(UnitTester $I): void
    {
        /** @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        $request = new Request(['timezone' => '0', 'date' => '2019-12-30', 'page' => '1', 'game_id' => 1]);

        $response = $this->controller->resultsAction($request, $partner->getApiCode())->getContent();
        $responseData = Json::decode($response);
        $I->assertCount(1, $responseData->runs);
        $I->assertEquals(
            GameDefinition::LUCKY_7,
            $responseData->runs[0]->gameId
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testResultsFilteredByGames(UnitTester $I): void
    {
        /** @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        $request = new Request([
            'timezone' => '0',
            'date' => '2019-12-30',
            'page' => '1',
            'games_ids' => implode(',', [ GameDefinition::LUCKY_5 ]),
        ]);
        $response = $this->controller->resultsAction($request, $partner->getApiCode())->getContent();
        $responseData = Json::decode($response);
        $I->assertCount(1, $responseData->runs);
        $I->assertEquals(
            GameDefinition::LUCKY_5,
            $responseData->runs[0]->gameId
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testResultsFilteredByDate(UnitTester $I): void
    {
        /** @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        $request = new Request(['timezone' => '0', 'date' => '2019-12-29', 'page' => '1']);

        $response = $this->controller->resultsAction($request, $partner->getApiCode())->getContent();
        $responseData = Json::decode($response);
        $I->assertCount(0, $responseData->runs);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testResult(UnitTester $I): void
    {
        /** @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        $request = new Request(['timezone' => '0'], [], [], [], [], ['SERVER_NAME' => 'mono.Acme.local']);

        $response = $this->controller->gameRunResultAction(
            $request,
            $partner->getApiCode(),
            '1DrawCode'
        );

        $actualResponse = Json::decode(
            $response->getContent()
        );
        $expectedResponse = Json::decode(
            file_get_contents(__DIR__ . '/../Response/game-result.json')
        );

        $I->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testResultNotFound(UnitTester $I): void
    {
        /** @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        $request = new Request([], [], [], [], [], ['SERVER_NAME' => 'mono.Acme.local']);

        $response = $this->controller->gameRunResultAction(
            $request,
            $partner->getApiCode(),
            'NotFound'
        );

        $actualResponse = Json::decode(
            $response->getContent()
        );

        $I->assertNull($actualResponse);
    }
}
