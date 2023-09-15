<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\Gamification;

use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Entity\PlayerProfile;
use Acme\SymfonyDb\Entity\PlayerProfileStateHistory;
use Codeception\Stub;
use Codeception\Stub\Expected;
use CoreBundle\Exception\ValidationException;
use CoreBundle\Service\RepositoryProviderInterface;
use DateTimeImmutable;
use Doctrine\ORM\Tools\ToolsException;
use Exception;
use GamesApiBundle\DataObject\Gamification\CreateProfileResponse;
use GamesApiBundle\DataObject\Gamification\GamificationProfile;
use GamesApiBundle\Event\Gamification\PostPlayerProfileCreationEvent;
use GamesApiBundle\Exception\Gamification\CaptainUpException;
use GamesApiBundle\Service\Gamification\AvatarProvider;
use GamesApiBundle\Service\Gamification\AvatarValidator;
use GamesApiBundle\Service\Gamification\PlayerProfileService;
use GamesApiBundle\Service\Gamification\RequestHandler;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class PlayerProfileServiceCest
 */
final class PlayerProfileServiceCest extends AbstractUnitTest
{
    protected array $tables = [
        PlayerProfile::class,
        PlayerProfileStateHistory::class,
    ];

    private RepositoryProviderInterface $repositoryProvider;
    private AvatarProvider $avatarProvider;
    private AvatarValidator $avatarValidator;
    private EventDispatcherInterface $eventDispatcher;

    /**
     * {@inheritDoc}
     */
    protected function setUpFixtures(): void
    {
        $this->fixtureBoostrapper->addPlayers(1);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $container = $I->getContainer();

        /** @var RepositoryProviderInterface $repositoryProvider */
        $repositoryProvider = $container->get(RepositoryProviderInterface::class);
        $this->repositoryProvider = $repositoryProvider;
        /** @var AvatarProvider $avatarProvider */
        $avatarProvider = $container->get(AvatarProvider::class);
        $this->avatarProvider = $avatarProvider;
        /** @var AvatarValidator $avatarValidator */
        $avatarValidator = $container->get(AvatarValidator::class);
        $this->avatarValidator = $avatarValidator;
        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $container->get(EventDispatcherInterface::class);
        $this->eventDispatcher = $eventDispatcher;

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['SERVER_NAME' => 'Acmetv.eu']
        );

        $I->getRequestStack()->push($request);
    }
    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     * @throws CaptainUpException
     */
    public function testSuccessCreate(UnitTester $I): void
    {
        $dispatcher = $I->getEventDispatcher();

        $service = new PlayerProfileService(
            Stub::make(
                RequestHandler::class,
                [
                    'createProfile' => new CreateProfileResponse('id', 'external-id', 'signed'),
                    'profileCreated' => Expected::once()
                ],
            ),
            $this->repositoryProvider,
            $this->avatarProvider,
            $this->avatarValidator,
            $dispatcher,
            []
        );

        /** @var Player $player */
        $player = $this->getEntityByReference('player:1');
        $partner = $player->getPartner();
        $partner->setGamificationEnabled(true);

        $player->getProfile();
        $profile = $service->create($player, 'ff', 'Acme.local');
        $I->assertEquals(
            'ff',
            $profile->getName()
        );

        $event = $dispatcher->events[0];

        $I->assertInstanceOf(PostPlayerProfileCreationEvent::class, $event);
        $I->assertEquals(1, $event->getPlayerId());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     * @throws CaptainUpException
     * @throws Exception
     */
    public function testSuccessUpdate(UnitTester $I): void
    {
        $profile = $this->getPlayerProfile();

        /** @var RequestHandler $requestHandler */
        $requestHandler = Stub::make(
            RequestHandler::class,
            [
                'updateProfile' =>  new GamificationProfile('id', $profile, 'signed'),
                'retrieveProfileLevel' => 'blalblabla',
            ]
        );

        $service = new PlayerProfileService(
            $requestHandler,
            $this->repositoryProvider,
            $this->avatarProvider,
            $this->avatarValidator,
            $this->eventDispatcher,
            ['blalblabla' => 1, 'blablabla' => 2]
        );

        /** @var Player $player */
        $player = $this->getEntityByReference('player:1');
        $partner = $player->getPartner();
        $partner->setGamificationEnabled(true);
        $player->setProfile($profile);
        $profile->setPlayer($player);

        $profile = $service->update($player, 'avatar_1', 'Acme.local');
        $I->assertEquals('name', $profile->getName());

        $em = $this->getEntityManager();

        /** @var PlayerProfile $profile */
        $profile = $em->getRepository(PlayerProfile::class)->find(1);
        $I->assertEquals('name', $profile->getName());
        $I->assertEquals(
            'https://static.Acme.tv/gamification/player_profile_avatars/avatar_1.jpg',
            $profile->getAvatarUrl()
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testCreateOnExistingProfile(UnitTester $I): void
    {
        /** @var RequestHandler $requestHandler */
        $requestHandler = Stub::make(
            RequestHandler::class,
            [
                'createProfile' => new CreateProfileResponse('id', 'external-id', 'signed'),
            ],
        );

        $dispatcher = $I->getEventDispatcher();

        $service = new PlayerProfileService(
            $requestHandler,
            $this->repositoryProvider,
            $this->avatarProvider,
            $this->avatarValidator,
            $dispatcher,
            []
        );

        $player = (new Player())
            ->setProfile(new PlayerProfile())
            ->setPartner((new Partner())->setApiCode('api-code')->setGamificationEnabled(true))
            ->setTag('existing')
            ->setTaggedAt(new DateTimeImmutable())
        ;

        $I->assertEmpty($dispatcher->events);

        $I->expectThrowable(
            new ValidationException('PROFILE_ALREADY_EXISTS'),
            fn() => $service->create($player, 'ff', 'Acme.local')
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testCreateWhenGamificationDisabled(UnitTester $I): void
    {
        /** @var PlayerProfileService $service */
        $service = $I->getContainer()->get(PlayerProfileService::class);

        $player = (new Player())
            ->setPartner((new Partner())->setApiCode('api-code')->setGamificationEnabled(false))
            ->setTag('existing')
            ->setTaggedAt(new DateTimeImmutable())
        ;

        $I->expectThrowable(
            new ValidationException('GAMIFICATION_DISABLED'),
            fn() => $service->create($player, 'ff', 'Acme.local')
        );

        $I->expectThrowable(
            new ValidationException('GAMIFICATION_DISABLED'),
            fn() => $service->block($player, 'reason')
        );

        $I->expectThrowable(
            new ValidationException('GAMIFICATION_DISABLED'),
            fn() => $service->unblock($player)
        );

        $I->expectThrowable(
            new ValidationException('GAMIFICATION_DISABLED'),
            fn() => $service->delete($player, 'reason')
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testProfileNotExists(UnitTester $I): void
    {
        $service = $I->getContainer()->get(PlayerProfileService::class);

        $player = (new Player())
            ->setPartner((new Partner())->setApiCode('api-code')->setGamificationEnabled(true))
            ->setTag('existing')
            ->setTaggedAt(new DateTimeImmutable())
        ;

        $I->expectThrowable(
            new ValidationException('PROFILE_NOT_EXISTS'),
            fn() => $service->block($player, 'reason')
        );

        $I->expectThrowable(
            new ValidationException('PROFILE_NOT_EXISTS'),
            fn() => $service->unblock($player)
        );

        $I->expectThrowable(
            new ValidationException('PROFILE_NOT_EXISTS'),
            fn() => $service->delete($player, 'reason')
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testBlockWhenProfileAlreadyBlocked(UnitTester $I): void
    {
        $profile = $this->getPlayerProfile();
        $profile->setBlocked(true);
        $service = new PlayerProfileService(
            Stub::make(
                RequestHandler::class,
                [
                    'blockProfile' => new GamificationProfile('id', $profile, 'signed'),
                ]
            ),
            $this->repositoryProvider,
            $this->avatarProvider,
            $this->avatarValidator,
            $this->eventDispatcher,
            []
        );

        $profile = (new PlayerProfile())
            ->setName('name')
            ->setAvatarUrl('https://some-image')
            ->setBlocked(true);

        /** @var Player $player */
        $player = $this->getEntityByReference('player:1');
        $partner = $player->getPartner();
        $partner->setGamificationEnabled(true);
        $player->setProfile($profile);
        $profile->setPlayer($player);

        $I->expectThrowable(
            new ValidationException('PROFILE_ALREADY_BLOCKED'),
            fn() => $service->block($player, 'reason')
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testUnblockWhenProfileAlreadyUnblocked(UnitTester $I): void
    {
        $profile = $this->getPlayerProfile();
        $profile->setBlocked(false);

        $service = new PlayerProfileService(
            Stub::make(
                RequestHandler::class,
                [
                    'blockProfile' => new GamificationProfile('id', $profile, 'signed'),
                ]
            ),
            $this->repositoryProvider,
            $this->avatarProvider,
            $this->avatarValidator,
            $this->eventDispatcher,
            []
        );

        $profile = (new PlayerProfile())
            ->setName('name')
            ->setAvatarUrl('https://some-image')
            ->setBlocked(false);

        /** @var Player $player */
        $player = $this->getEntityByReference('player:1');
        $partner = $player->getPartner();
        $partner->setGamificationEnabled(true);
        $player->setProfile($profile);
        $profile->setPlayer($player);

        $I->expectThrowable(
            new ValidationException('PROFILE_ALREADY_UNBLOCKED'),
            fn() => $service->unblock($player)
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     * @throws ValidationException
     */
    public function testBlockSuccess(UnitTester $I): void
    {
        $profile = $this->getPlayerProfile();

        $service = new PlayerProfileService(
            Stub::make(
                RequestHandler::class,
                [
                    'blockProfile' => new GamificationProfile('id', $profile, 'signed'),
                ]
            ),
            $this->repositoryProvider,
            $this->avatarProvider,
            $this->avatarValidator,
            $this->eventDispatcher,
            []
        );

        /** @var Player $player */
        $player = $this->getEntityByReference('player:1');
        $partner = $player->getPartner();
        $partner->setGamificationEnabled(true);
        $player->setProfile($profile);
        $profile->setPlayer($player);

        $profile = $service->block($player, 'reason');
        $I->assertEquals('name', $profile->getName());
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     * @throws ValidationException
     */
    public function testUnblockSuccess(UnitTester $I): void
    {
        $profile = $this->getPlayerProfile();
        $profile->setBlocked(true);
        $service = new PlayerProfileService(
            Stub::make(
                RequestHandler::class,
                [
                    'unblockProfile' => new GamificationProfile('id', $profile, 'signed'),
                ]
            ),
            $this->repositoryProvider,
            $this->avatarProvider,
            $this->avatarValidator,
            $this->eventDispatcher,
            []
        );

        /** @var Player $player */
        $player = $this->getEntityByReference('player:1');
        $partner = $player->getPartner();
        $partner->setGamificationEnabled(true);
        $player->setProfile($profile);
        $profile->setPlayer($player);

        $profile = $service->unblock($player);
        $I->assertEquals('name', $profile->getName());
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     * @throws ValidationException
     */
    public function testDeleteSuccess(UnitTester $I): void
    {
        $profile = $this->getPlayerProfile();

        $service = new PlayerProfileService(
            Stub::make(
                RequestHandler::class,
                [
                    'deleteProfile' => new GamificationProfile('id', $profile, 'signed'),
                ]
            ),
            $this->repositoryProvider,
            $this->avatarProvider,
            $this->avatarValidator,
            $this->eventDispatcher,
            []
        );

        $player = $this->getEntityByReference('player:1');
        $partner = $player->getPartner();
        $partner->setGamificationEnabled(true);
        $player->setProfile($profile);
        $profile->setPlayer($player);

        $profile = $service->delete($player, 'reason');
        $I->assertEquals('name', $profile->getName());
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     * @throws ValidationException
     */
    public function testSavingCreatedStateToDataBaseSuccess(UnitTester $I): void
    {
        $profile = $this->getPlayerProfile();

        $service = new PlayerProfileService(
            Stub::make(
                RequestHandler::class,
                [
                    'createProfile' => new CreateProfileResponse('id', 'external-id', 'signed'),
                    'profileCreated' => null
                ],
            ),
            $this->repositoryProvider,
            $this->avatarProvider,
            $this->avatarValidator,
            $this->eventDispatcher,
            []
        );

        $player = $this->getEntityByReference('player:1');
        $partner = $player->getPartner();
        $partner->setGamificationEnabled(true);

        $player->getProfile();
        $profile = $service->create($player, 'ff', 'Acme.local');

        $em = $this->getEntityManager();

        /** @var PlayerProfileStateHistory $state */
        $state = $em->getRepository(PlayerProfileStateHistory::class)->find(1);

        $I->assertEquals(1, $state->getId());
        $I->assertEquals('ff', $profile->getName());
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     * @throws ValidationException
     */
    public function testSavingBlockedStateToDataBaseSuccess(UnitTester $I): void
    {
        $profile = $this->getPlayerProfile();
        $service = new PlayerProfileService(
            Stub::make(
                RequestHandler::class,
                [
                    'blockProfile' => new GamificationProfile('id', $profile, 'signed'),
                ]
            ),
            $this->repositoryProvider,
            $this->avatarProvider,
            $this->avatarValidator,
            $this->eventDispatcher,
            []
        );

        /** @var Player $player */
        $player = $this->getEntityByReference('player:1');
        $partner = $player->getPartner();
        $partner->setGamificationEnabled(true);
        $player->setProfile($profile);
        $profile->setPlayer($player);

        $profile = $service->block($player, 'not_understand');

        $em = $this->getEntityManager();

        /** @var PlayerProfileStateHistory $state */
        $state = $em->getRepository(PlayerProfileStateHistory::class)->find(1);

        $I->assertEquals('not_understand', $state->getUpdateReason());
        $I->assertEquals(1, $state->getId());
        $I->assertEquals('name', $profile->getName());
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     * @throws ValidationException
     */
    public function testSavingUnblockedStateToDataBaseSuccess(UnitTester $I): void
    {
        $profile = $this->getPlayerProfile();
        $profile->setBlocked(true);
        $service = new PlayerProfileService(
            Stub::make(
                RequestHandler::class,
                [
                    'unblockProfile' => new GamificationProfile('id', $profile, 'signed'),
                ]
            ),
            $this->repositoryProvider,
            $this->avatarProvider,
            $this->avatarValidator,
            $this->eventDispatcher,
            []
        );

        /** @var Player $player */
        $player = $this->getEntityByReference('player:1');
        $partner = $player->getPartner();
        $partner->setGamificationEnabled(true);
        $player->setProfile($profile);
        $profile->setPlayer($player);

        $profile = $service->unblock($player);

        $em = $this->getEntityManager();

        /** @var PlayerProfileStateHistory $state */
        $state = $em->getRepository(PlayerProfileStateHistory::class)->find(1);

        $I->assertNull($state->getUpdateReason());
        $I->assertEquals(1, $state->getId());
        $I->assertEquals('name', $profile->getName());
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     * @throws ValidationException
     */
    public function testSavingDeletedStateToDataBaseSuccess(UnitTester $I): void
    {
        $profile = $this->getPlayerProfile();

        $service = new PlayerProfileService(
            Stub::make(
                RequestHandler::class,
                [
                    'deleteProfile' => new GamificationProfile('id', $profile, 'signed'),
                ]
            ),
            $this->repositoryProvider,
            $this->avatarProvider,
            $this->avatarValidator,
            $this->eventDispatcher,
            []
        );

        $player = $this->getEntityByReference('player:1');
        $partner = $player->getPartner();
        $partner->setGamificationEnabled(true);
        $player->setProfile($profile);
        $profile->setPlayer($player);

        $profile = $service->delete($player, 'other');

        $em = $this->getEntityManager();

        /** @var PlayerProfileStateHistory $state */
        $state = $em->getRepository(PlayerProfileStateHistory::class)->find(1);

        $I->assertEquals('other', $state->getUpdateReason());
        $I->assertEquals(1, $state->getId());
        $I->assertEquals('name', $profile->getName());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     * @throws \ReflectionException
     * @throws CaptainUpException
     */
    public function testDeletingPlayerProfileSuccess(UnitTester $I): void
    {
        $profile = $this->getPlayerProfile();

        $service = new PlayerProfileService(
            Stub::make(
                RequestHandler::class,
                [
                    'createProfile' => new CreateProfileResponse('id', 'external-id', 'signed'),
                    'profileCreated' => null
                ],
            ),
            $this->repositoryProvider,
            $this->avatarProvider,
            $this->avatarValidator,
            $this->eventDispatcher,
            []
        );

        $player = $this->getEntityByReference('player:1');
        $partner = $player->getPartner();
        $partner->setGamificationEnabled(true);

        $em = $this->getEntityManager();

        $player->getProfile();
        $service->create($player, 'ff', 'Acme.tv');
        $profile = $em->getRepository(PlayerProfile::class)->find(1);
        $I->assertEquals(1, $profile->getId());

        $service = new PlayerProfileService(
            Stub::make(
                RequestHandler::class,
                [
                    'deleteProfile' => new GamificationProfile('id', $profile, 'signed'),
                ]
            ),
            $this->repositoryProvider,
            $this->avatarProvider,
            $this->avatarValidator,
            $this->eventDispatcher,
            []
        );

        $player = $this->getEntityByReference('player:1');
        $partner = $player->getPartner();
        $partner->setGamificationEnabled(true);
        $player->setProfile($profile);
        $profile->setPlayer($player);

        $service->delete($player, 'other');
        $profile = $em->getRepository(PlayerProfile::class)->find(1);
        $I->assertNull($profile);
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

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return new Request(
            [],
            [],
            [],
            [],
            [],
            ['SERVER_NAME' => 'Acmetv.eu']
        );
    }
}
