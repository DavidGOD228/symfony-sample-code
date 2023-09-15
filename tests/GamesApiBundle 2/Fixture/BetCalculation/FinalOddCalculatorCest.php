<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\BetCalculation;

use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Combination;
use GamesApiBundle\Service\BetCalculation\FinalOddCalculator;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class FinalOddCalculatorCest
 */
final class FinalOddCalculatorCest extends AbstractUnitTest
{
    private FinalOddCalculator $finalOddCalculator;

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->finalOddCalculator = $I->getContainer()->get(FinalOddCalculator::class);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function getLostFinalOddTest(UnitTester $I): void
    {
        $bet = (new Bet())->setStatus(Bet::STATUS_LOST);
        $I->assertEquals(0.00, $this->finalOddCalculator->calculate($bet));
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function getReturnedFinalOddTest(UnitTester $I): void
    {
        $bet = (new Bet())->setStatus(Bet::STATUS_RETURNED);
        $I->assertEquals(1.00, $this->finalOddCalculator->calculate($bet));
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function getWonFinalOddTest(UnitTester $I): void
    {
        $bet = (new Bet())
            ->setStatus(Bet::STATUS_WON)
            ->setOddValue(23.23);

        $I->assertEquals(23.23, $this->finalOddCalculator->calculate($bet));
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function getComboLostFinalOddTest(UnitTester $I): void
    {
        $bet = (new Bet())->setOddValue(5.0);
        $combination = (new Combination())
            ->addBet($bet)
            ->setAmount(1)
            ->setAmountWon(0)
        ;


        $I->assertEquals(
            0.00,
            $this->finalOddCalculator->calculateCombinationBet($combination, $bet)
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function getComboReturnedFinalOddTest(UnitTester $I): void
    {
        $bet = (new Bet())->setOddValue(5.0);
        $combination = (new Combination())
            ->addBet($bet)
            ->setAmount(3)
            ->setAmountWon(3)
        ;


        $I->assertEquals(
            1.00,
            $this->finalOddCalculator->calculateCombinationBet($combination, $bet)
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function getComboWonFinalOddTest(UnitTester $I): void
    {
        $bet = (new Bet())->setOddValue(5.0);
        $combination = (new Combination())
            ->addBet($bet)
            ->setAmount(3)
            ->setAmountWon(5)
        ;

        $I->assertEquals(
            5.00,
            $this->finalOddCalculator->calculateCombinationBet($combination, $bet)
        );
    }
}
