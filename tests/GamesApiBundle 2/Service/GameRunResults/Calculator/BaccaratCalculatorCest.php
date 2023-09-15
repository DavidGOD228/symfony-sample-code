<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\GameRunResults\Calculator;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\BaccaratCard;
use Acme\SymfonyDb\Entity\BaccaratRunCard;
use Acme\SymfonyDb\Entity\Odd;
use CoreBundle\Service\CacheService;
use GamesApiBundle\Service\GameRunResults\BaccaratSideOddsProvider;
use GamesApiBundle\Service\GameRunResults\Calculator\BaccaratCalculator;
use SymfonyTests\_support\Doctrine\EntityHelper;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\GamesApiBundle\Fixture\GameRunResults\OddFixture;
use SymfonyTests\UnitTester;

/**
 * Class BaccaratCalculatorCest
 */
final class BaccaratCalculatorCest extends AbstractUnitTest
{
    protected array $tables = [
        Odd::class,
    ];

    protected array $fixtures = [
        OddFixture::class,
    ];

    private BaccaratCalculator $calculator;

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->calculator = $I->getContainer()->get(BaccaratCalculator::class);
    }

    /**
     * {@inheritDoc}
     */
    protected function setUpFixtures(): void
    {
        parent::setUpFixtures();
        $this->fixtureBoostrapper->addGames([GameDefinition::BACCARAT, GameDefinition::POKER]);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \ReflectionException
     */
    public function testCalculateDealerWins(UnitTester $I): void
    {
        $cards = [
            (new BaccaratRunCard())->setDealtTo('player')->setCard(
                (new BaccaratCard())->setSuit('h')->setValue('j')->setScore(0)
            ),
            (new BaccaratRunCard())->setDealtTo('player')->setCard(
                (new BaccaratCard())->setSuit('s')->setValue('j')->setScore(0)
            ),
            (new BaccaratRunCard())->setDealtTo('player')->setCard(
                (new BaccaratCard())->setSuit('d')->setValue('3')->setScore(3)
            ),
            (new BaccaratRunCard())->setDealtTo('dealer')->setCard(
                (new BaccaratCard())->setSuit('h')->setValue('3')->setScore(3)
            ),
            (new BaccaratRunCard())->setDealtTo('dealer')->setCard(
                (new BaccaratCard())->setSuit('d')->setValue('j')->setScore(0)
            ),
            (new BaccaratRunCard())->setDealtTo('dealer')->setCard(
                (new BaccaratCard())->setSuit('h')->setValue('3')->setScore(3)
            ),
        ];

        $winner = $this->calculator->calculate($cards);
        $I->assertEquals(['dealer'], $winner->getWinners());
        $I->assertEquals([4, 6, 9], $winner->getCombinations());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \ReflectionException
     */
    public function testCalculateTie(UnitTester $I): void
    {
        $cards = [
            (new BaccaratRunCard())->setDealtTo('player')->setCard(
                (new BaccaratCard())->setSuit('h')->setValue('6')->setScore(6)
            ),
            (new BaccaratRunCard())->setDealtTo('player')->setCard(
                (new BaccaratCard())->setSuit('d')->setValue('6')->setScore(6)
            ),
            (new BaccaratRunCard())->setDealtTo('player')->setCard(
                (new BaccaratCard())->setSuit('h')->setValue('5')->setScore(5)
            ),
            (new BaccaratRunCard())->setDealtTo('dealer')->setCard(
                (new BaccaratCard())->setSuit('h')->setValue('j')->setScore(0)
            ),
            (new BaccaratRunCard())->setDealtTo('dealer')->setCard(
                (new BaccaratCard())->setSuit('s')->setValue('7')->setScore(7)
            ),
        ];

        $winner = $this->calculator->calculate($cards);
        $I->assertEquals(['tie'], $winner->getWinners());
        $I->assertEquals([4, 6, 9], $winner->getCombinations());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \ReflectionException
     */
    public function testCalculatePlayerWins(UnitTester $I): void
    {
        $cards = [
            (new BaccaratRunCard())->setDealtTo('player')->setCard(
                (new BaccaratCard())->setSuit('h')->setValue('9')->setScore(9)
            ),
            (new BaccaratRunCard())->setDealtTo('player')->setCard(
                (new BaccaratCard())->setSuit('d')->setValue('4')->setScore(4)
            ),
            (new BaccaratRunCard())->setDealtTo('player')->setCard(
                (new BaccaratCard())->setSuit('h')->setValue('10')->setScore(0)
            ),
            (new BaccaratRunCard())->setDealtTo('dealer')->setCard(
                (new BaccaratCard())->setSuit('h')->setValue('a')->setScore(0)
            ),
            (new BaccaratRunCard())->setDealtTo('dealer')->setCard(
                (new BaccaratCard())->setSuit('s')->setValue('a')->setScore(0)
            ),
            (new BaccaratRunCard())->setDealtTo('dealer')->setCard(
                (new BaccaratCard())->setSuit('d')->setValue('q')->setScore(2)
            ),
        ];

        $winner = $this->calculator->calculate($cards);

        $I->assertEquals(['player'], $winner->getWinners());
        $I->assertEquals([5, 6, 9], $winner->getCombinations());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \ReflectionException
     */
    public function testPerfectPair(UnitTester $I): void
    {
        /** @var BaccaratCard $dbCard */
        $dbCard = EntityHelper::getEntityWithId(BaccaratCard::class, 1);
        $cards = [
            (new BaccaratRunCard())->setDealtTo('player')->setCard(
                (new BaccaratCard())->setSuit('s')->setValue('4')->setScore(4)
            ),
            (new BaccaratRunCard())->setDealtTo('player')->setCard(
                (new BaccaratCard())->setSuit('s')->setValue('4')->setScore(4)
            ),
            (new BaccaratRunCard())->setDealtTo('dealer')->setCard(
                (new BaccaratCard())->setSuit('d')->setValue('k')->setScore(0)
            ),
            (new BaccaratRunCard())->setDealtTo('dealer')->setCard(
                (new BaccaratCard())->setSuit('d')->setValue('k')->setScore(0)
            ),
        ];

        $winner = $this->calculator->calculate($cards);

        $I->assertEquals(['player'], $winner->getWinners());
        $I->assertEquals(
            [4, 5, 6, 7, 8],
            $winner->getCombinations()
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testSideOddsShouldBeProperlyMappedAndCached(UnitTester $I): void
    {
        /** @var BaccaratSideOddsProvider $provider */
        $provider = $I->getContainer()->get(BaccaratSideOddsProvider::class);

        $expectedMap = [
            'PAIR_PLAYER' => 4,
            'PAIR_BANKER' => 5,
            'PAIR_ANY' => 6,
            'PAIR_PERFECT' => 7,
            'HAND_SMALL' => 8,
            'HAND_BIG' => 9,
        ];
        $I->assertEquals($expectedMap, $provider->getSideOddsIdsMap());

        $cache = $I->getContainer()->get(CacheService::class)->get('baccarat:side-odds');
        $I->assertStringEqualsFile(
            __DIR__ . '/../../../Fixture/GameRunResults/side-bets.cached',
            $cache
        );

        $logger = $I->getSqlLogger();
        $I->assertNotEmpty($logger->queries);
        $logger->queries = [];

        // From cache - same data.
        $I->assertEquals($expectedMap, $provider->getSideOddsIdsMap());
        // No DB queries was executed - took from cache.
        $I->assertEmpty($logger->queries);
    }
}
