<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\DataObject;

use Acme\SymfonyRequest\Request;
use GamesApiBundle\Request\PartnerInitialStateRequest;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class InitialStateRequestCest
 */
final class InitialStateRequestCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     */
    public function testNoRequestShouldNotFilter(UnitTester $I) : void
    {
        $request = new Request();
        $request->server->set('REMOTE_ADDR', '127.0.0.1');
        $dto = new PartnerInitialStateRequest($I->getUserSession(), $request);

        $I->assertNull($dto->getIosAppVersion());
    }

    /**
     * @param UnitTester $I
     */
    public function testInvalidHeaderShouldNotFilter(UnitTester $I) : void
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['REMOTE_ADDR' => '127.0.0.1', 'HTTP_IOS_PACKAGE' => '1_2_3']
        );
        $dto = new PartnerInitialStateRequest($I->getUserSession(), $request);

        $I->assertNull($dto->getIosAppVersion());
    }

    /**
     * @param UnitTester $I
     */
    public function testValidHeaderShouldProvideIosAppVersion(UnitTester $I) : void
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['REMOTE_ADDR' => '127.0.0.1', 'HTTP_IOS_PACKAGE' => '1.2.3']
        );
        $dto = new PartnerInitialStateRequest($I->getUserSession(), $request);

        $I->assertEquals('1.2.3', $dto->getIosAppVersion());
    }
}
