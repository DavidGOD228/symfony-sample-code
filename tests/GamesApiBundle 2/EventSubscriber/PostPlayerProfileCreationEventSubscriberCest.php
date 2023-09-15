<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\EventSubscriber;

use Carbon\CarbonImmutable;
use Doctrine\ORM\Tools\ToolsException;
use GamesApiBundle\Event\Gamification\PostPlayerProfileCreationEvent;
use GamesApiBundle\EventSubscriber\PostPlayerProfileCreationEventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class PostPlayerProfileCreationEventSubscriberCest
 */
final class PostPlayerProfileCreationEventSubscriberCest extends AbstractUnitTest
{
    private ?PostPlayerProfileCreationEventSubscriber $subscriber;

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->subscriber = $I->getContainer()->get(PostPlayerProfileCreationEventSubscriber::class);
    }

    /**
     * @param UnitTester $I
     */
    public function testSubscribedEvents(UnitTester $I): void
    {
        $I->assertInstanceOf(
            EventSubscriberInterface::class,
            $this->subscriber
        );

        $I->assertEquals(
            [
                PostPlayerProfileCreationEvent::class => 'update'
            ],
            PostPlayerProfileCreationEventSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testUpdateSuccess(UnitTester $I): void
    {
        $dateTIme = CarbonImmutable::create(2021, 5, 31, 00, 00, 00);

        $event = new PostPlayerProfileCreationEvent(10, $dateTIme);

        $this->subscriber->update($event);

        $I->assertEquals(
            [
                [
                    'channel' => 'gamification_messages_producer',
                    'message' => self::getConfirmProfileCreationRequest(),
                    'routingKey' => '',
                    'delay' => 0
                ]
            ],
            $I->getProducer()->messages
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testInfrastructureFailure(UnitTester $I): void
    {
        /** @var PostPlayerProfileCreationEventSubscriber $subscriber */
        $subscriber = $I->getContainer()->get(PostPlayerProfileCreationEventSubscriber::class);
        $I->getProducer()->disconnect();

        $subscriber->update(
            new PostPlayerProfileCreationEvent(1, new \DateTimeImmutable('now'))
        );

        $I->assertEquals(
            'Failed to publish PostPlayerProfileCreationEvent for player 1: RMQ_IS_DISCONNECTED',
            $I->getTestLogger()->getRecords()[0]['message']
        );
    }

    /**
     * @return string
     */
    private static function getConfirmProfileCreationRequest(): string
    {
        return file_get_contents(
            __DIR__ . '/../Fixture/Gamification/worker/confirm-profile-creation.serialized'
        );
    }
}