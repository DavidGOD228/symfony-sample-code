<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\Gamification;

use Acme\Curl\CurlException;
use Acme\Curl\CurlTimeoutException;
use Acme\Curl\NullCurlAdapter;
use Acme\Curl\NullCurlFactory;
use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Combination;
use Acme\SymfonyDb\Entity\PlayerProfile;
use Acme\SymfonyDb\Entity\RpsBet;
use Acme\SymfonyDb\Entity\RpsRunRound;
use Acme\SymfonyDb\Entity\RpsRunRoundCard;
use Acme\SymfonyDb\Entity\Subscription;
use Acme\SymfonyDb\Entity\Ticket;
use DateTimeImmutable;
use Doctrine\ORM\Tools\ToolsException;
use GamesApiBundle\DataObject\Gamification\ConfirmProfileCreationRequest;
use GamesApiBundle\DataObject\Gamification\PlaceBetRequest;
use GamesApiBundle\DataObject\Gamification\PayOutBetRequest;
use GamesApiBundle\Exception\Gamification\CaptainUpException;
use GamesApiBundle\Service\Gamification\RequestBuilder;
use GamesApiBundle\Service\Gamification\RequestHandler;
use Psr\Log\Test\TestLogger;
use SymfonyTests\_support\Helper\GamificationRequestParser;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\CleanupBundle\Fixture\Bet\TicketFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\BetFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\CombinationFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\PartnerFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\PlayerFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\PlayerProfileFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\RpsBetFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\RpsRunRoundCardFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\RpsRunRoundFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\SubscriptionFixture;
use SymfonyTests\UnitTester;

/**
 * Class RequestHandlerCest
 */
final class RequestHandlerCest extends AbstractUnitTest
{
    private TestLogger $logger;
    private RequestHandler $service;
    private NullCurlAdapter $curl;

    protected array $tables = [
        RpsBet::class,
        RpsRunRound::class,
        RpsRunRoundCard::class,
        Ticket::class,
        Bet::class,
        PlayerProfile::class,
        Subscription::class,
        Combination::class
    ];

    protected array $fixtures = [
        PartnerFixture::class,
        PlayerFixture::class,
        PlayerProfileFixture::class,
        TicketFixture::class,
        RpsRunRoundFixture::class,
        RpsRunRoundCardFixture::class,
        RpsBetFixture::class,
        SubscriptionFixture::class,
        CombinationFixture::class,
        BetFixture::class,
    ];

    /**
     * {@inheritDoc}
     */
    protected function setUpFixtures(): void
    {
        parent::setUpFixtures();

        $this->fixtureBoostrapper->addGames([1, 15]);
        $this->fixtureBoostrapper->addPlayers(1);
        $this->fixtureBoostrapper->addPartners(1);
        $this->fixtureBoostrapper->addLanguages(['en']);
        $this->fixtureBoostrapper->addBets([1, 15], new DateTimeImmutable('2018-01-01 12:59:59'));
    }

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->logger = new TestLogger();
        $curlFactory = new NullCurlFactory();
        $this->curl = new NullCurlAdapter();
        $curlFactory->setInstance($this->curl);

        /** @var RequestBuilder $requestBuilder */
        $requestBuilder = $I->getContainer()->get(RequestBuilder::class);

        $this->service = new RequestHandler(
            $curlFactory,
            $this->logger,
            $requestBuilder
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     */
    public function testSuccessCreateProfileRequest(UnitTester $I): void
    {
        $profile = $this->getProfile();
        $this->curl->setRawResponse('{"data":{"id":"U3453D17021621Q3279052K003000871"}}');
        $this->service->createProfile($profile);

        $I->assertEquals(
            'captain_up:players: success',
            $this->logger->records[0]['message']
        );

        $request = GamificationRequestParser::parseFile(
            __DIR__ . '/../../Fixture/Gamification/request/profile.request'
        );
        $I->assertEquals(
            [
                'request' => $request,
                'response' => '{"data":{"id":"U3453D17021621Q3279052K003000871"}}'
            ],
            $this->logger->records[0]['context']
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     */
    public function testSuccessUpdateProfileRequest(UnitTester $I): void
    {
        $profile = $this->getProfile();
        $this->curl->setRawResponse('{"data":{"id":"U3453D17021621Q3279052K003000871"}}');
        $this->service->updateProfile($profile);

        $I->assertEquals(
            'captain_up:players: success',
            $this->logger->records[0]['message']
        );

        $request = GamificationRequestParser::parseFile(
            __DIR__ . '/../../Fixture/Gamification/request/profile.request'
        );
        $I->assertEquals(
            [
                'request' => $request,
                'response' => '{"data":{"id":"U3453D17021621Q3279052K003000871"}}'
            ],
            $this->logger->records[0]['context']
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     */
    public function testRetrieveProfileLevel(UnitTester $I): void
    {
        $profile = $this->getProfile();
        $this->curl->setRawResponse('{"data":{"level":"some-id"}}');
        $this->service->retrieveProfileLevel($profile);

        $I->assertEquals(
            'captain_up:players: success',
            $this->logger->records[0]['message']
        );

        $request = GamificationRequestParser::parseFile(
            __DIR__ . '/../../Fixture/Gamification/request/profile.request'
        );
        $I->assertEquals(
            [
                'request' => $request,
                'response' => '{"data":{"level":"some-id"}}'
            ],
            $this->logger->records[0]['context']
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testTransportError(UnitTester $I): void
    {
        $profile = $this->getProfile();
        $this->curl->setRequestHandler(
            function () {
                throw (new CurlException('CURL error: INTERNAL'))
                    ->setRawResponse(null)
                    ->setHttpStatusCode(500);
            }
        );

        $I->expectThrowable(
            new CaptainUpException('captain_up:players: CURL error: INTERNAL'),
            fn() => $this->service->createProfile($profile)
        );
        $I->expectThrowable(
            new CaptainUpException('captain_up:players: CURL error: INTERNAL'),
            fn() => $this->service->updateProfile($profile)
        );
        $I->expectThrowable(
            new CaptainUpException('captain_up:players/block: CURL error: INTERNAL'),
            fn() => $this->service->blockProfile($profile)
        );
        $I->expectThrowable(
            new CaptainUpException('captain_up:players/block: CURL error: INTERNAL'),
            fn() => $this->service->unblockProfile($profile)
        );
        $I->expectThrowable(
            new CaptainUpException('captain_up:users: CURL error: INTERNAL'),
            fn() => $this->service->deleteProfile($profile)
        );
        $I->assertEquals(
            'captain_up:players: CURL error: INTERNAL',
            $this->logger->records[0]['message']
        );

        $request = GamificationRequestParser::parseFile(
            __DIR__ . '/../../Fixture/Gamification/request/profile.request'
        );
        $I->assertEquals(
            [
                'request' => $request,
                'response' => null
            ],
            $this->logger->records[0]['context']
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testJsonError(UnitTester $I): void
    {
        $profile = $this->getProfile();
        $this->curl->setRawResponse('{aaa');

        $I->expectThrowable(
            new CaptainUpException('captain_up:players: Syntax error'),
            fn() => $this->service->createProfile($profile)
        );
        $I->expectThrowable(
            new CaptainUpException('captain_up:players: Syntax error'),
            fn() => $this->service->updateProfile($profile)
        );
        $I->expectThrowable(
            new CaptainUpException('captain_up:players/block: Syntax error'),
            fn() => $this->service->blockProfile($profile)
        );
        $I->expectThrowable(
            new CaptainUpException('captain_up:players/block: Syntax error'),
            fn() => $this->service->unblockProfile($profile)
        );
        $I->expectThrowable(
            new CaptainUpException('captain_up:users: Syntax error'),
            fn() => $this->service->deleteProfile($profile)
        );
        $I->assertEquals(
            'captain_up:players: Syntax error',
            $this->logger->records[0]['message']
        );

        $request = GamificationRequestParser::parseFile(
            __DIR__ . '/../../Fixture/Gamification/request/profile.request'
        );
        $I->assertEquals(
            [
                'request' => $request,
                'response' => '{aaa'
            ],
            $this->logger->records[0]['context']
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testLongHtmlError(UnitTester $I): void
    {
        $profile = $this->getProfile();
        $this->curl->setRawResponse(
            file_get_contents(__DIR__ . '/error.response.html')
        );
        $I->expectThrowable(
            new CaptainUpException('captain_up:players: Syntax error'),
            fn() => $this->service->createProfile($profile)
        );

        $I->assertEquals(
            1024,
            strlen($this->logger->records[0]['context']['response'])
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testTimeoutError(UnitTester $I): void
    {
        $profile = $this->getProfile();
        $this->curl->setRequestHandler(
            function () {
                throw (new CurlTimeoutException('CURL error: Timeout'))
                    ->setRawResponse(null)
                    ->setHttpStatusCode(408);
            }
        );

        $I->expectThrowable(
            new CaptainUpException('captain_up:players: CURL error: Timeout'),
            fn() => $this->service->createProfile($profile)
        );

        $I->assertEquals(
            'captain_up:players: CURL error: Timeout',
            $this->logger->records[0]['message']
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     */
    public function testSuccessPlaceBetRequest(UnitTester $I): void
    {
        $profile = $this->getProfile();
        $this->curl->setRawResponse('{"code":"200"}');

        $params = [
            PlaceBetRequest::PARTNER_CODE_FIELD => 'testApiCode1',
            PlaceBetRequest::CURRENCY_CODE_FIELD => 'eur',
            PlaceBetRequest::BET_TIME_FIELD => '2021-03-08 00:00:00',
            PlaceBetRequest::BET_TYPE_FIELD => 's',
            PlaceBetRequest::BET_AMOUNT_FIELD => 10,
            PlaceBetRequest::BET_AMOUNT_EUR_FIELD => 10,
            PlaceBetRequest::GAME_IDS_FIELD => [1],
            PlaceBetRequest::RUN_CODES_FIELD => ['1DrawCode', '1DrawCode'],
            PlaceBetRequest::ODD_CLASSES_FIELD => ['BALLX1_YES'],
            PlaceBetRequest::ODD_VALUE_FIELD => 10,
            PlaceBetRequest::TIE_ODD_VALUE_FIELD => 10,
            PlaceBetRequest::ROUND_NUMBER_FIELD => 1,
            PlaceBetRequest::BET_ITEMS_FIELD => ['10_2_red', '10_4_blue'],
        ];


        $request = new PlaceBetRequest($params);
        $this->service->placeBet($profile->getPlayer()->getId(), $request);

        $I->assertEquals(
            'captain_up:place_bet: success',
            $this->logger->records[0]['message']
        );

        $request = GamificationRequestParser::parseFile(
            __DIR__ . '/../../Fixture/Gamification/request/place-bet.request'
        );

        $I->assertEquals(
            [
                'request' => $request,
                'response' => '{"code":"200"}'
            ],
            $this->logger->records[0]['context']
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     */
    public function testSuccessPayOutBetRequest(UnitTester $I): void
    {
        $profile = $this->getProfile();
        $this->curl->setRawResponse('{"code":"200"}');

        $params = [
            PayOutBetRequest::PARTNER_CODE_FIELD => 'testApiCode1',
            PayOutBetRequest::CURRENCY_CODE_FIELD => 'eur',
            PayOutBetRequest::BET_TIME_FIELD => '2021-03-08 00:00:00',
            PayOutBetRequest::BET_TYPE_FIELD => 's',
            PayOutBetRequest::BET_STATUS_FIELD => 'Active',
            PayOutBetRequest::AMOUNT_WON_FIELD => 100,
            PayOutBetRequest::AMOUNT_WON_EUR_FIELD => 200,
            PayOutBetRequest::GAME_IDS_FIELD => [1, 1],
            PayOutBetRequest::RUN_CODES_FIELD => ['1DrawCode', '1DrawCode'],
            PayOutBetRequest::ODD_CLASSES_FIELD => ['BALLX1_YES', 'BALLX1_YES'],
            PayOutBetRequest::ODD_VALUE_FIELD => 10,
            PayOutBetRequest::TIE_ODD_VALUE_FIELD => 1.3,
            PayOutBetRequest::ROUND_NUMBER_FIELD => 1,
            PayOutBetRequest::RESULTS_FIELD => [],
            PayOutBetRequest::BET_ITEMS_FIELD => ['10_2_red', '10_4_blue'],
        ];

        $request = new PayOutBetRequest($params);
        $this->service->payOutBet($profile->getPlayer()->getId(), $request);

        $I->assertEquals(
            'captain_up:pay_out: success',
            $this->logger->records[0]['message']
        );

        $request = GamificationRequestParser::parseFile(
            __DIR__ . '/../../Fixture/Gamification/request/pay-out.request'
        );
        $I->assertEquals(
            [
                'request' => $request,
                'response' => '{"code":"200"}'
            ],
            $this->logger->records[0]['context']
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     */
    public function testSuccessConfirmProfileCreationRequest(UnitTester $I): void
    {
        $profile = $this->getProfile();
        $this->curl->setRawResponse('{"code":"200"}');

        $params = [
            ConfirmProfileCreationRequest::DATE_TIME_FIELD => '2021-05-31T15:30:10+00:00'
        ];

        $request = new ConfirmProfileCreationRequest($params);
        $this->service->confirmProfileCreation($profile->getPlayer()->getId(), $request);

        $I->assertEquals(
            'captain_up:profile_created: success',
            $this->logger->records[0]['message']
        );

        $request = GamificationRequestParser::parseFile(
            __DIR__ . '/../../Fixture/Gamification/request/create-profile.request'
        );
        $I->assertEquals(
            [
                'request' => $request,
                'response' => '{"code":"200"}'
            ],
            $this->logger->records[0]['context']
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     */
    public function testSuccessBlockProfileRequest(UnitTester $I): void
    {
        $profile = $this->getProfile();
        $this->curl->setRawResponse('{"foo":"bar"}');

        $gamification_profile = $this->service->blockProfile($profile);
        $I->assertStringEqualsFile(
            __DIR__ . '/../../Fixture/Gamification/objects/gamification-profile.serialized',
            serialize($gamification_profile)
        );

        $I->assertEquals(
            'captain_up:players/block: success',
            $this->logger->records[0]['message']
        );

        $request = GamificationRequestParser::parseFile(
            __DIR__ . '/../../Fixture/Gamification/request/block.request'
        );
        $I->assertEquals(
            [
                'request' => $request,
                'response' => '{"foo":"bar"}'
            ],
            $this->logger->records[0]['context']
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     */
    public function testSuccessUnBlockProfileRequest(UnitTester $I): void
    {
        $profile = $this->getProfile();
        $this->curl->setRawResponse('{"foo":"bar"}');

        $gamification_profile = $this->service->unblockProfile($profile);
        $I->assertStringEqualsFile(
            __DIR__ . '/../../Fixture/Gamification/objects/gamification-profile.serialized',
            serialize($gamification_profile)
        );

        $I->assertEquals(
            'captain_up:players/block: success',
            $this->logger->records[0]['message']
        );

        $request = GamificationRequestParser::parseFile(
            __DIR__ . '/../../Fixture/Gamification/request/block.request'
        );
        $I->assertEquals(
            [
                'request' => $request,
                'response' => '{"foo":"bar"}'
            ],
            $this->logger->records[0]['context']
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws CaptainUpException
     */
    public function testSuccessDeleteProfileRequest(UnitTester $I): void
    {
        $profile = $this->getProfile();
        $this->curl->setRawResponse('{"foo":"bar"}');

        $gamification_profile = $this->service->deleteProfile($profile);
        $I->assertStringEqualsFile(
            __DIR__ . '/../../Fixture/Gamification/objects/gamification-profile.serialized',
            serialize($gamification_profile)
        );

        $I->assertEquals(
            'captain_up:users: success',
            $this->logger->records[0]['message']
        );

        $request = GamificationRequestParser::parseFile(
            __DIR__ . '/../../Fixture/Gamification/request/delete.request'
        );
        $I->assertEquals(
            [
                'request' => $request,
                'response' => '{"foo":"bar"}'
            ],
            $this->logger->records[0]['context']
        );
    }

    /**
     * @return PlayerProfile
     */
    private function getProfile(): PlayerProfile
    {
        /** @var  PlayerProfile $profile*/
        $profile = $this->getEntityByReference('profile1:gamification');
        $profile->getPlayer()->getPartner()->setApiCode('api');
        return $profile;
    }
}
