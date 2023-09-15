<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Controller\Gamification;

use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\PlayerProfile;
use Acme\SymfonyDb\Entity\Player;
use CoreBundle\Exception\MissingSessionKeyException;
use Doctrine\ORM\Tools\ToolsException;
use GamesApiBundle\Controller\Gamification\GamificationSettingsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\GamesApiBundle\Fixture\PlayerFixture;
use SymfonyTests\UnitTester;

/**
 * Class GamificationSettingsControllerCest
 */
final class GamificationSettingsControllerCest extends AbstractUnitTest
{
    private Partner $partner;
    private GamificationSettingsController $controller;

    protected array $tables
        = [
            Player::class,
            Partner::class,
            PlayerProfile::class,
        ];

    protected array $fixtures
        = [
            PlayerFixture::class,
        ];

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $container = $I->getContainer();

        /** @var GamificationSettingsController $controller */
        $controller = $container->get(GamificationSettingsController::class);
        $this->controller = $controller;

        /** @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        $this->partner = $partner;
    }

    /**
     * Setup.
     */
    protected function setUpFixtures(): void
    {
        $this->fixtureBoostrapper->addPartners(1);
    }

    /**
     * @param UnitTester $I
     */
    public function testNoPlayerInSessionGamificationEnabled(UnitTester $I): void
    {
        $this->partner->setGamificationEnabled(true);

        $I->expectThrowable(
            new MissingSessionKeyException('NO_AUTH'),
            fn() => $this->controller->settingsAction()
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testNoPlayerInSessionGamificationDisabled(UnitTester $I): void
    {
        $this->partner->setGamificationEnabled(false);

        $I->expectThrowable(
            new MissingSessionKeyException('NO_AUTH'),
            fn() => $this->controller->settingsAction()
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testPlayerInSessionGamificationDisabled(UnitTester $I): void
    {
        $this->partner->setGamificationEnabled(false);

        $I->getUserSession()->setApiUserData('any', 1, 1, 1)
        ;

        $I->expectThrowable(
            new NotFoundHttpException(),
            fn() => $this->controller->settingsAction()
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws MissingSessionKeyException
     */
    public function testGamificationEnabledWithProfile(UnitTester $I): void
    {
        /** @var Player $player */
        $player = $this->getEntityByReference('player:1');
        $playerProfile = (new PlayerProfile())
            ->setPlayer($player)
            ->setExternalId('U3453D17021621Q3279052K003000871')
            ->setName('some-name')
            ->setAvatarUrl('https://some-image')
            ->setBlocked(true);
        $player->setProfile($playerProfile);

        $entityManager = $this->getEntityManager();

        $entityManager->persist($player);
        $entityManager->flush();

        $this->partner->setGamificationEnabled(true);

        $I->getUserSession()->setApiUserData('any', 1, 1, 1)
        ;

        $response = $this->controller->settingsAction();

        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/Gamification/GamificationSettings/enabled-with-profile.json',
            $response->getContent()
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws MissingSessionKeyException
     */
    public function testGamificationEnabledNoProfile(UnitTester $I): void
    {
        $this->partner->setGamificationEnabled(true);

        $I->getUserSession()->setApiUserData('any', 1, 1, 1)
        ;

        $response = $this->controller->settingsAction();

        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/Gamification/GamificationSettings/enabled-no-profile.json',
            $response->getContent()
        );
    }
}
