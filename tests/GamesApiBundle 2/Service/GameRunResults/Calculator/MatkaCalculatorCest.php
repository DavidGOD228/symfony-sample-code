<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\GameRunResults\Calculator;

use Acme\SymfonyDb\Entity\MatkaCard;
use Acme\SymfonyDb\Entity\MatkaRunCard;
use GamesApiBundle\Service\GameRunResults\Calculator\MatkaCalculator;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class MatkaCalculatorCest
 */
final class MatkaCalculatorCest extends AbstractUnitTest
{

    private MatkaCalculator $calculator;

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);
        $this->calculator = $I->getContainer()->get(MatkaCalculator::class);
    }

    /**
     * @param UnitTester $I
     */
    public function testCalculateSinglePana(UnitTester $I): void
    {
        $cards = [
            (new MatkaRunCard())->setCard((new MatkaCard())->setValue('10')->setSuit('s')),
            (new MatkaRunCard())->setCard((new MatkaCard())->setValue('7')->setSuit('c')),
            (new MatkaRunCard())->setCard((new MatkaCard())->setValue('a')->setSuit('h')),
        ];

        $winner = $this->calculator->calculate($cards);
        $I->assertEquals(['ank' => '8', 'pana' => '170', 'type' => 'single'], $winner->getWinners());
        $I->assertEquals([], $winner->getCombinations());
    }

    /**
     * @param UnitTester $I
     */
    public function testCalculateDoublePana(UnitTester $I): void
    {
        $cards = [
            (new MatkaRunCard())->setCard((new MatkaCard())->setValue('10')->setSuit('s')),
            (new MatkaRunCard())->setCard((new MatkaCard())->setValue('a')->setSuit('c')),
            (new MatkaRunCard())->setCard((new MatkaCard())->setValue('a')->setSuit('h')),
        ];

        $winner = $this->calculator->calculate($cards);
        $I->assertEquals(['ank' => '2', 'pana' => '110', 'type' => 'double'], $winner->getWinners());
        $I->assertEquals([], $winner->getCombinations());
    }

    /**
     * @param UnitTester $I
     */
    public function testCalculateTriplePana(UnitTester $I): void
    {
        $cards = [
            (new MatkaRunCard())->setCard((new MatkaCard())->setValue('7')->setSuit('s')),
            (new MatkaRunCard())->setCard((new MatkaCard())->setValue('7')->setSuit('c')),
            (new MatkaRunCard())->setCard((new MatkaCard())->setValue('7')->setSuit('h')),
        ];

        $winner = $this->calculator->calculate($cards);
        $I->assertEquals(['ank' => '1', 'pana' => '777', 'type' => 'triple'], $winner->getWinners());
        $I->assertEquals([], $winner->getCombinations());
    }
}
