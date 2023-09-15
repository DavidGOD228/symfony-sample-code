<?php

namespace SymfonyTests\Unit\GamesApiBundle\Controller;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\CashbackConfig;
use Acme\SymfonyDb\Entity\Country;
use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\CurrencyButtonAmount;
use Acme\SymfonyDb\Entity\FavoriteOdd;
use Acme\SymfonyDb\Entity\GameItem;
use Acme\SymfonyDb\Entity\GroupingOdd;
use Acme\SymfonyDb\Entity\Logo;
use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyDb\Entity\OddGroup;
use Acme\SymfonyDb\Entity\OddValue;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\PartnerDisabledOdd;
use Acme\SymfonyDb\Entity\PartnerFirewall;
use Acme\SymfonyDb\Entity\PartnerLogoLink;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Entity\PlayerProfile;
use Acme\SymfonyDb\Entity\Promotion;
use Acme\SymfonyDb\Entity\PromotionAmountByType;
use Acme\SymfonyDb\Entity\PromotionEnabledFor;
use Acme\SymfonyDb\Entity\Setting;
use Acme\SymfonyDb\Entity\StreamPreset;
use Acme\SymfonyDb\Entity\StreamPresetOption;
use Acme\SymfonyDb\Entity\TaxScheme;
use Acme\SymfonyDb\Entity\TaxSchemeRate;
use Acme\SymfonyDb\Entity\User;
use Acme\SymfonyDb\Entity\UserRole;
use Codeception\Stub;
use CoreBundle\Service\NodeService;
use Doctrine\ORM\Tools\ToolsException;
use GamesApiBundle\Controller\AuthController;
use GamesApiBundle\Exception\AnonymousAuthenticationException;
use GamesApiBundle\Service\AuthService;
use GamesApiBundle\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\CoreBundle\Fixture\CountryFixture;
use SymfonyTests\Unit\CoreBundle\Fixture\PartnerFirewallFixture;
use SymfonyTests\Unit\CoreBundle\Fixture\SettingFixture;
use SymfonyTests\Unit\CoreBundle\Fixture\StreamPresetFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\CurrencyButtonAmountFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\CurrencyFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\FavoriteOddFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\LogoFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Matka\OddFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\PartnerLogoLinkFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\PlayerFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\StreamPresetOptionFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\TaxSchemeFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\TaxSchemeRateFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\UserFixture;
use SymfonyTests\UnitTester;
use Acme\SymfonyRequest\Request;
use Exception;

/**
 * Class AuthControllerCest
 */
class AuthControllerCest extends AbstractUnitTest
{
    private const GAME_IDS = [
        GameDefinition::LUCKY_7,
        GameDefinition::LUCKY_5,
        GameDefinition::WHEEL
    ];

    private const GAME_IDS_WITH_MATKA = [
        GameDefinition::LUCKY_7,
        GameDefinition::LUCKY_5,
        GameDefinition::WHEEL,
        GameDefinition::MATKA
    ];

    protected array $tables = [
        TaxScheme::class,
        TaxSchemeRate::class,
        CurrencyButtonAmount::class,
        User::class,
        UserRole::class,
        Logo::class,
        PartnerLogoLink::class,
        Currency::class,
        Promotion::class,
        PromotionEnabledFor::class,
        PromotionAmountByType::class,
        CashbackConfig::class,
        Odd::class,
        PartnerDisabledOdd::class,
        OddValue::class,
        OddGroup::class,
        GroupingOdd::class,
        GameItem::class,
        Player::class,
        Country::class,
        PartnerFirewall::class,
        FavoriteOdd::class,
        Setting::class,
        StreamPreset::class,
        StreamPresetOption::class,
        PlayerProfile::class,
    ];

    protected array $fixtures = [
        TaxSchemeFixture::class,
        TaxSchemeRateFixture::class,
        CurrencyButtonAmountFixture::class,
        UserFixture::class,
        LogoFixture::class,
        PartnerLogoLinkFixture::class,
        CurrencyFixture::class,
        PlayerFixture::class,
        CountryFixture::class,
        PartnerFirewallFixture::class,
        FavoriteOddFixture::class,
        SettingFixture::class,
        StreamPresetFixture::class,
        StreamPresetOptionFixture::class,
        OddFixture::class,
    ];

    private string $content = '{
                "partner_code": "test1",
                "token": "externalToken1",
                "language": "en",
                "timezone": "3"
            }';

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $container = $I->getContainer();

        $nodeService = Stub::makeEmpty(
            NodeService::class,
            [
                'getCurrentWebsocketUrl' => 'socket.url',
                'getNodeSocketToken' => 'socket_token',
            ]
        );

        $container->set(NodeService::class, $nodeService);


        /** @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        $partner->setApiShowBalance(true);
        $this->partner = $partner;
    }

    /**
     * Setup.
     */
    protected function setUpFixtures(): void
    {
        $this->fixtureBoostrapper->addGames(self::GAME_IDS_WITH_MATKA);
        $this->fixtureBoostrapper->addPartners(2, true);
        $this->fixtureBoostrapper->addOdds(self::GAME_IDS, true, 1);
        $this->fixtureBoostrapper->addGameItemFixture(self::GAME_IDS_WITH_MATKA, false);
        $this->fixtureBoostrapper->addRunRounds(self::GAME_IDS);
        $this->fixtureBoostrapper->addLanguages(['en']);
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testInvalidContentType(UnitTester $I): void
    {
        $container = $I->getContainer();

        /* @var AuthService $service */
        $service = Stub::make(
            AuthService::class,
            [
                'clearApiUserData' => null,
            ]
        );
        $container->set(AuthService::class, $service);

        /** @var AuthController $controller */
        $controller = $container->get(AuthController::class);

        $request = new Request([], [], [], [], [], ['REMOTE_ADDR' => '192.168.0.1'], '{}');

        $response = $controller->indexAction($request);
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../Fixture/Auth/bad-request.json',
            $response->getContent()
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testNoTokenFromApi(UnitTester $I): void
    {
        $container = $I->getContainer();

        /* @var AuthService $service */
        $service = Stub::make(
            AuthService::class,
            [
                'login' => function ()
                {
                    throw new AuthenticationException('please_login', Response::HTTP_NOT_FOUND);
                },
                'clearApiUserData' => null,
            ]
        );
        $container->set(AuthService::class, $service);

        /** @var AuthController $controller */
        $controller = $container->get(AuthController::class);

        $request = new Request([], [], [], [], [], ['REMOTE_ADDR' => '192.168.0.1'], $this->content);
        $request->headers->set('Content-Type', 'application/json');
        $response = $controller->indexAction($request);

        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../Fixture/Auth/empty-token.json',
            $response->getContent()
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testAnonymousTokenAuth(UnitTester $I): void
    {
        $container = $I->getContainer();

        /* @var AuthService $service */
        $service = Stub::make(
            AuthService::class,
            [
                'login' => function ()
                {
                    throw new AnonymousAuthenticationException('ANONYMOUS_TOKEN');
                },
                'clearApiUserData' => null,
            ]
        );
        $container->set(AuthService::class, $service);

        /** @var AuthController $controller */
        $controller = $container->get(AuthController::class);

        $request = new Request([], [], [], [], [], ['REMOTE_ADDR' => '192.168.0.1'], $this->content);
        $request->headers->set('Content-Type', 'application/json');
        $response = $controller->indexAction($request);

        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../Fixture/Auth/anonymous-token.json',
            $response->getContent()
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testExistingPlayer(UnitTester $I): void
    {
        $I->getUserSession()->setSessionId(1);

        $container = $I->getContainer();
        /** @var AuthController $controller */
        $controller = $container->get(AuthController::class);

        $this->setAuthPlayerWithBalance($I);

        /** @var Player $player */
        $player = $this->getEntityByReference('player:1');
        $player->setExternalCode('222')->setExternalToken('222');

        $this->getEntityManager()->persist($player);
        $this->getEntityManager()->flush();

        $request = $this->getRequest();

        $response = $controller->indexAction($request);

        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../Fixture/Auth/existing-player.json',
            $response->getContent()
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testExistingPlayerWithTag(UnitTester $I): void
    {
        $I->getUserSession()->setSessionId(1);

        $container = $I->getContainer();
        /** @var AuthController $controller */
        $controller = $container->get(AuthController::class);

        $this->setAuthPlayerWithBalance($I);

        /** @var Player $player */
        $player = $this->getEntityByReference('player:1');
        $player->setExternalCode('222')
            ->setExternalToken('222')
            ->setTag('ExampleTag');

        $this->getEntityManager()->persist($player);
        $this->getEntityManager()->flush();

        $request = $this->getRequest();

        $response = $controller->indexAction($request);

        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../Fixture/Auth/player-with-tag.json',
            $response->getContent()
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testInvalidRequestContent(UnitTester $I): void
    {
        $container = $I->getContainer();
        /** @var AuthController $controller */
        $controller = $container->get(AuthController::class);

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['REMOTE_ADDR' => '192.168.0.1'],
            <<<JSON
{
    "foo" : "bar"
}
JSON
        );
        $request->headers->set('Content-Type', 'application/json');

        $response = $controller->indexAction($request);
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../Fixture/Auth/invalid-request-content.json',
            $response->getContent()
        );
    }
    /**
     * @return Request
     *
     * @throws Exception
     */
    private function getRequest() : Request
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['REMOTE_ADDR' => '192.168.0.1'],
            $this->content
        );

        $request->headers->set('Content-Type', 'application/json');

        return $request;
    }

    /**
     * @param UnitTester $I
     */
    private function setAuthPlayerWithBalance(UnitTester $I): void
    {
        // Set balance for player
        $I->getUserSession()->setApiUserData('any', 1, 1, 1);
        $I->getCacheRedis()->set('directapi_balance_1', '123.45€');
    }
}
