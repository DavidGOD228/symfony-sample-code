<?php

namespace SymfonyTests\Unit\GamesApiBundle\Service;

use Acme\SymfonyDb\Entity\Country;
use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\PartnerFirewall;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Entity\User;
use Acme\SymfonyDb\Entity\UserRole;
use Acme\WebApi\Api\WebApiNone;
use Acme\WebApi\Structure\AccountDetails;
use Codeception\Stub;
use CoreBundle\Exception\MissingSessionKeyException;
use GamesApiBundle\Exception\AnonymousAuthenticationException;
use GamesApiBundle\Exception\AuthenticationException;
use GamesApiBundle\Request\AuthParams;
use GamesApiBundle\Service\AuthService;
use PartnerApiBundle\Service\PartnerWebApiProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use SymfonyTests\_support\CoreBundleMock\PartnerWebApiProviderMock;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\CoreBundle\Fixture\CountryFixture;
use SymfonyTests\Unit\CoreBundle\Fixture\PartnerFirewallFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\CurrencyFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\UserFixture;
use SymfonyTests\UnitTester;

/**
 * Class AuthServiceCest
 */
class AuthServiceCest extends AbstractUnitTest
{
    /**
     * @var array
     */
    protected array $tables = [
        Country::class,
        Player::class,
        PartnerFirewall::class,
        UserRole::class,
        User::class,
        Currency::class
    ];

    protected array $fixtures = [
        UserFixture::class,
        CountryFixture::class,
        PartnerFirewallFixture::class,
        CurrencyFixture::class
    ];

    private PartnerWebApiProviderMock $webApiProviderMock;
    private AuthService $service;

    private array $body = [
        'partner_code' => 'test',
        'token' => '123',
        'language' => 'en',
        'timezone' => '3',
        'is_mobile' => true,
        'odds_format' => 'fractional',
    ];

    /** @inheritDoc */
    protected function setUpFixtures(): void
    {
        $this->fixtureBoostrapper->addLanguages(['en', 'ru']);
        $this->fixtureBoostrapper->addPartners(2);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->webApiProviderMock = $I->getContainer()->get(PartnerWebApiProvider::class);
        $this->service = $I->getContainer()->get(AuthService::class);
    }

    /**
     * @param UnitTester $I
     *
     * @throws MissingSessionKeyException
     * @throws \CoreBundle\Exception\ValidationException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testLogin(UnitTester $I): void
    {
        $body = $this->body;
        $params = new AuthParams($body);
        $I->expectThrowable(
            new AuthenticationException(
                'game_is_turned_off_contact_administrator',
                Response::HTTP_NOT_FOUND
            ),
            function () use ($params)
            {
                $this->service->login($params, '192.168.0.1');
            }
        );

        $body = $this->body;
        $body['partner_code'] = 'test1';
        $params = new AuthParams($body);

        $player = $this->service->login($params, '192.168.0.1');
        $I->assertEquals(1, $player->getId());

        $body = $this->body;
        $body['language'] = 'lt';
        $body['partner_code'] = 'test1';
        $params = new AuthParams($body);
        $this->service->login($params, '192.168.0.1');
        $I->assertEquals(1, $I->getUserSession()->getLanguageId(), 'Should be fallback to EN');

        $player = $this->service->login($params, '192.168.0.1');

        $I->assertEquals(1, $player->getId());
    }

    /**
     * @param UnitTester $I
     *
     * @throws MissingSessionKeyException
     * @throws \CoreBundle\Exception\ValidationException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testLoginFromReverseIntegration(UnitTester $I): void
    {
        /** @var SessionStorageInterface $sessionStorage */
        $sessionStorage = $I->getContainer()->get(SessionStorageInterface::class);

        $sessionStorage->setId('abc123def');
        $sessionStorage->start();
        $sessionStorage->setSessionData(['client_currency_id' => 1]);
        $sessionStorage->save();
        $sessionStorage->setId('');

        $body = $this->body;
        $body['partner_code'] = 'test1';
        $params = new AuthParams($body);

        $this->service->login($params, '192.168.0.1');
        $I->assertNotEmpty($I->getUserSession()->getSessionId());
        $I->assertNotEquals('abc123def', $I->getUserSession()->getSessionId());
        $sessionStorage->save();
        $I->getUserSession()->setSessionId('');

        $body = $this->body;
        $body['partner_code'] = 'test1';
        $body['sid'] = 'abc123def';
        $params = new AuthParams($body);
        $this->service->login($params, '192.168.0.1');
        $I->assertEquals('abc123def', $I->getUserSession()->getSessionId());
        $I->assertEquals(1, $I->getUserSession()->getCurrencyId());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \CoreBundle\Exception\ValidationException
     */
    public function testLoginToken(UnitTester $I): void
    {
        $body = $this->body;
        $body['partner_code'] = 'test1';
        $params = new AuthParams($body);

        $this->webApiProviderMock->getPartnerApiMock()->setNewToken('');

        $expectedException = new AuthenticationException('please_login', Response::HTTP_NOT_FOUND);

        $I->expectThrowable(
            $expectedException,
            function () use ($params)
            {
                $this->service->login($params, '192.168.0.1');
            }
        );

        unset($body['token']);
        $params = new AuthParams($body);

        $I->expectThrowable(
            $expectedException,
            function () use ($params)
            {
                $this->service->login($params, '192.168.0.1');
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \CoreBundle\Exception\ValidationException
     */
    public function testAccountDetails(UnitTester $I): void
    {
        $body = $this->body;
        $body['partner_code'] = 'test1';
        $params = new AuthParams($body);

        $this->webApiProviderMock->getPartnerApiMock()->setAccountDetails(null);

        $I->expectThrowable(
            new AuthenticationException('please_login', Response::HTTP_UNAUTHORIZED),
            function () use ($params)
            {
                $this->service->login($params, '192.168.0.1');
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \CoreBundle\Exception\ValidationException
     */
    public function testAccountDetailsCurrency(UnitTester $I): void
    {
        $body = $this->body;
        $body['partner_code'] = 'test1';
        $params = new AuthParams($body);

        $this->webApiProviderMock->getPartnerApiMock()->setAccountDetails(
            new AccountDetails('234', '', false)
        );

        $I->expectThrowable(
            new AuthenticationException('cant_login', Response::HTTP_NOT_FOUND),
            function () use ($params)
            {
                $this->service->login($params, '192.168.0.1');
            }
        );

        $this->webApiProviderMock->getPartnerApiMock()->setAccountDetails(
            new AccountDetails('234', 'php', false)
        );

        $I->expectThrowable(
            new AuthenticationException('cant_login', Response::HTTP_NOT_FOUND),
            function () use ($params)
            {
                $this->service->login($params, '192.168.0.1');
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws MissingSessionKeyException
     * @throws \CoreBundle\Exception\ValidationException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testSession(UnitTester $I): void
    {
        $body = $this->body;
        $body['partner_code'] = 'test1';
        $params = new AuthParams($body);

        $this->service->login($params, '192.168.0.1');

        $I->assertEquals('123', $I->getUserSession()->getClientToken());
        $I->assertEquals(1, $I->getUserSession()->getPartnerId());
        $I->assertEquals(1, $I->getUserSession()->getPlayerId());
        $I->assertEquals(1, $I->getUserSession()->getCurrencyId());

        $I->assertEquals(1, $I->getUserSession()->getLanguageId());
        $I->assertEquals(3, $I->getUserSession()->getTimezone());
        $I->assertEquals(true, $I->getUserSession()->isMobile());
        $I->assertEquals('fractional', $I->getUserSession()->getOddFormat());

        $this->service->clearApiUserData();

        $I->expectThrowable(
            new MissingSessionKeyException('client_token'),
            function () use ($I)
            {
                $I->getUserSession()->getClientToken();
            }
        );

        $I->expectThrowable(
            new MissingSessionKeyException('client_partner_id'),
            function () use ($I)
            {
                $I->getUserSession()->getPartnerId();
            }
        );

        $I->expectThrowable(
            new MissingSessionKeyException('client_player_id'),
            function () use ($I)
            {
                $I->getUserSession()->getPlayerId();
            }
        );

        $I->expectThrowable(
            new MissingSessionKeyException('client_currency_id'),
            function () use ($I)
            {
                $I->getUserSession()->getCurrencyId();
            }
        );

        $I->assertEquals(1, $I->getUserSession()->getLanguageId());
        $I->assertEquals(3, $I->getUserSession()->getTimezone());
        $I->assertEquals(true, $I->getUserSession()->isMobile());
        $I->assertEquals('fractional', $I->getUserSession()->getOddFormat());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \CoreBundle\Exception\ValidationException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testBlockedByCountry(UnitTester $I): void
    {
        $body = $this->body;
        $body['partner_code'] = 'test1';
        $params = new AuthParams($body);

        $I->expectThrowable(
            new AuthenticationException('GEO_BLOCKING', Response::HTTP_FORBIDDEN),
            function () use ($params) {
                $this->service->login($params, '65.55.37.104');
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \CoreBundle\Exception\ValidationException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testDisabledCurrency(UnitTester $I): void
    {
        $body = $this->body;
        $body['partner_code'] = 'test1';
        $params = new AuthParams($body);

        $this->webApiProviderMock->getPartnerApiMock()->setAccountDetails(
            new AccountDetails('234', 'test', false)
        );

        $I->expectThrowable(
            new AuthenticationException('cant_login', Response::HTTP_NOT_FOUND),
            function () use ($params)
            {
                $this->service->login($params, '192.168.0.1');
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \CoreBundle\Exception\ValidationException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testLoginShouldThrowExceptionForAnonymousUserWhenSupported(UnitTester $I) : void
    {
        $body = $this->body;
        $body['partner_code'] = 'test1';
        $body['token'] = '-';
        $params = new AuthParams($body);

        $I->expectThrowable(
            new AnonymousAuthenticationException('ANONYMOUS_TOKEN', Response::HTTP_FORBIDDEN),
            function () use ($params) {
                $this->service->login($params, '192.168.0.1');
            }
        );
    }
    /**
     * @param UnitTester $I
     *
     * @throws \CoreBundle\Exception\ValidationException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testLoginShouldNotThrowExceptionForAnonymousUserWhenNotSupported(UnitTester $I) : void
    {
        $body = $this->body;
        $body['partner_code'] = 'test1';
        $params = new AuthParams($body);

        $this->webApiProviderMock->setPartnerApi(
            Stub::make(WebApiNone::class, [
                'requestNewToken' => '-',
            ])
        );

        $I->expectThrowable(
            new AuthenticationException('please_login', Response::HTTP_UNAUTHORIZED),
            function () use ($params) {
                $this->service->login($params, '192.168.0.1');
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws MissingSessionKeyException
     * @throws \CoreBundle\Exception\ValidationException
     * @throws \Doctrine\DBAL\DBALException
     *
     * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassBeforeLastUsed
     */
    public function testFreePlayLogin(UnitTester $I): void
    {
        $this->webApiProviderMock->getPartnerApiMock()->setNewToken('freeplay-12345');
        $this->webApiProviderMock->getPartnerApiMock()->setAccountDetails(
            new AccountDetails('freeplay-12345', 'demo', false)
        );

        $I->getUserSession()->setFreePlay(true);

        /** @var Partner $partner **/
        $partner = $this->getEntityByReference('partner:1');

        $body = $this->body;
        $body['partner_code'] = $partner->getApiCode();
        unset($body['token']);
        $params = new AuthParams($body);

        $player = $this->service->login($params, '192.168.0.1');
        $I->assertEquals(1, $player->getId());
        $I->assertTrue($player->isFreePlay());
        $I->assertStringStartsWith('freeplay-', $player->getExternalCode());
        $I->assertSame($player->getExternalCode(), $player->getExternalToken());
    }

    /**
     * @param UnitTester $I
     *
     * @throws MissingSessionKeyException
     * @throws \CoreBundle\Exception\ValidationException
     * @throws \Doctrine\DBAL\DBALException
     *
     * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassBeforeLastUsed
     */
    public function testOnCurrencyChangedLoggedWarning(UnitTester $I): void
    {
        $body = $this->body;
        $body['partner_code'] = 'test1';
        $this->webApiProviderMock->getPartnerApiMock()->setAccountDetails(
            new AccountDetails('test123', 'eur', false)
        );

        $params = new AuthParams($body);

        $firstLoginPlayer = $this->service->login($params, '192.168.0.1');
        $I->assertEmpty($I->getTestLogger()->getRecords());

        $this->webApiProviderMock->getPartnerApiMock()->setAccountDetails(
            new AccountDetails('test123', 'demo', false)
        );

        $secondLoginPlayer = $this->service->login($params, '192.168.0.1');

        $I->assertEquals($firstLoginPlayer->getId(), $secondLoginPlayer->getId());
        $I->assertCount(1, $I->getTestLogger()->getRecords());
        $I->assertEquals(
            'Currency was changed from=eur to=demo for player=1',
            $I->getTestLogger()->getRecords()[0]['message']
        );
    }
}
