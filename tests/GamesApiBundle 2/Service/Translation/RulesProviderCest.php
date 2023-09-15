<?php

namespace SymfonyTests\Unit\GamesApiBundle\Service\Translation;

use Codeception\Stub;
use GamesApiBundle\Service\Translation\RulesProvider;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class RulesProviderCest
 */
class RulesProviderCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testFormat(UnitTester $I): void
    {
        /* @var RulesProvider $rulesProvide */
        $rulesProvide = Stub::make(RulesProvider::class);

        $code = 'rules.jackpot.11.01';
        $title = 'Rules';
        $desc = 'To participate, you must start playing from the first card.';
        $translation = $title . '|||' . $desc;
        $formatted = $rulesProvide->format($code, $translation);

        $I->assertEquals('jackpot', $formatted['promotion']);
        $I->assertEquals('11', $formatted['gameId']);
        $I->assertEquals('01', $formatted['index']);
        $I->assertEquals($desc, $formatted['description']);
        $I->assertEquals($title, $formatted['title']);
    }
}