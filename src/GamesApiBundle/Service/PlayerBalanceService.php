<?php

namespace GamesApiBundle\Service;

use Acme\Semaphore\SemaphoreInterface;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\Player;
use CoreBundle\Exception\CoreException;
use GamesApiBundle\DataObject\PlayerBalance;

/**
 * Class PlayerBalanceService
 */
class PlayerBalanceService
{
    private BalanceService $balanceService;
    private SemaphoreInterface $semaphoreService;

    /**
     * PlayerBalanceService constructor.
     *
     * @param BalanceService $balanceService
     * @param SemaphoreInterface $semaphoreService
     */
    public function __construct(BalanceService $balanceService, SemaphoreInterface $semaphoreService)
    {
        $this->balanceService = $balanceService;
        $this->semaphoreService = $semaphoreService;
    }

    /**
     * @param Player $player
     *
     * @return PlayerBalance
     */
    public function getPlayerBalanceState(Player $player): PlayerBalance
    {
        return $this->getPlayerBalance($player->getPartner(), $player);
    }

    /**
     * We may receive several parallel requests to this method if player has multiple tabs open.
     * While we need to process only a single one. Locks guarantees that we won't process
     * refresh balance requests for the same player more often than once per 15 seconds.
     *
     * @param Player $player
     *
     * @return bool
     * @throws CoreException
     */
    public function refreshPlayerBalance(Player $player): bool
    {
        $lockKey = 'refreshBalance:' . $player->getId();
        $isLockAcquired = $this->semaphoreService->acquireLock($lockKey, BalanceService::CACHE_TTL);
        if (!$isLockAcquired) {
            return false;
        }

        $this->balanceService->publishRefreshBalanceTask($player);
        return true;
    }

    /**
     * @param Partner $partner
     * @param Player $player
     *
     * @return PlayerBalance
     */
    public function getPlayerBalance(
        Partner $partner,
        Player $player
    ): PlayerBalance
    {
        $balanceString = $this->getPlayerBalanceString(
            $partner,
            $player
        );

        return new PlayerBalance($partner->getApiShowBalance(), $balanceString);
    }

    /**
     * @param Partner $partner
     * @param Player $player
     *
     * @return string
     */
    private function getPlayerBalanceString(Partner $partner, Player $player): string
    {
        $balance = $this->balanceService->getCachedBalanceByPlayerId($player->getId());

        if (!$balance) {
            $balance = 0;
        }

        return $balance ?: $this->balanceService->updateCachedBalance(
            $partner,
            $player->getId(),
            $player->getExternalToken(),
            $player->getCurrency(),
            $player->isFreePlay()
        );
    }
}
