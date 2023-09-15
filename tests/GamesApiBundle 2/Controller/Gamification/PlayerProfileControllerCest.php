<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Controller\Gamification;

use Acme\Semaphore\SemaphoreException;
use Acme\Semaphore\SemaphoreInterface;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Entity\PlayerProfile;
use Acme\SymfonyRequest\RequestValidationException;
use Codeception\Stub;
use CoreBundle\Constraint\UniqueEntityPropertyConstraintValidator;
use CoreBundle\Exception\MissingSessionKeyException;
use CoreBundle\Exception\ValidationException;
use Doctrine\ORM\EntityManagerInterface;
use GamesApiBundle\Controller\Gamification\PlayerProfileController;
use GamesApiBundle\DataObject\Gamification\GamificationProfile;
use GamesApiBundle\Exception\Gamification\CaptainUpException;
use GamesApiBundle\Service\Gamification\PlayerProfileService;
use GamesApiBundle\Service\Gamification\RequestValidator;
use GamesApiBundle\Service\PlayerService;
use Acme\SymfonyRequest\Request;
use Symfony\Component\Serializer\SerializerInterface;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\PartnerFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\PlayerFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\PlayerProfileFixture;
use SymfonyTests\UnitTester;
use Eastwest\Json\Json;

/**
 * Class PlayerProfileControllerCest
 */
final class PlayerProfileControllerCest extends AbstractUnitTest
{
    private PlayerProfileController $controller;
    private SemaphoreInterface $semaphore;

    private string $requestCreateContent = '{"name": "aaaa"}';
    private string $requestValidateNicknameContent = '{"name": "aaaa"}';
    private string $requestValidateNicknameWrongContent = '{"name": "aaa"}';
    private string $requestValidateNicknameAltContent = '{"name": "aaab"}';
    private string $requestUpdateContent = '{"avatar":"avatar_12"}';
    private string $requestBlockContent = '{"reason": "see_no_value"}';
    private string $requestDeleteContent = '{"reason": "other"}';

    protected array $tables = [
        Partner::class,
        Player::class,
        PlayerProfile::class,
    ];

    protected array $fixtures = [
        PartnerFixture::class,
        PlayerFixture::class,
        PlayerProfileFixture::class,
    ];

    /**
     * {@inheritDoc}
     */
    protected function setUpFixtures(): void
    {
        parent::setUpFixtures();
        $this->fixtureBoostrapper->addPartners(1);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $container = $I->getContainer();

        $this->semaphore = $container->get(SemaphoreInterface::class);

        $this->controller = new PlayerProfileController(
            $container->get(RequestValidator::class),
            $container->get(PlayerService::class),
            $container->get(PlayerProfileService::class),
            $this->semaphore,
            $container->get(SerializerInterface::class),
        );
        $this->controller->setContainer($container);

        $repository = $this->getRepositoryProvider()->getMasterRepository(PlayerProfile::class);
        $em = Stub::makeEmpty(EntityManagerInterface::class, [
            'getRepository' => $repository
        ]);
        $uniqueEntityValidator = new UniqueEntityPropertyConstraintValidator($em);

        $I->getContainer()->set(UniqueEntityPropertyConstraintValidator::class, $uniqueEntityValidator);
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     * @throws MissingSessionKeyException
     * @throws ValidationException
     * @throws SemaphoreException
     */
    public function testSuccessCreateAction(UnitTester $I): void
    {
        $I->getUserSession()->setApiUserData('token', 1, 1, 1);
        $profile = $this->getPlayerProfile();

        /** @var PlayerProfileService $playerProfileService */
        $playerProfileService = Stub::make(
            PlayerProfileService::class,
            ['create' => new GamificationProfile('id', $profile, 'signed')]
        );

        $this->stubsToVerify[] = $playerProfileService;

        $controller = new PlayerProfileController(
            $I->getContainer()->get(RequestValidator::class),
            $I->getContainer()->get(PlayerService::class),
            $playerProfileService,
            $this->semaphore,
            $I->getContainer()->get(SerializerInterface::class),
        );
        $controller->setContainer($I->getContainer());

        $request = new Request([], [], [], [], [], [], $this->requestCreateContent);
        $request->headers->set('Content-Type', 'application/json');
        $response = $controller->createAction($request);

        $expected = Json::decode(
            file_get_contents(__DIR__ . '/../../Fixture/Gamification/response/profile.response'),
            true
        );
        $actual = Json::decode($response->getContent(), true);

        $I->assertEquals($expected, $actual);
        $I->assertEquals(0, $I->getWsRedis()->exists('semaphore:createProfile:5'));
    }

    /**
     * @param UnitTester $I
     *
     * @throws MissingSessionKeyException
     * @throws SemaphoreException
     */
    public function testSuccessValidateNicknameAction(UnitTester $I): void
    {
        $I->getUserSession()->setApiUserData('token', 1, 1, 1);

        $this->controller->setContainer($I->getContainer());

        $request = new Request([], [], [], [], [], [], $this->requestValidateNicknameContent);
        $request->headers->set('Content-Type', 'application/json');
        $response = $this->controller->validateNicknameAction($request);

        $expected = ['isAvailable' => true];
        $actual = Json::decode($response->getContent(), true);

        $I->assertEquals($expected, $actual);
        $I->assertEquals(1, $I->getWsRedis()->exists('semaphore:validateNickname:1'));
    }

    /**
     * @param UnitTester $I
     *
     * @throws MissingSessionKeyException
     * @throws SemaphoreException
     */
    public function testFailValidateNicknameAction(UnitTester $I): void
    {
        $I->getUserSession()->setApiUserData('token', 1, 1, 1);

        $request = new Request([], [], [], [], [], [], $this->requestValidateNicknameWrongContent);
        $request->headers->set('Content-Type', 'application/json');
        $response = $this->controller->validateNicknameAction($request);

        $expected = ['isAvailable' => false];
        $actual = Json::decode($response->getContent(), true);

        $I->assertEquals($expected, $actual);
        $I->assertEquals(1, $I->getWsRedis()->exists('semaphore:validateNickname:1'));
    }

    /**
     * @param UnitTester $I
     *
     * @throws MissingSessionKeyException
     * @throws SemaphoreException
     */
    public function testValidateNicknameActionManyRequestRestriction(UnitTester $I): void
    {
        $I->getUserSession()->setApiUserData('token', 1, 1, 1);
        $request1 = new Request([], [], [], [], [], [], $this->requestValidateNicknameContent);
        $request1->headers->set('Content-Type', 'application/json');
        $response = $this->controller->validateNicknameAction($request1);

        $expected = ['isAvailable' => true];
        $actual = Json::decode($response->getContent(), true);
        $I->assertEquals($expected, $actual);

        $request2 = new Request([], [], [], [], [], [], $this->requestValidateNicknameAltContent);
        $request2->headers->set('Content-Type', 'application/json');

        $I->expectThrowable(
            SemaphoreException::class,
            fn() => $this->controller->validateNicknameAction($request2)
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     * @throws MissingSessionKeyException
     * @throws ValidationException
     * @throws SemaphoreException
     */
    public function testSuccessUpdateAction(UnitTester $I): void
    {
        $I->getUserSession()->setApiUserData('token', 1, 1, 1);
        $profile = $this->getPlayerProfile();

        /** @var PlayerProfileService $playerProfileService */
        $playerProfileService = Stub::make(
            PlayerProfileService::class,
            ['update' => new GamificationProfile('id', $profile, 'signed')]
        );

        $this->stubsToVerify[] = $playerProfileService;

        $controller = new PlayerProfileController(
            $I->getContainer()->get(RequestValidator::class),
            $I->getContainer()->get(PlayerService::class),
            $playerProfileService,
            $this->semaphore,
            $I->getContainer()->get(SerializerInterface::class),
        );
        $controller->setContainer($I->getContainer());

        $request = new Request([], [], [], [], [], [], $this->requestUpdateContent);
        $request->headers->set('Content-Type', 'application/json');
        $response = $controller->updateAction($request);

        $expected = Json::decode(
            file_get_contents(__DIR__ . '/../../Fixture/Gamification/response/profile.response'),
            true
        );
        $actual = Json::decode($response->getContent(), true);

        $I->assertEquals($expected, $actual);
        $I->assertEquals(0, $I->getWsRedis()->exists('semaphore:updateProfile:5'));
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     * @throws MissingSessionKeyException
     * @throws SemaphoreException
     * @throws ValidationException
     */
    public function testSuccessBlockAction(UnitTester $I): void
    {
        $I->getUserSession()->setApiUserData('token', 1, 1, 1);
        $profile = $this->getPlayerProfile();
        $profile->setBlocked(true);

        /** @var PlayerProfileService $playerProfileService */
        $playerProfileService = Stub::make(
            PlayerProfileService::class,
            ['block' => new GamificationProfile('id', $profile, 'signed')]
        );

        $controller = new PlayerProfileController(
            $I->getContainer()->get(RequestValidator::class),
            $I->getContainer()->get(PlayerService::class),
            $playerProfileService,
            $this->semaphore,
            $I->getContainer()->get(SerializerInterface::class),
        );
        $controller->setContainer($I->getContainer());

        $request = new Request([], [], [], [], [], [], $this->requestBlockContent);
        $request->headers->set('Content-Type', 'application/json');
        $response = $controller->blockAction($request);

        $expected = Json::decode(
            file_get_contents(__DIR__ . '/../../Fixture/Gamification/response/profile.response'),
            true
        );
        $expected['blocked'] = true;

        $actual = Json::decode($response->getContent(), true);

        $I->assertEquals($expected, $actual);
        $I->assertEquals(0, $I->getWsRedis()->exists('semaphore:blockProfile:2'));
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     * @throws MissingSessionKeyException
     * @throws SemaphoreException
     * @throws ValidationException
     */
    public function testSuccessUnblockAction(UnitTester $I): void
    {
        $I->getUserSession()->setApiUserData('token', 1, 1, 1);
        $profile = $this->getPlayerProfile();

        /** @var PlayerProfileService $playerProfileService */
        $playerProfileService = Stub::make(
            PlayerProfileService::class,
            ['unblock' => new GamificationProfile('id', $profile, 'signed')]
        );

        $controller = new PlayerProfileController(
            $I->getContainer()->get(RequestValidator::class),
            $I->getContainer()->get(PlayerService::class),
            $playerProfileService,
            $this->semaphore,
            $I->getContainer()->get(SerializerInterface::class),
        );
        $controller->setContainer($I->getContainer());

        $request = new Request([], [], [], [], [], [], $this->requestBlockContent);
        $request->headers->set('Content-Type', 'application/json');
        $response = $controller->unblockAction();

        $expected = Json::decode(
            file_get_contents(__DIR__ . '/../../Fixture/Gamification/response/profile.response'),
            true
        );
        $actual = Json::decode($response->getContent(), true);

        $I->assertEquals($expected, $actual);
        $I->assertEquals(0, $I->getWsRedis()->exists('semaphore:unblockProfile:2'));
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     * @throws MissingSessionKeyException
     * @throws SemaphoreException
     * @throws ValidationException
     */
    public function testSuccessDeleteAction(UnitTester $I): void
    {
        $profile = $this->getPlayerProfile();

        /** @var PlayerProfileService $playerProfileService */
        $playerProfileService = Stub::make(
            PlayerProfileService::class,
            ['delete' => new GamificationProfile('id', $profile, 'signed')]
        );

        $controller = new PlayerProfileController(
            $I->getContainer()->get(RequestValidator::class),
            $I->getContainer()->get(PlayerService::class),
            $playerProfileService,
            $this->semaphore,
            $I->getContainer()->get(SerializerInterface::class),
        );
        $controller->setContainer($I->getContainer());

        $I->getUserSession()->setApiUserData('aaa', 2, 2, 3);

        $request = new Request([], [], [], [], [], [], $this->requestBlockContent);
        $request->headers->set('Content-Type', 'application/json');
        $response = $controller->deleteAction($request);

        $expected = Json::decode(
            file_get_contents(__DIR__ . '/../../Fixture/Gamification/response/profile.response'),
            true
        );
        $actual = Json::decode($response->getContent(), true);

        $I->assertEquals($expected, $actual);
        $I->assertEquals(0, $I->getWsRedis()->exists('semaphore:deleteProfile:2'));
    }

    /**
     * @param UnitTester $I
     */
    public function testNoAuth(UnitTester $I): void
    {
        $controller = $I->getContainer()->get(PlayerProfileController::class);

        $request = new Request([], [], [], [], [], [], $this->requestCreateContent);
        $request->headers->set('Content-Type', 'application/json');

        $I->expectThrowable(
            MissingSessionKeyException::class,
            fn() => $controller->createAction($request)
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testValidationIsCalled(UnitTester $I): void
    {
        $request = new Request();
        $I->expectThrowable(
            RequestValidationException::class,
            fn() => $this->controller->createAction($request)
        );

        $I->expectThrowable(
            RequestValidationException::class,
            fn() => $this->controller->updateAction($request)
        );

        $I->expectThrowable(
            RequestValidationException::class,
            fn() => $this->controller->blockAction($request)
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testCaptainUpCreateProfileError(UnitTester $I): void
    {
        $I->getUserSession()->setApiUserData('token', 1, 1, 1);

        /** @var PlayerProfileService $playerProfileService */
        $playerProfileService = Stub::make(
            PlayerProfileService::class,
            [
                'create' => function () {
                    throw new CaptainUpException('');
                }
            ]
        );
        $this->stubsToVerify[] = $playerProfileService;
        $controller = new PlayerProfileController(
            $I->getContainer()->get(RequestValidator::class),
            $I->getContainer()->get(PlayerService::class),
            $playerProfileService,
            $this->semaphore,
            $I->getContainer()->get(SerializerInterface::class),
        );
        $controller->setContainer($I->getContainer());

        $request = new Request([], [], [], [], [], [], $this->requestCreateContent);
        $request->headers->set('Content-Type', 'application/json');

        $I->expectThrowable(
            CaptainUpException::class,
            fn() => $controller->createAction($request)
        );

        $I->assertEquals(0, $I->getWsRedis()->exists('semaphore:createProfile:5'));
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testCaptainUpUpdateProfileError(UnitTester $I): void
    {
        $I->getUserSession()->setApiUserData('token', 1, 1, 1);

        /** @var PlayerProfileService $playerProfileService */
        $playerProfileService = Stub::make(
            PlayerProfileService::class,
            [
                'update' => function () {
                    throw new CaptainUpException('');
                }
            ]
        );
        $this->stubsToVerify[] = $playerProfileService;
        $controller = new PlayerProfileController(
            $I->getContainer()->get(RequestValidator::class),
            $I->getContainer()->get(PlayerService::class),
            $playerProfileService,
            $this->semaphore,
            $I->getContainer()->get(SerializerInterface::class),
        );
        $controller->setContainer($I->getContainer());

        $request = new Request([], [], [], [], [], [], $this->requestUpdateContent);
        $request->headers->set('Content-Type', 'application/json');

        $I->expectThrowable(
            CaptainUpException::class,
            fn() => $controller->updateAction($request)
        );

        $I->assertEquals(0, $I->getWsRedis()->exists('semaphore:updateProfile:5'));
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testCaptainBlockProfileError(UnitTester $I): void
    {
        $I->getUserSession()->setApiUserData('token', 1, 1, 1);

        /** @var PlayerProfileService $playerProfileService */
        $playerProfileService = Stub::make(
            PlayerProfileService::class,
            [
                'block' => function () {
                    throw new CaptainUpException('');
                }
            ]
        );
        $this->stubsToVerify[] = $playerProfileService;
        $controller = new PlayerProfileController(
            $I->getContainer()->get(RequestValidator::class),
            $I->getContainer()->get(PlayerService::class),
            $playerProfileService,
            $this->semaphore,
            $I->getContainer()->get(SerializerInterface::class),
        );
        $controller->setContainer($I->getContainer());

        $request = new Request([], [], [], [], [], [], $this->requestBlockContent);
        $request->headers->set('Content-Type', 'application/json');
        $I->expectThrowable(
            CaptainUpException::class,
            fn() => $controller->blockAction($request)
        );

        $I->assertEquals(0, $I->getWsRedis()->exists('semaphore:blockProfile:5'));
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testCaptainUnblockProfileError(UnitTester $I): void
    {
        $I->getUserSession()->setApiUserData('token', 1, 1, 1);

        /** @var PlayerProfileService $playerProfileService */
        $playerProfileService = Stub::make(
            PlayerProfileService::class,
            [
                'unblock' => function () {
                    throw new CaptainUpException('');
                }
            ]
        );
        $this->stubsToVerify[] = $playerProfileService;
        $controller = new PlayerProfileController(
            $I->getContainer()->get(RequestValidator::class),
            $I->getContainer()->get(PlayerService::class),
            $playerProfileService,
            $this->semaphore,
            $I->getContainer()->get(SerializerInterface::class),
        );
        $controller->setContainer($I->getContainer());

        $request = new Request([], [], [], [], [], []);
        $request->headers->set('Content-Type', 'application/json');
        $I->expectThrowable(
            CaptainUpException::class,
            fn() => $controller->unblockAction()
        );

        $I->assertEquals(0, $I->getWsRedis()->exists('semaphore:blockProfile:5'));
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testCaptainDeleteProfileError(UnitTester $I): void
    {
        $I->getUserSession()->setApiUserData('token', 1, 1, 1);

        /** @var PlayerProfileService $playerProfileService */
        $playerProfileService = Stub::make(
            PlayerProfileService::class,
            [
                'delete' => function () {
                    throw new CaptainUpException('');
                }
            ]
        );
        $this->stubsToVerify[] = $playerProfileService;
        $controller = new PlayerProfileController(
            $I->getContainer()->get(RequestValidator::class),
            $I->getContainer()->get(PlayerService::class),
            $playerProfileService,
            $this->semaphore,
            $I->getContainer()->get(SerializerInterface::class),
        );
        $controller->setContainer($I->getContainer());

        $request = new Request([], [], [], [], [], [], $this->requestDeleteContent);
        $request->headers->set('Content-Type', 'application/json');

        $I->expectThrowable(
            CaptainUpException::class,
            fn() => $controller->deleteAction($request)
        );

        $I->assertEquals(0, $I->getWsRedis()->exists('semaphore:deleteProfile:5'));
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testRaceCondition(UnitTester $I): void
    {
        $I->getUserSession()->setApiUserData('token', 1, 1, 1);

        /** @var PlayerProfileService $playerProfileService */
        $playerProfileService = Stub::make(
            PlayerProfileService::class,
            ['create' => Stub\Expected::never()]
        );
        $this->stubsToVerify[] = $playerProfileService;
        $controller = $I->getContainer()->get(PlayerProfileController::class);

        $this->semaphore->acquireLock('createProfile:1');
        $I->assertEquals(1, $I->getWsRedis()->exists('semaphore:createProfile:1'));

        $request = new Request([], [], [], [], [], [], $this->requestCreateContent);
        $request->headers->set('Content-Type', 'application/json');

        $I->expectThrowable(
            new SemaphoreException('TOO_MANY_REQUESTS'),
            fn() => $controller->createAction($request)
        );

        $I->assertEquals(1, $I->getWsRedis()->exists('semaphore:createProfile:1'));
    }

    /**
     * @return PlayerProfile
     */
    private function getPlayerProfile(): PlayerProfile
    {
        return (new PlayerProfile())
            ->setName('name')
            ->setExternalId('U3453D17021621Q3279052K003000871')
            ->setAvatarUrl('image')
            ->setBlocked(false);
    }
}
