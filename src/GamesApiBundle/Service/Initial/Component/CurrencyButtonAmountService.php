<?php

namespace GamesApiBundle\Service\Initial\Component;

use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\CurrencyButtonAmount;
use CoreBundle\Service\CacheServiceInterface;
use CoreBundle\Service\RepositoryProviderInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Class CurrencyButtonAmountService
 */
class CurrencyButtonAmountService
{
    public const CACHE_KEY_BUTTONS_LIST = 'currency:amount_buttons:v2:';

    private CacheServiceInterface $cacheService;
    private EntityRepository $currencyButtonAmountRepository;

    /**
     * CurrencyButtonAmountService constructor.
     *
     * @param CacheServiceInterface $cacheService
     * @param RepositoryProviderInterface $repositoryProvider
     */
    public function __construct(CacheServiceInterface $cacheService, RepositoryProviderInterface $repositoryProvider)
    {
        $this->cacheService = $cacheService;
        $this->currencyButtonAmountRepository = $repositoryProvider->getSlaveRepository(CurrencyButtonAmount::class);
    }

    /**
     * @param Currency $currency
     *
     * @return array<CurrencyButtonAmount>
     *
     * @noinspection PhpDocMissingThrowsInspection - cache key hardcoded, no exception will be
     */
    public function getBetAmounts(Currency $currency): array
    {
        $buttons = $this->cacheService->getUnserialized(self::CACHE_KEY_BUTTONS_LIST . $currency->getId());

        // To cache empty lists needed strict check, because [] == false.
        if ($buttons !== null) {
            return $buttons;
        }

        $buttons = $this->currencyButtonAmountRepository->findBy(
            ['currency' => $currency],
            ['value' => 'ASC']
        );

        $this->cacheService->set(
            self::CACHE_KEY_BUTTONS_LIST . $currency->getId(),
            $buttons
        );

        return $buttons;
    }
}
