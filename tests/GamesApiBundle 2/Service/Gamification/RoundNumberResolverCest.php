<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\Gamification;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyDb\Entity\PokerRunRound;
use Doctrine\ORM\Tools\ToolsException;
use GamesApiBundle\Service\Gamification\RoundNumberResolver;
use InvalidArgumentException;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class RoundNumberResolverCest
 */
final class RoundNumberResolverCest extends AbstractUnitTest
{
    private RoundNumberResolver $roundNumberResolver;

    protected array $tables = [
        PokerRunRound::class,
    ];

    /**
     * {@inheritDoc}
     */
    protected function setUpFixtures(): void
    {
        parent::setUpFixtures();

        $gameIds = [
            GameDefinition::LUCKY_7,
            GameDefinition::POKER,
            GameDefinition::BACCARAT,
            GameDefinition::WAR,
            GameDefinition::SPEEDY7,
            GameDefinition::HEADSUP,
            GameDefinition::ANDAR_BAHAR,
            GameDefinition::STS_POKER,
            GameDefinition::RPS,
        ];
        $this->fixtureBoostrapper->addRunRounds($gameIds, true, true);
        $this->fixtureBoostrapper->addBets($gameIds);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $container = $I->getContainer();
        $this->roundNumberResolver = $container->get(RoundNumberResolver::class);
    }

    /**
     * @param UnitTester $I
     */
    public function testGetRoundNumberWithUnsupportedGame(UnitTester $I): void
    {
        $game = Game::createFromId(100);
        $odd = (new Odd())->setGame($game);
        $bet = (new Bet())->setOdd($odd)->setRunRoundId(1);

        $I->expectThrowable(
            new InvalidArgumentException('UNSUPPORTED_GAME:100'),
            function () use ($bet): void {
                $this->roundNumberResolver->getRoundNumber($bet);
            }
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testGetRoundNumber(UnitTester $I): void
    {
        foreach (GameDefinition::getKnown() as $gameId) {
            if ($gameId === GameDefinition::MATKA) { // ToDo: Remove in CORE-2739
                continue;
            }
            $game = Game::createFromId($gameId);
            $odd = (new Odd())->setGame($game);
            $bet = (new Bet())->setOdd($odd)->setRunRoundId(1);
            $roundNumber = $this->roundNumberResolver->getRoundNumber($bet);

            if (GameDefinition::isLottery($gameId)) {
                $I->assertNull($roundNumber);
            } else {
                $I->assertContains($roundNumber, [1, 2]);
            }
        }
    }

    /**
     * @param UnitTester $I
     */
    public function testRunRoundLottery(UnitTester $I): void
    {
        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:1:1");
        $bet->setRunRoundId(1);
        $roundNumber = $this->roundNumberResolver->getRoundNumber($bet);
        $I->assertNull($roundNumber);
    }

    /**
     * @param UnitTester $I
     */
    public function testRunRoundPoker(UnitTester $I): void
    {
        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:5:1");
        $bet->setRunRoundId(1);
        $roundNumber = $this->roundNumberResolver->getRoundNumber($bet);
        $I->assertEquals(1, $roundNumber);
    }

    /**
     * @param UnitTester $I
     */
    public function testRunRoundBaccarat(UnitTester $I): void
    {
        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:6:1");
        $bet->setRunRoundId(2);
        $roundNumber = $this->roundNumberResolver->getRoundNumber($bet);
        $I->assertEquals(2, $roundNumber);
    }

    /**
     * @param UnitTester $I
     */
    public function testRunRoundWar(UnitTester $I): void
    {
        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:8:1");
        $bet->setRunRoundId(1);
        $roundNumber = $this->roundNumberResolver->getRoundNumber($bet);
        $I->assertEquals(1, $roundNumber);
    }

    /**
     * @param UnitTester $I
     */
    public function testRunRoundSpeedy7(UnitTester $I): void
    {
        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:11:1");
        $bet->setRunRoundId(2);
        $roundNumber = $this->roundNumberResolver->getRoundNumber($bet);
        $I->assertEquals(2, $roundNumber);
    }

    /**
     * @param UnitTester $I
     */
    public function testRunRoundsHeadsUp(UnitTester $I): void
    {
        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:12:1");
        $bet->setRunRoundId(1);
        $roundNumber = $this->roundNumberResolver->getRoundNumber($bet);
        $I->assertEquals(1, $roundNumber);
    }

    /**
     * @param UnitTester $I
     */
    public function testRunRoundAb(UnitTester $I): void
    {
        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:13:1");
        $bet->setRunRoundId(2);
        $roundNumber = $this->roundNumberResolver->getRoundNumber($bet);
        $I->assertEquals(2, $roundNumber);
    }

    /**
     * @param UnitTester $I
     */
    public function testRunRoundPokerSts(UnitTester $I): void
    {
        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:14:1");
        $bet->setRunRoundId(1);
        $roundNumber = $this->roundNumberResolver->getRoundNumber($bet);
        $I->assertEquals(1, $roundNumber);
    }

    /**
     * @param UnitTester $I
     */
    public function testRunRoundRps(UnitTester $I): void
    {
        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:15:1");
        $bet->setRunRoundId(2);
        $roundNumber = $this->roundNumberResolver->getRoundNumber($bet);
        $I->assertEquals(1, $roundNumber);// Rps game has always 1 round
    }
}
