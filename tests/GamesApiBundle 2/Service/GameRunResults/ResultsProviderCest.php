<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\GameRunResults;

use Codeception\Example;
use ReflectionClass;
use ReflectionException;
use SymfonyTests\UnitTester;
use Acme\SymfonyRequest\Request;
use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\Partner;
use Doctrine\ORM\Tools\ToolsException;
use SymfonyTests\Unit\AbstractUnitTest;
use CoreBundle\Exception\ValidationException;
use GamesApiBundle\Service\GameRunResults\ResultsProvider;
use GamesApiBundle\DataObject\GameRunResults\ResultsParams;

/**
 * Class ResultsProviderCest
 */
final class ResultsProviderCest extends AbstractUnitTest
{
    private ?ResultsProvider $resultsProvider;

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->resultsProvider = $I->getContainer()->get(ResultsProvider::class);
    }

    /**
     * {@inheritDoc}
     */
    protected function setUpFixtures(): void
    {
        parent::setUpFixtures();
        $this->fixtureBoostrapper->addGames([
            GameDefinition::POKER,
            GameDefinition::BACCARAT,
            GameDefinition::LUCKY_6,
            GameDefinition::LUCKY_7,
        ]);
        $this->fixtureBoostrapper->addPartners(1, true);
    }

    /**
     * @param UnitTester $I
     * @param Example $example
     *
     * @return void
     *
     * @dataProvider notAllowedGamesIdsExampleProvider
     */
    public function testGetGamesMustReturnExceptionOnInvalidGame(UnitTester $I, Example $example): void
    {
        /** @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        $request = new Request([
            'timezone' => '0',
            'date' => '2019-12-30',
            'page' => '1',
            'games_ids' => $example['gamesIds'],
        ]);
        $params = new ResultsParams($request);
        $I->expectThrowable(
            new ValidationException('GAME_NOT_ALLOWED'),
            function () use ($partner, $params) {
                $this->invokeMethod($this->resultsProvider, 'getGames', [ $partner, $params ]);
            }
        );
    }

    /**
     * @return iterable
     */
    private function notAllowedGamesIdsExampleProvider(): iterable
    {
        yield [
            'gamesIds' => implode(',', [ 555 ]),
        ];

        yield [
            'gamesIds' => implode(',', [ GameDefinition::MATKA ]),
        ];

        yield [
            'gamesIds' => implode(',', [ GameDefinition::LUCKY_5, GameDefinition::LUCKY_7, GameDefinition::LUCKY_6 ]),
        ];
    }

    /**
     * @param UnitTester $I
     * @param Example $example
     *
     * @return void
     *
     * @dataProvider allowedGamesIdsExampleProvider
     * @throws ReflectionException
     */
    public function testGetGamesMustReturnValidGames(UnitTester $I, Example $example): void
    {
        /** @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        $request = new Request([
            'timezone' => '0',
            'date' => '2019-12-30',
            'page' => '1',
            'games_ids' => $example['gamesIds'],
        ]);
        $params = new ResultsParams($request);
        $result = $this->invokeMethod($this->resultsProvider, 'getGames', [ $partner, $params ]);
        $I->assertCount($example['expectedCount'], $result);
    }

    /**
     * @return iterable
     */
    private function allowedGamesIdsExampleProvider(): iterable
    {
        yield [
            'gamesIds' => implode(',', [ GameDefinition::LUCKY_7 ]),
            'expectedCount' => 1,
        ];

        yield [
            'gamesIds' => implode(',', [ GameDefinition::POKER, GameDefinition::LUCKY_7, GameDefinition::LUCKY_6 ]),
            'expectedCount' => 3,
        ];
    }

    /**
     * @param UnitTester $I
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testGameIdMustHaveHigherPriorityThanGamesIds(UnitTester $I): void
    {
        /** @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        $request = new Request([
            'timezone' => '0',
            'date' => '2019-12-30',
            'page' => '1',
            'game_id' => GameDefinition::POKER,
            'games_ids' => implode(',', [ GameDefinition::POKER, GameDefinition::LUCKY_7 ])
        ]);
        $params = new ResultsParams($request);
        $result = $this->invokeMethod($this->resultsProvider, 'getGames', [ $partner, $params ]);
        $I->assertCount(1, $result);
    }

    /**
     * @param ResultsProvider $object Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     * @throws ReflectionException
     */
    private function invokeMethod(ResultsProvider &$object, string $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
