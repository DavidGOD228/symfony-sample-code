<?php

namespace SymfonyTests\Unit\GamesApiBundle\Service;

use Acme\SymfonyDb\Interfaces\CardInterface;
use Codeception\Stub;
use GamesApiBundle\Service\CardFormatter;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class CardFormatterCest
 */
class CardFormatterCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testFormatCard(UnitTester $I)
    {
        foreach ($this->getCases() as $expected => $card) {
            $result = CardFormatter::formatCard($card);
            $I->assertEquals($expected, $result, "Wrong for: $expected");
        }
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    private function getCases(): array
    {
        return [
            '2d' => $this->getCard('2', 'diamonds'),
            'Th' => $this->getCard('T', 'hearts'),
            'Tc' => $this->getCard('10', 'clubs'),
            'As' => $this->getCard('Ace', 'spades'),
            'Ah' => $this->getCard('A', 'hearts'),
            'Qc' => $this->getCard('Queen', 'clubs'),
            'Qd' => $this->getCard('Queen', 'diamonds'),
        ];
    }

    /**
     * @param string $value
     * @param string $suit
     *
     * @return CardInterface
     * @throws \Exception
     */
    private function getCard(string $value, string $suit): CardInterface
    {
        /** @var CardInterface $card */
        $card = Stub::makeEmpty(CardInterface::class, [
            'getValue' => $value,
            'getSuit' => $suit
        ]);
        return $card;
    }
}
