<?php

declare(strict_types = 1);

namespace GamesApiBundle\Worker;

use Acme\Delay\DelayInterface;
use Acme\Semaphore\SemaphoreInterface;
use CoreBundle\Command\Worker\AbstractSemaphoreQueueWorker;
use CoreBundle\Exception\CoreException;
use Acme\RmqProducer\ProducerInterface;
use Acme\Time\Time;
use Doctrine\ORM\EntityManagerInterface;
use GamesApiBundle\DataObject\Gamification\ConfirmProfileCreationRequest;
use GamesApiBundle\DataObject\Gamification\GamificationAsyncRequestDto;
use GamesApiBundle\DataObject\Gamification\PayOutBetRequest;
use GamesApiBundle\DataObject\Gamification\PlaceBetRequest;
use GamesApiBundle\Exception\Gamification\CaptainUpException;
use GamesApiBundle\Service\Gamification\RequestHandler;
use Psr\Log\LoggerInterface;

/**
 * Class GamificationMessagesWorker
 */
final class GamificationMessagesWorker extends AbstractSemaphoreQueueWorker
{
    public const CHANNEL = 'gamification.messages.v1';
    public const PRODUCER = 'gamification_messages_producer';

    private const DELAY = 5; // 5 seconds. Agreed with CUP

    private RequestHandler $requestHandler;
    private ProducerInterface $producer;

    /**
     * GamificationActionWorker constructor.
     *
     * @param SemaphoreInterface $semaphore
     * @param LoggerInterface $logger
     * @param DelayInterface $delay
     * @param RequestHandler $requestHandler
     * @param ProducerInterface $producer
     * @param EntityManagerInterface|null $entityManager
     */
    public function __construct(
        SemaphoreInterface $semaphore,
        LoggerInterface $logger,
        DelayInterface $delay,
        RequestHandler $requestHandler,
        ProducerInterface $producer,
        ?EntityManagerInterface $entityManager
    )
    {
        parent::__construct($semaphore, $logger, $delay, $entityManager);

        $this->requestHandler = $requestHandler;
        $this->producer = $producer;
    }

    /**
     * @return int
     */
    protected function getDoctrineCacheStrategy(): int
    {
        return self::CACHE_STRATEGY_CLEAR_EM_BETWEEN_RUNS;
    }

    /**
     * @param GamificationAsyncRequestDto $packet
     *
     * @return string
     */
    protected function getLockKey($packet): string
    {
        $request = $packet->getRequest();

        if ($request instanceof ConfirmProfileCreationRequest) {
            $identifier = 'player' . ':' . $packet->getPlayerId();
        } else {
            $identifier = 'bet' . ':' . $packet->getBetId();
        }

        return sprintf(
            '%s:%s',
            self::CHANNEL,
            $identifier
        );
    }

    /**
     * @return string
     */
    protected function getTopic(): string
    {
        return self::CHANNEL;
    }

    /**
     * @param GamificationAsyncRequestDto $packet
     *
     * @throws CoreException
     */
    protected function processPacket($packet): void
    {
        try {
            $playerId = $packet->getPlayerId();
            $request = $packet->getRequest();

            if ($request instanceof PlaceBetRequest) {
                $this->requestHandler->placeBet($playerId, $request);
            } elseif ($request instanceof PayOutBetRequest) {
                $this->requestHandler->payOutBet($playerId, $request);
            } elseif ($request instanceof ConfirmProfileCreationRequest) {
                $this->requestHandler->confirmProfileCreation($playerId, $request);
            }
        } catch (CaptainUpException $e) {
            $this->producer->produce(
                self::PRODUCER,
                serialize($packet),
                '',
                self::DELAY * Time::MILLISECONDS_IN_SECOND
            );
            $this->logger->error('CaptainUpException during PayIn/PayOut.', ['exception' => $e]);
        }
    }

    /**
     * @param string $messageBody
     *
     * @return GamificationAsyncRequestDto
     */
    protected function unserializeMessage(string $messageBody): GamificationAsyncRequestDto
    {
        $allowedClasses = [
            GamificationAsyncRequestDto::class,
            PlaceBetRequest::class,
            PayOutBetRequest::class,
            ConfirmProfileCreationRequest::class,
        ];

        return unserialize($messageBody, ['allowed_classes' => $allowedClasses]);
    }
}
