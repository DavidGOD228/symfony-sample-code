<?php

declare(strict_types=1);

namespace GamesApiBundle\EventSubscriber;

use CodeigniterSymfonyBridge\AbstractBettingEvent;
use CodeigniterSymfonyBridge\PayData;
use CodeigniterSymfonyBridge\PayInWithBetEvent;
use CodeigniterSymfonyBridge\PayOutWithBetEvent;
use CoreBundle\Enum\BetType;
use GamesApiBundle\Service\WinningAnnounceService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class WinningAnnounceSubscriber
 */
class WinningAnnounceSubscriber implements EventSubscriberInterface
{
    private WinningAnnounceService $winningAnnounceService;
    private LoggerInterface $logger;

    /**
     * @param WinningAnnounceService $winningAnnounceService
     * @param LoggerInterface $logger
     */
    public function __construct(WinningAnnounceService $winningAnnounceService, LoggerInterface $logger)
    {
        $this->winningAnnounceService = $winningAnnounceService;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PayInWithBetEvent::class => 'onPostPayIn',
            PayOutWithBetEvent::class => 'onPostPayOut',
        ];
    }

    /**
     * @param PayInWithBetEvent $event
     */
    public function onPostPayIn(PayInWithBetEvent $event): void
    {
        $betType = $this->getBetTypeFromEvent($event);
        $bets = $event->getBets();

        try {
            $this->winningAnnounceService->processIncomingBets($bets, $betType);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Failed to process post pay in on PlayerWonAnnounceSubscriber ' . $exception->getMessage(),
                ['exception' => $exception]
            );
        }
    }

    /**
     * @param PayOutWithBetEvent $event
     */
    public function onPostPayOut(PayOutWithBetEvent $event): void
    {
        $betType = $this->getBetTypeFromEvent($event);
        $bets = $event->getBets();
        $bet = end($bets);

        try {
            $this->winningAnnounceService->processPaidOutBet($bet, $betType);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Failed to process post pay out on PlayerWonAnnounceSubscriber ' . $exception->getMessage(),
                ['exception' => $exception]
            );
        }
    }

    /**
     * @param AbstractBettingEvent $event
     *
     * @return BetType
     */
    private function getBetTypeFromEvent(AbstractBettingEvent $event) : BetType
    {
        switch ($event->getBetType()) {
            case PayData::TYPE_COMBO:
                return BetType::getCombination();
            case PayData::TYPE_SUBSCRIPTION:
                // For PayIn only, subscriptions are paid out as single bets
                return BetType::getSubscription();
            case PayData::TYPE_SINGLE:
            default:
                return BetType::getSingle();
        }
    }
}