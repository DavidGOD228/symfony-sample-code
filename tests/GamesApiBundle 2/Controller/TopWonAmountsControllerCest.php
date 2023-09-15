<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Controller;

use Codeception\Stub;
use CoreBundle\Exception\ValidationException;
use GamesApiBundle\Controller\TopWonAmountsController;
use GamesApiBundle\DataObject\TopWonAmount;
use GamesApiBundle\Service\TopWon\TopWonAmountsProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class TopWonAmountsControllerCest
 */
final class TopWonAmountsControllerCest extends AbstractUnitTest
{
    /**
     * @var TopWonAmountsController
     */
    private $controller;

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     * @throws \Exception
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);
        /* @var TopWonAmountsProvider $topWonAmountsService */
        $topWonAmountsService = Stub::make(
            TopWonAmountsProvider::class,
            [
                'getTopWonAmountsByPartnerId' => [],
                'getTopWonAmountByGameId' => (new TopWonAmount(1, []))
            ]
        );

        $this->controller = new TopWonAmountsController(
            $topWonAmountsService
        );

        $this->controller->setContainer($I->getContainer());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testGetByGameIdAction(UnitTester $I): void
    {
        $gameId = (string) 1;
        $response = $this->controller->getByGameIdAction($gameId);
        $I->assertEquals('{"gameId":1,"amounts":[]}', $response->getContent());

        $controller = $this->controller;

        $I->expectThrowable(ValidationException::class, function () use ($controller)
        {
            $controller->getByGameIdAction('veryWrongGameId');
        });
    }

    /**
     * @param array $data
     * @param int $statusCode
     *
     * @return JsonResponse
     */
    protected function jsonResponse(array $data, $statusCode = JsonResponse::HTTP_OK): JsonResponse
    {
        return new JsonResponse($data, $statusCode);
    }
}