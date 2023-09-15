<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\GameRunResults\Calculator;

use Acme\PokerCalculator\Game\GamePokerHeadsup;
use Acme\SymfonyDb\Entity\HeadsUpCard;
use Acme\SymfonyDb\Entity\HeadsUpRunCard;
use Acme\SymfonyDb\Type\DealedToType;
use Doctrine\ORM\Tools\ToolsException;
use GamesApiBundle\Service\GameRunResults\Calculator\HeadsUpCalculator;
use SymfonyTests\_support\Doctrine\EntityHelper;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class HeadsUpCalculatorCest
 */
final class HeadsUpCalculatorCest extends AbstractUnitTest
{
    private const CARD_IDS = [
        0 => [
            DealedToType::PLAYER => [106, 107],
            DealedToType::DEALER => [108, 109],
            DealedToType::BOARD => [110, 111, 112, 113, 206],
        ],
        1 => [
            DealedToType::PLAYER => [109, 113],
            DealedToType::DEALER => [114, 207],
            DealedToType::BOARD => [213, 209, 314, 409, 407],
        ],
    ];

    private HeadsUpCalculator $calculator;

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->calculator = new HeadsUpCalculator(new GamePokerHeadsup());
    }

    /**
     * @param UnitTester $I
     */
    public function testCalculate(UnitTester $I): void
    {
        $cards = [];
        foreach (self::CARD_IDS[0] as $dealtTo => $cardIds) {
            foreach ($cardIds as $cardId) {
                $dbCard = (new HeadsUpCard())
                    ->setSuit('any')
                    ->setValue('any');
                EntityHelper::setId($dbCard, $cardId);
                $cards[] = (new HeadsUpRunCard())->setDealtTo($dealtTo)->setCard($dbCard);
            }
        }

        $winner = $this->calculator->calculate($cards);

        $I->assertEquals(['dealer'], $winner->getWinners());
        $I->assertEquals([9], $winner->getCombinations());
    }

    /**
     * @param UnitTester $I
     */
    public function testCalculateDiff(UnitTester $I): void
    {
        $cards = [];
        foreach (self::CARD_IDS[1] as $dealtTo => $cardIds) {
            foreach ($cardIds as $cardId) {
                $dbCard = (new HeadsUpCard())
                    ->setSuit('any')
                    ->setValue('any');
                EntityHelper::setId($dbCard, $cardId);
                $cards[] = (new HeadsUpRunCard())->setDealtTo($dealtTo)->setCard($dbCard);
            }
        }

        $winner = $this->calculator->calculate($cards);

        $I->assertEquals(['player'], $winner->getWinners());
        $I->assertEquals([6], $winner->getCombinations());
    }
}
