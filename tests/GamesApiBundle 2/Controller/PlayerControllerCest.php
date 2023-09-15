<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Controller;

use Acme\WebApi\Api\WebApiNone;
use Acme\WebApi\Enum\WebApiType;
use Acme\WebApi\Feature\ReinitSessionApiInterface;
use Codeception\Stub;
use CoreBundle\Service\CacheService;
use GamesApiBundle\Controller\PlayerController;
use PartnerApiBundle\Service\PartnerWebApiProvider;
use Symfony\Component\HttpFoundation\Request;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class PlayerControllerCest
 */
final class PlayerControllerCest extends AbstractUnitTest
{
    /** @inheritDoc */
    protected function setUpFixtures(): void
    {
        $this->fixtureBoostrapper->addPartners(1, true);
        $this->fixtureBoostrapper->addPlayers();
    }

    /**
     * @param UnitTester $I
     *
     * @throws \CoreBundle\Exception\CoreException
     */
    public function testNotAuthUser(UnitTester $I): void
    {
        $container = $I->getContainer();
        /** @var PlayerController $controller */
        $controller = $container->get(PlayerController::class);
        $response = $controller->balanceAction();
        $I->assertEquals('{"show":false,"value":"0"}', $response->getContent());

        $response = $controller->refreshBalanceAction();
        $I->assertEquals('false', $response->getContent());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \CoreBundle\Exception\CoreException
     */
    public function testAuthUserGetBalance(UnitTester $I): void
    {
        $container = $I->getContainer();
        $I->getUserSession()
          ->setApiUserData('externalToken1', 1, 1, 1);

        /** @var PlayerController $controller */
        $controller = $container->get(PlayerController::class);
        $partner = $this->getEntityByReference('partner:1');
        $partner->setApiId('Acme1');

        /** @var CacheService $cache */
        $cache = $container->get(CacheService::class);
        $cache->set('directapi_balance_1', '100eur');
        $cache->set('player_token_validated_1', true);

        $response = $controller->balanceAction();
        $I->assertEquals('{"show":false,"value":"100eur"}', $response->getContent());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \CoreBundle\Exception\CoreException
     */
    public function testAuthUser(UnitTester $I): void
    {
        $container = $I->getContainer();
        $I->getUserSession()
          ->setApiUserData('externalToken1', 1, 1, 1);

        /** @var PlayerController $controller */
        $controller = $container->get(PlayerController::class);

        $partner = $this->getEntityByReference('partner:1');
        $partner->setApiId('Acme1');

        $response = $controller->refreshBalanceAction();
        $I->assertEquals('true', $response->getContent());

        $I->assertCount(1, $I->getProducer()->messages);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testRefreshTokenAuth(UnitTester $I): void
    {
        $container = $I->getContainer();

        $I->getUserSession()
            ->setApiUserData('externalToken1', 1, 1, 1);

        /** @var PlayerController $controller */
        $controller = $container->get(PlayerController::class);
        $request = new Request([], [], [], [], [], ['REMOTE_ADDR' => '192.168.0.1']);
        $response = $controller->refreshTokenAction($request);
        $I->assertTrue($response->isSuccessful());
        $I->assertEquals('true', $response->getContent());
    }

    /**
     * @param UnitTester $I
     */
    public function testRefreshTokenActionNoAuth(UnitTester $I): void
    {
        $container = $I->getContainer();
        /** @var PlayerController $controller */
        $controller = $container->get(PlayerController::class);
        $request = new Request([], [], [], [], [], ['REMOTE_ADDR' => '192.168.0.1']);
        $response = $controller->refreshTokenAction($request);
        $I->assertTrue($response->isSuccessful());
        $I->assertEquals('false', $response->getContent());
    }

    /**
     * @param UnitTester $I
     */
    public function testRefreshTokenAuthAndCache(UnitTester $I): void
    {
        $container = $I->getContainer();
        $I->getUserSession()
          ->setApiUserData('externalToken1', 1, 1, 1);
        $container->get(CacheService::class)->set('player:token_validated:1', '123');
        /** @var PlayerController $controller */
        $controller = $container->get(PlayerController::class);
        $request = new Request([], [], [], [], [], ['REMOTE_ADDR' => '192.168.0.1']);
        $response = $controller->refreshTokenAction($request);
        $I->assertTrue($response->isSuccessful());
        $I->assertEquals('true', $response->getContent());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testRefreshTokenAuthPartnerNoApi(UnitTester $I): void
    {
        $container = $I->getContainer();

        $container->set(
            PartnerWebApiProvider::class,
            Stub::make(
                PartnerWebApiProvider::class,
                [
                    'getPartnerApi' => new WebApiNone('', '', 0),
                ]
            )
        );

        $I->getUserSession()
          ->setApiUserData('externalToken1', 1, 1, 1);
        /** @var PlayerController $controller */
        $controller = $container->get(PlayerController::class);
        $request = new Request([], [], [], [], [], ['REMOTE_ADDR' => '192.168.0.1']);
        $response = $controller->refreshTokenAction($request);
        $I->assertTrue($response->isSuccessful());
        $I->assertEquals('false', $response->getContent());
    }

    /**
     * @param UnitTester $I
     */
    public function testReInitGameSessionAction(UnitTester $I): void
    {
        $container = $I->getContainer();

        $container->set(
            PartnerWebApiProvider::class,
            Stub::make(
                PartnerWebApiProvider::class,
                [
                    'getPartnerApi' => Stub::makeEmpty(
                        ReinitSessionApiInterface::class,
                        [
                            'getType' => WebApiType::getIsoftbet(),
                            'reinitializeGameSession' => true,
                        ]
                    ),
                ]
            )
        );

        $session = $I->getUserSession();
        $session->setApiUserData('externalToken1', 1, 1, 1);
        $session->setProvidedUserData('en', 0, true, 'default');

        /** @var PlayerController $controller */
        $controller = $container->get(PlayerController::class);
        $response = $controller->reInitGameSessionAction(1);
        $I->assertTrue($response->isSuccessful());
        $I->assertEquals('true', $response->getContent());
    }

    /**
     * @param UnitTester $I
     */
    public function testReInitGameSessionActionNoAuth(UnitTester $I): void
    {
        $container = $I->getContainer();
        /** @var PlayerController $controller */
        $controller = $container->get(PlayerController::class);
        $response = $controller->reInitGameSessionAction(1);
        $I->assertTrue($response->isSuccessful());
        $I->assertEquals('false', $response->getContent());
    }

    /**
     * @param UnitTester $I
     */
    public function testReInitGameSessionActionNoApi(UnitTester $I): void
    {
        $container = $I->getContainer();
        /** @var PlayerController $controller */
        $controller = $container->get(PlayerController::class);
        $response = $controller->reInitGameSessionAction(1);
        $I->assertTrue($response->isSuccessful());
        $I->assertEquals('false', $response->getContent());
    }
}
