<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification;

use Acme\SymfonyDb\Entity\Currency;
use CoreBundle\Exception\ValidationException;
use CoreBundle\Service\CurrencyService;

/**
 * Class CaptainUpCurrencyConverter
 */
final class CaptainUpCurrencyConverter
{
    private const CAPTAIN_UP_DEFAULT_CURRENCY = 'eur';

    private CurrencyService $currencyService;

    /**
     * CaptainUpCurrencyConverter constructor.
     *
     * @param CurrencyService $currencyService
     */
    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    /**
     * @param Currency $currencyConvertFrom
     * @param float $amount
     *
     * @return float
     *
     * @throws ValidationException
     */
    public function covertToDefault(Currency $currencyConvertFrom, float $amount): float
    {
        $currencyConvertTo = $this->currencyService->getCurrencyByCodeStrict(self::CAPTAIN_UP_DEFAULT_CURRENCY);
        $amountEur = $this->currencyService->convert($amount, $currencyConvertFrom, $currencyConvertTo);

        return $amountEur;
    }
}
