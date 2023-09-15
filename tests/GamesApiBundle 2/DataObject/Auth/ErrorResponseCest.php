<?php

namespace SymfonyTests\Unit\GamesApiBundle\DataObject\Auth;

use GamesApiBundle\DataObject\Auth\ErrorResponse;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;
use Exception;

/**
 * Class ErrorResponseCest.php
 */
class ErrorResponseCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testJsonSerialize(UnitTester $I): void
    {
        $response = new ErrorResponse('response error');

        $I->assertEquals(['error' => 'response error'], $response->jsonSerialize());
    }
}