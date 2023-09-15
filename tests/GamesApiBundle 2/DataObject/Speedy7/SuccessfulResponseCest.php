<?php

namespace SymfonyTests\Unit\GamesApiBundle\DataObject\Speedy7;

use GamesApiBundle\DataObject\Speedy7\SuccessfulResponse;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class SuccessfulResponseCest
 */
class SuccessfulResponseCest extends AbstractUnitTest
{
    /**
     * @var SuccessfulResponse
     */
    private $response;

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);
        $this->response = new SuccessfulResponse(1);
    }

    /**
     * @param UnitTester $I
     */
    public function testResponse(UnitTester $I): void
    {
        $result = $this->response->getEntityId();
        $I->assertEquals(1, $result);

        $result = $this->response->getIsOk();
        $I->assertEquals(true, $result);

        $this->response->setEntityId(2);
        $result = $this->response->getEntityId();
        $I->assertEquals(2, $result);
    }
}