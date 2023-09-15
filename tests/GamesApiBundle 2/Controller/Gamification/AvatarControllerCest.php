<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Controller\Gamification;

use Doctrine\ORM\Tools\ToolsException;
use Eastwest\Json\Json;
use GamesApiBundle\Controller\Gamification\AvatarController;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;
use Acme\SymfonyRequest\Request;

/**
 * Class AvatarControllerCest
 */
final class AvatarControllerCest extends AbstractUnitTest
{
    private AvatarController $controller;

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $container = $I->getContainer();
        /** @var AvatarController $controller */
        $controller = $container->get(AvatarController::class);

        $this->controller = $controller;
        $this->controller->setContainer($container);
    }

    /**
     * @param UnitTester $I
     */
    public function testSuccessListActionAction(UnitTester $I): void
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['SERVER_NAME' => 'Acmetv.eu']
        );

        $I->getRequestStack()->push($request);

        $expectedResponse = Json::decode(
            file_get_contents(__DIR__ . '/../../Fixture/Gamification/response/avatars-data.response')
        );

        $actualResponse = Json::decode(
            $this->controller->listAction($request)->getContent()
        );

        $I->assertEquals($expectedResponse, $actualResponse);
    }
}
