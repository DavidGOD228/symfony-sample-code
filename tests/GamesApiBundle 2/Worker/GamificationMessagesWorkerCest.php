<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Worker;

use Acme\Curl\CurlTimeoutException;
use Acme\Curl\NullCurlAdapter;
use CoreBundle\Command\Worker\AbstractSemaphoreQueueWorker;
use GamesApiBundle\DataObject\Gamification\ConfirmProfileCreationRequest;
use GamesApiBundle\DataObject\Gamification\GamificationAsyncRequestDto;
use GamesApiBundle\DataObject\Gamification\PayOutBetRequest;
use GamesApiBundle\Worker\GamificationMessagesWorker;
use Monolog\Handler\TestHandler;
use PhpAmqpLib\Message\AMQPMessage;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\GamesApiBundle\Helper\Camification\UnsupportedRequest;
use SymfonyTests\UnitTester;

/**
 * Class GamificationMessagesWorkerCest
 */
final class GamificationMessagesWorkerCest extends AbstractUnitTest
{
    protected GamificationMessagesWorker $worker;
    protected TestHandler $logger;

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->logger = $I->getTestLogger();
        $curl = new NullCurlAdapter();
        $curl->setRawResponse('{"code":"200"}');
        $I->getCurlFactory()->setInstance($curl);

        $this->worker = $I->getContainer()->get(GamificationMessagesWorker::class);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testProcessPacketWithPlaceBetRequest(UnitTester $I): void
    {
        $message = new AMQPMessage($this->getPlaceBetRequest(), []);
        $message->delivery_info['delivery_tag'] = 'something';

        $result = $this->worker->execute($message);

        $I->assertTrue($result);
        $I->assertEquals(
            'captain_up:place_bet: success',
            $this->logger->getRecords()[0]['message']
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testProcessPacketWithPayOutBetRequest(UnitTester $I): void
    {
        $message = new AMQPMessage($this->getPayOutBetRequest(), []);
        $message->delivery_info['delivery_tag'] = 'something';

        $result = $this->worker->execute($message);

        $I->assertTrue($result);
        $I->assertEquals(
            'captain_up:pay_out: success',
            $this->logger->getRecords()[0]['message']
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testProcessPacketWithConfirmProfileCreationRequest(UnitTester $I): void
    {
        $message = new AMQPMessage($this->getConfirmProfileCreationRequest(), []);
        $message->delivery_info['delivery_tag'] = 'something';

        $result = $this->worker->execute($message);

        $I->assertTrue($result);
        $I->assertEquals(
            'captain_up:profile_created: success',
            $this->logger->getRecords()[0]['message']
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testProcessPacketWithException(UnitTester $I): void
    {
        $curl = new NullCurlAdapter();
        $curl->setRequestHandler(
            function () {
                throw (new CurlTimeoutException('CURL error: Timeout'))
                    ->setRawResponse(null)
                    ->setHttpStatusCode(408);
            }
        );
        $I->getCurlFactory()->setInstance($curl);

        $message = new AMQPMessage($this->getPayOutBetRequest(), []);
        $message->delivery_info['delivery_tag'] = 'something';

        $result = $this->worker->execute($message);

        $I->assertTrue($result);

        $I->assertEquals(
            [
                'channel' => 'gamification_messages_producer',
                'message' => file_get_contents(
                    __DIR__ . '/../Fixture/Gamification/worker/message.response'
                ),
                'routingKey' => '',
                'delay' => 5000,
            ],
            $I->getProducer()->messages[0]
        );

        $I->assertEquals(
            'captain_up:pay_out: CURL error: Timeout',
            $this->logger->getRecords()[0]['message']
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testProcessPacketWithUnsupportedRequest(UnitTester $I): void
    {
        $packet = new GamificationAsyncRequestDto(new UnsupportedRequest(), 10, 23);

        $message = new AMQPMessage(serialize($packet), []);
        $message->delivery_info['delivery_tag'] = 'something';

        $this->callProtected($this->worker, 'processPacket', [$packet]);
        $result = $this->worker->execute($message);

        $I->assertTrue($result);

        $I->assertEquals(
            '[gamification.messages.v1:something] -  Error while ' .
            'unserializing message! ACKing to remove it from queue.',
            $this->logger->getRecords()[0]['message']
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testGetDoctrineCacheStrategy(UnitTester $I): void
    {
        $strategy = $this->callProtected($this->worker, 'getDoctrineCacheStrategy', []);

        $I->assertEquals(
            AbstractSemaphoreQueueWorker::CACHE_STRATEGY_CLEAR_EM_BETWEEN_RUNS,
            $strategy
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testGetLockKeyWithBetRequestPacket(UnitTester $I): void
    {
        $packet = $this->getPayOutBetRequestPacket();

        $lockKey = $this->callProtected($this->worker, 'getLockKey', [$packet]);
        $I->assertEquals(
            "gamification.messages.v1:bet:23",
            $lockKey
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testGetLockKeyWitConfirmProfileCreationRequestPacket(UnitTester $I): void
    {
        $packet = $this->getConfirmProfileCreationPacket();

        $lockKey = $this->callProtected($this->worker, 'getLockKey', [$packet]);
        $I->assertEquals(
            "gamification.messages.v1:player:10",
            $lockKey
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testGetTopic(UnitTester $I): void
    {
        $topic = $this->callProtected($this->worker, 'getTopic', []);
        $I->assertEquals(
            'gamification.messages.v1',
            $topic
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testInvalidMessage(UnitTester $I): void
    {
        $message = new AMQPMessage('message', []);
        $message->delivery_info['delivery_tag'] = 'something';

        $this->worker->execute($message);

        $I->assertEquals(
            '[gamification.messages.v1:something] -  Error while ' .
            'unserializing message! ACKing to remove it from queue.',
            $I->getTestLogger()->getRecords()[0]['message']
        );
    }

    /**
     * @return GamificationAsyncRequestDto
     */
    private function getPayOutBetRequestPacket(): GamificationAsyncRequestDto
    {
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

        return new GamificationAsyncRequestDto($request, 10, 23);
    }

    /**
     * @return GamificationAsyncRequestDto
     */
    private function getConfirmProfileCreationPacket(): GamificationAsyncRequestDto
    {
        $params = [
            ConfirmProfileCreationRequest::DATE_TIME_FIELD => '2021-05-31T00:00:00+00:00',
        ];

        $request = new ConfirmProfileCreationRequest($params);

        return new GamificationAsyncRequestDto($request, 10, null);
    }

    /**
     * @return string
     */
    private function getPlaceBetRequest(): string
    {
        return file_get_contents(
            __DIR__ . '/../Fixture/Gamification/worker/place-bet.serialized'
        );
    }

    /**
     * @return string
     */
    private function getPayOutBetRequest(): string
    {
        return file_get_contents(
            __DIR__ . '/../Fixture/Gamification/worker/pay-out.serialized'
        );
    }

    /**
     * @return string
     */
    private function getConfirmProfileCreationRequest(): string
    {
        return file_get_contents(
            __DIR__ . '/../Fixture/Gamification/worker/confirm-profile-creation.serialized'
        );
    }
}
