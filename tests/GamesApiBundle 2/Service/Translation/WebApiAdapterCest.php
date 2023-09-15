<?php

namespace SymfonyTests\Unit\GamesApiBundle\Service\Translation;

use GamesApiBundle\Service\Translation\WebApiAdapter;
use SymfonyTests\_support\Translation\StubTranslator;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class WebApiAdapterCest
 */
class WebApiAdapterCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     */
    public function testTranslation(UnitTester $I): void
    {
        $translator = new StubTranslator();
        $service = new WebApiAdapter($translator);
        $I->assertEquals(
            '[t:lt:odds]1-ODD[/t]',
            $service->translateOdd(1, 'ODD', 'lt')
        );
        $I->assertEquals(
            '[t:lt:games]1[/t]',
            $service->translateGame(1, 'lt')
        );
    }
}