<?php

declare(strict_types = 1);

namespace GamesApiBundle\EventSubscriber;

use Acme\RmqProducer\ProducerInterface;
use GamesApiBundle\DataObject\Gamification\GamificationAsyncRequestDto;
use GamesApiBundle\Event\Gamification\PostPlayerProfileCreationEvent;
use GamesApiBundle\Service\Gamification\ConfirmPlayerProfileCreationRequestBuilder as RequestBuilder;
use GamesApiBundle\Worker\GamificationMessagesWorker;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PostPlayerProfileCreationEventSubscriber
 */
final class PostPlayerProfileCreationEventSubscriber implements EventSubscriberInterface
{
    private ProducerInterface $producer;
    private LoggerInterface $logger;
    private RequestBuilder  $requestBuilder;

    /**
     * PostPlayerProfileCreationEventSubscriber constructor.
     *
     * @param ProducerInterface $producer
     * @param LoggerInterface $logger
     * @param RequestBuilder $requestBuilder
     */
    public function __construct(ProducerInterface $producer, LoggerInterface $logger, RequestBuilder $requestBuilder)
    {
        $this->producer = $producer;
        $this->logger = $logger;
        $this->requestBuilder = $requestBuilder;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PostPlayerProfileCreationEvent::class => 'update',
        ];
    }

    /**
     * @param PostPlayerProfileCreationEvent $event
     */
    public function update(PostPlayerProfileCreationEvent $event): void
    {
        try {
            $request = $this->requestBuilder->build($event->getDateTime());
            $packet = new GamificationAsyncRequestDto($request, $event->getPlayerId(), null);

            $this->producer->produce(GamificationMessagesWorker::PRODUCER, serialize($packet));
        } catch (\Throwable $e) {
            $this->logger->error(
                sprintf(
                    'Failed to publish PostPlayerProfileCreationEvent for player %s: %s',
                    $event->getPlayerId(),
                    $e->getMessage()
                ),
                ['exception' => $e]
            );
        }
    }
}
