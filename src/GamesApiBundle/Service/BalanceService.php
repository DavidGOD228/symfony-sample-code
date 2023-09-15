<?php

namespace GamesApiBundle\Service;

use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\Player;
use Acme\WebApi\Feature\FeatureChecker;
use Acme\WebApi\Feature\SessionAwareApiInterface;
use Acme\WebApi\WebApiInterface;
use CodeigniterSymfonyBridge\CacheKeys;
use CodeigniterSymfonyBridge\UpdateBalanceTask;
use CoreBundle\AppEvents\UpdatedBalanceEvent;
use CoreBundle\Exception\CoreException;
use CoreBundle\Service\CacheService;
use CoreBundle\Service\CurrencyService;
use CoreBundle\Service\Event\EventBroadcaster;
use CoreBundle\Service\GameService;
use CoreBundle\Service\PartnerService;
use Acme\RmqProducer\ProducerInterface;
use CoreBundle\Service\SerializerService;
use CoreBundle\Worker\UpdateBalanceWorker;
use PartnerApiBundle\Service\PartnerWebApiProvider;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class BalanceService
 */
class BalanceService
{
    private const CACHE_KEY_BALANCE = CacheKeys::PLAYERS_BALANCE;

    private const DEFAULT_BALANCE = '0';

    // Should be no longer than duration of the fastest game round.
    public const CACHE_TTL = GameService::FASTEST_ROUND_DURATION;

    private CacheService $cacheService;
    private PartnerWebApiProvider $apiProvider;
    private ProducerInterface $producer;
    private PartnerService $partnerService;
    private EventBroadcaster $eventBroadcaster;
    private SessionInterface $session;
    private SerializerService $serializer;

    /**
     * @param CacheService $cacheService
     * @param PartnerWebApiProvider $apiProvider
     * @param ProducerInterface $producer
     * @param PartnerService $partnerService
     * @param EventBroadcaster $eventBroadcaster
     * @param SessionInterface $session
     * @param SerializerService $serializer
     */
    public function __construct(
        CacheService $cacheService,
        PartnerWebApiProvider $apiProvider,
        ProducerInterface $producer,
        PartnerService $partnerService,
        EventBroadcaster $eventBroadcaster,
        SessionInterface $session,
        SerializerService $serializer
    )
    {
        $this->cacheService = $cacheService;
        $this->apiProvider = $apiProvider;
        $this->producer = $producer;
        $this->partnerService = $partnerService;
        $this->eventBroadcaster = $eventBroadcaster;
        $this->session = $session;
        $this->serializer = $serializer;
    }

    /**
     * @param Player $player
     * @param float $updatedBalance
     * @param Currency $currency
     */
    public function setUpdatedBalance(Player $player, float $updatedBalance, Currency $currency): void
    {
        $this->setUpdatedBalanceByPlayerId($player->getId(), $updatedBalance, $currency);
    }


    /**
     * @param int $playerId
     * @param float $updatedBalance
     * @param Currency $currency
     *
     * @return string
     */
    public function setUpdatedBalanceByPlayerId(int $playerId, float $updatedBalance, Currency $currency): string
    {
        if (!$updatedBalance) {
            $updatedBalance = self::DEFAULT_BALANCE;
        }

        $balance = CurrencyService::currencyFormat($updatedBalance, $currency);

        $cacheKey = $this->getCacheKey($playerId);
        $oldBalance = $this->cacheService->get($cacheKey);
        $this->cacheService->set($cacheKey, $balance, self::CACHE_TTL);
        if ($balance != $oldBalance) {
            $updatedBalancePayload = $this->serializer->serialize([
                'playerId' => $playerId,
                'balance' => $balance
            ]);
            $this->eventBroadcaster->broadcast(new UpdatedBalanceEvent($updatedBalancePayload));
        }

        return $balance;
    }

    /**
     * @param int $playerId
     *
     * @return string
     */
    public function getCachedBalanceByPlayerId(int $playerId): string
    {
        $balance = $this->cacheService->get($this->getCacheKey($playerId));
        if (!$balance) {
            $balance = self::DEFAULT_BALANCE;
        }
        return $balance;
    }

    /**
     * @param int $partnerId
     * @param int $playerId
     * @param string $playersToken
     * @param Currency $currency
     * @param bool $isFreePlay
     *
     * @return string
     */
    public function updateCachedBalanceByPartnerId(
        int $partnerId,
        int $playerId,
        string $playersToken,
        Currency $currency,
        bool $isFreePlay
    ): string
    {
        $partner = $this->partnerService->getEnabledPartner($partnerId);
        if (!$partner) {
            return CurrencyService::currencyFormat(self::DEFAULT_BALANCE, $currency);
        }
        return $this->updateCachedBalance($partner, $playerId, $playersToken, $currency, $isFreePlay);
    }

    /**
     * @param Partner $partner
     * @param int $playerId
     * @param string $playersToken
     * @param Currency $currency
     * @param bool $isFreePlay
     *
     * @return string
     */
    public function updateCachedBalance(
        Partner $partner,
        int $playerId,
        string $playersToken,
        Currency $currency,
        bool $isFreePlay
    ): string
    {
        $partnerApi = $this->apiProvider->getPartnerApi($partner, $isFreePlay);
        $balanceSum = $partnerApi->getBalance($playersToken);
        if (!$balanceSum) {
            $balanceSum = self::DEFAULT_BALANCE;
        }
        return $this->setUpdatedBalanceByPlayerId($playerId, $balanceSum, $currency);
    }

    /**
     * @param int $partnerId
     * @param int $playerId
     * @param string $playersToken
     * @param bool $isFreePlay
     * @param int $currencyId
     * @param WebApiInterface|SessionAwareApiInterface $webApi
     *
     * @throws CoreException
     */
    public function publishUpdateBalanceTask(
        int $partnerId,
        int $playerId,
        string $playersToken,
        bool $isFreePlay,
        int $currencyId,
        WebApiInterface $webApi
    ): void
    {
        $task = new UpdateBalanceTask($partnerId, $playerId, $playersToken, $currencyId, $isFreePlay);
        if (FeatureChecker::supportsSessions($webApi->getType())) {
            $requiredSessionKeys = $webApi->getRequiredSessionDataKeys();
            foreach ($requiredSessionKeys as $key) {
                $task->setSessionDataKey($key, $this->session->get($key));
            }
        }

        $this->producer->produce(UpdateBalanceWorker::KEY_PRODUCER, serialize($task));
    }

    /**
     * @param Player $player
     *
     * @throws CoreException
     */
    public function publishRefreshBalanceTask(Player $player): void
    {
        $partner = $player->getPartner();
        $isFreePlay = $player->isFreePlay();
        $webApi = $this->apiProvider->getPartnerApi($partner, $isFreePlay);
        if (!FeatureChecker::isEnabled($webApi->getType())) {
            return;
        }
        $this->publishUpdateBalanceTask(
            $partner->getId(),
            $player->getId(),
            $player->getExternalToken(),
            $isFreePlay,
            $player->getCurrency()->getId(),
            $webApi
        );
    }

    /**
     * @param int $playerId
     *
     * @return string
     */
    private function getCacheKey(int $playerId): string
    {
        return self::CACHE_KEY_BALANCE . $playerId;
    }
}
