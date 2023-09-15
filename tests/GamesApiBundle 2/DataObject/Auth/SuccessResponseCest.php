<?php

namespace SymfonyTests\Unit\GamesApiBundle\DataObject\Auth;

use GamesApiBundle\DataObject\Auth\SuccessResponse;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;
use Exception;

/**
 * Class SuccessResponseCest
 */
class SuccessResponseCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testJsonSerialize(UnitTester $I): void
    {
        $response = new SuccessResponse('1');

        $I->assertEquals(['auth' => '1'], $response->jsonSerialize());
    }
}