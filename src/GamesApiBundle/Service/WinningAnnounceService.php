<?php

declare(strict_types=1);

namespace GamesApiBundle\Service;

use Acme\SymfonyDb\Entity\Bet;
use CoreBundle\Enum\BetType;
use CoreBundle\Service\CacheService;
use CoreBundle\Service\Event\EventBroadcaster;
use CoreBundle\Service\GameService;
use GamesApiBundle\IframeEvent\Payload\PlayerWonEventPayload;
use GamesApiBundle\IframeEvent\PlayerWonEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

/**
 * After all player bets are calculated for run we should broadcast him a message with amount of won.
 *
 * Class WinningAnnounceService
 */
class WinningAnnounceService
{
    // When enabling for other games,
    // don't forget to check whether we need to broadcast amount on each round
    private const ENABLED_FOR_GAMES = [
        GameService::GAME_ANDAR_BAHAR,
        GameService::GAME_BACCARAT,
        GameService::GAME_WAR,
        GameService::GAME_POKER,
        GameService::GAME_HEADSUP,
        GameService::GAME_RPS,
        GameService::GAME_FORTUNE,
        GameService::GAME_DICE_DUEL,
        GameService::GAME_MATKA,
    ];

    private const KEY_PREFIX = 'run_player_bets';
    private const KEY_BETS_COUNT = 'count';
    private const KEY_WON_AMOUNT = 'amount';

    private CacheService $cacheService;
    private SerializerInterface $serializer;
    private EventBroadcaster $broadcaster;
    private LoggerInterface $logger;

    /**
     * @param CacheService $cacheService
     * @param SerializerInterface $serializer
     * @param EventBroadcaster $broadcaster
     * @param LoggerInterface $logger
     */
    public function __construct(
        CacheService $cacheService,
        SerializerInterface $serializer,
        EventBroadcaster $broadcaster,
        LoggerInterface $logger
    )
    {
        $this->cacheService = $cacheService;
        $this->serializer = $serializer;
        $this->broadcaster = $broadcaster;
        $this->logger = $logger;
    }

    /**
     * Incrementing players betCount to be able to distinguish is all bets was processed
     *
     * @param array|Bet[] $bets
     * @param BetType $betType
     */
    public function processIncomingBets(array $bets, BetType $betType): void
    {
        foreach ($this->filterSupportedBets($bets, $betType) as $bet) {
            if (!$this->validateGameId($this->getGameId($bet))) {
                continue;
            }
            $key = $this->getPlayerBetCountKey($bet);
            $this->cacheService->increment($key);
        }
    }

    /**
     * @param Bet $paidOutBet
     */
    public function processPaidOutBet(Bet $paidOutBet): void
    {
        if (!$this->validateGameId($this->getGameId($paidOutBet))) {
            return;
        }

        $betsCountKey = $this->getPlayerBetCountKey($paidOutBet);
        $wonAmountKey = $this->getPlayerWonAmountKey($paidOutBet);
        $activeBetCount = $this->cacheService->decrement($betsCountKey);

        if ($this->isBetWon($paidOutBet)) {
            $currentBetAmountWom = $this->getAmountWon($paidOutBet);
            $combinedAmountWon = $this->cacheService->incrementBy($wonAmountKey, $currentBetAmountWom);
        } else {
            $combinedAmountWon = $this->cacheService->get($wonAmountKey);
        }

        if ($activeBetCount > 0) {
            return;
        }

        $this->cacheService->delete($betsCountKey);
        $this->cacheService->delete($wonAmountKey);

        if ($combinedAmountWon > 0) {
            $this->broadcastPayout($paidOutBet, (string) $combinedAmountWon);
        }
    }

    /**
     * @param Bet $bet
     * @param string $amountWon
     */
    private function broadcastPayout(Bet $bet, string $amountWon): void
    {
        try {
            $playerId = $bet->getPlayer()->getId();
            $gameId = $this->getGameId($bet);
            $runId = $bet->getGameRun()->getId();
            $payload = new PlayerWonEventPayload($playerId, $gameId, $runId, $amountWon);
            $this->broadcaster->broadcast(
                new PlayerWonEvent(
                    $this->serializer->serialize(
                        $payload,
                        PlayerWonEvent::FORMAT
                    )
                )
            );
        } catch (Throwable $exception) {
            $this->logger->error(
                'Failed to broadcast PlayerWonEvent: ' . $exception->getMessage(),
                ['exception' => $exception]
            );
        }
    }

    /**
     * @param Bet $bet
     *
     * @return int
     */
    private function getGameId(Bet $bet): int
    {
        return $bet->getGameRun()->getGame()->getId();
    }

    /**
     * @param int $gameId
     *
     * @return bool
     */
    private function validateGameId(int $gameId): bool
    {
        return in_array($gameId, self::ENABLED_FOR_GAMES, true);
    }

    /**
     * @param Bet $bet
     *
     * @return string
     */
    private function getCachePrefix(Bet $bet): string
    {
        return $this->cacheService->getCacheKey(
            [
                self::KEY_PREFIX,
                $bet->getPlayer()->getId(),
                $bet->getGameRun()->getId(),
            ]
        );
    }

    /**
     * @param Bet $bet
     *
     * @return string
     */
    private function getPlayerBetCountKey(Bet $bet): string
    {
        return $this->cacheService->getCacheKey([$this->getCachePrefix($bet), self::KEY_BETS_COUNT]);
    }

    /**
     * @param Bet $bet
     *
     * @return string
     */
    private function getPlayerWonAmountKey(Bet $bet): string
    {
        return $this->cacheService->getCacheKey([$this->getCachePrefix($bet), self::KEY_WON_AMOUNT]);
    }

    /**
     * @param Bet $bet
     *
     * @return bool
     */
    private function isBetWon(Bet $bet): bool
    {
        $combination = $bet->getCombination();
        // For combinations, consider the bet won if the entire combination is won or returned
        if ($combination) {
            return (bool) $combination->getAmountWon();
        }

        if (in_array($bet->getStatus(), [Bet::STATUS_WON, Bet::STATUS_TIE], true)) {
            return true;
        }

        // If a bet is not won, not tied and not returned, it shouldn't be reported
        if ($bet->getStatus() !== Bet::STATUS_RETURNED) {
            return false;
        }

        // TODO: Remove following logic in GM-609 (after switching to Bet::STATUS_TIE)
        $run = $bet->getGameRun();
        if ($run->getIsReturned()) {
            return false;
        }

        // Consider returned bets as won for single-round games
        switch ($run->getGame()->getId()) {
            case GameService::GAME_BACCARAT:
            case GameService::GAME_RPS:
            case GameService::GAME_DICE_DUEL:
            case GameService::GAME_FORTUNE:
            case GameService::GAME_MATKA:
                return true;
        }

        return false;
    }

    /**
     * @param Bet $bet
     *
     * @return float|null
     */
    private function getAmountWon(Bet $bet): ?float
    {
        $combination = $bet->getCombination();
        // For combinations, include entire combination amount in winnings
        if ($combination) {
            return $combination->getAmountWon();
        }

        return $bet->getAmountWon();
    }

    /**
     * @param array $bets
     * @param BetType $betType
     *
     * @return array
     */
    private function filterSupportedBets(array $bets, BetType $betType): array
    {
        if ($betType->getValue() === BetType::COMBINATION) {
            return [end($bets)];
        }

        return $bets;
    }
}
