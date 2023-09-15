<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\GameRunResults\Calculator;

use Acme\BetOnPokerCalculator\Game\GameBetOnPoker;
use Acme\SymfonyDb\Entity\PokerCard;
use Acme\SymfonyDb\Entity\PokerRunCard;
use Doctrine\ORM\Tools\ToolsException;
use GamesApiBundle\Service\GameRunResults\Calculator\PokerCalculator;
use SymfonyTests\_support\Doctrine\EntityHelper;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\CoreBundle\Fixture\PokerCardFixture;
use SymfonyTests\UnitTester;

/**
 * Class PokerCalculatorCest
 */
final class PokerCalculatorCest extends AbstractUnitTest
{
    protected array $tables = [
        PokerCard::class,
    ];

    protected array $fixtures = [
        PokerCardFixture::class,
    ];

    private const CARD_IDS = [
        0 => [103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 202, 203, 204, 205, 206, 207],
        1 => [213, 104, 305, 106, 407, 108, 412, 110, 111, 112, 113, 202, 203, 204, 303, 206, 207],
    ];

    private PokerCalculator $calculator;

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->calculator = new PokerCalculator(new GameBetOnPoker());
    }

    /**
     * @param UnitTester $I
     */
    public function testCalculate(UnitTester $I): void
    {
        $cards = [];
        $cardRepo = $this->getEntityManager()->getRepository(PokerCard::class);

        foreach (self::CARD_IDS[0] as $cardId) {
            $dbCard = $cardRepo->find($cardId);
            $cards[] = (new PokerRunCard())->setCard($dbCard);
        }

        $winner = $this->calculator->calculate($cards);

        $I->assertEquals(['1', '2', '3', '4', '5', '6'], $winner->getWinners());
        $I->assertEquals([9], $winner->getCombinations());
    }

    /**
     * @param UnitTester $I
     */
    public function testCalculateDiff(UnitTester $I): void
    {
        $cards = [];
        foreach (self::CARD_IDS[1] as $cardId) {
            $dbCard = (new PokerCard())
                ->setSuit('any')
                ->setValue('any')
            ;
            EntityHelper::setId($dbCard, $cardId);
            $cards[] = (new PokerRunCard())->setCard($dbCard);
        }

        $winner = $this->calculator->calculate($cards);

        $I->assertEquals(['1'], $winner->getWinners());
        $I->assertEquals([6], $winner->getCombinations());
    }
}
