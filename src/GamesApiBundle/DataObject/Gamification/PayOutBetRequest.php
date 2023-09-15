<?php

declare(strict_types = 1);

namespace GamesApiBundle\DataObject\Gamification;

/**
 * Class PayOutBetRequest
 */
final class PayOutBetRequest implements AsyncRequestInterface
{
    public const PARTNER_CODE_FIELD = 'partner_code';
    public const CURRENCY_CODE_FIELD = 'currency_code';
    public const BET_TIME_FIELD = 'bet_time';
    public const BET_TYPE_FIELD = 'bet_type';
    public const BET_STATUS_FIELD = 'bet_status';
    public const AMOUNT_WON_FIELD = 'amount_won';
    public const AMOUNT_WON_EUR_FIELD = 'amount_won_eur';
    public const GAME_IDS_FIELD = 'game_ids';
    public const RUN_CODES_FIELD = 'run_codes';
    public const RESULTS_FIELD = 'game_results';
    public const BET_ITEMS_FIELD = 'bet_items';
    public const ODD_CLASSES_FIELD = 'odd_classes';
    public const ODD_VALUE_FIELD = 'odd_value';
    public const TIE_ODD_VALUE_FIELD = 'tie_odd_value';
    public const ROUND_NUMBER_FIELD = 'round_number';

    private string $partnerCode;
    private string $currencyCode;
    private string $betTime;
    private string $betType;
    private string $betStatus;
    private float $amountWon;
    private float $amountWonInEur;
    private array $gameIds;
    private array $runCodes;
    private array $results;
    private array $betItems;
    private array $oddClasses;
    private float $oddValue;
    private ?float $tieOddValue;
    private ?int $roundNumber;

    /**
     * PayOutBetRequest constructor.
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->partnerCode = $params[self::PARTNER_CODE_FIELD];
        $this->currencyCode = $params[self::CURRENCY_CODE_FIELD];
        $this->betTime = $params[self::BET_TIME_FIELD];
        $this->betType = $params[self::BET_TYPE_FIELD];
        $this->betStatus = $params[self::BET_STATUS_FIELD];
        $this->amountWon = $params[self::AMOUNT_WON_FIELD];
        $this->amountWonInEur = $params[self::AMOUNT_WON_EUR_FIELD];
        $this->gameIds = $params[self::GAME_IDS_FIELD];
        $this->runCodes = $params[self::RUN_CODES_FIELD];
        $this->results = $params[self::RESULTS_FIELD];
        $this->betItems = $params[self::BET_ITEMS_FIELD];
        $this->oddClasses = $params[self::ODD_CLASSES_FIELD];
        $this->oddValue = $params[self::ODD_VALUE_FIELD];
        $this->tieOddValue = $params[self::TIE_ODD_VALUE_FIELD];
        $this->roundNumber = $params[self::ROUND_NUMBER_FIELD];
    }

    /**
     * @return string
     */
    public function getPartnerCode(): string
    {
        return $this->partnerCode;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * @return string
     */
    public function getBetTime(): string
    {
        return $this->betTime;
    }

    /**
     * @return string
     */
    public function getBetType(): string
    {
        return $this->betType;
    }

    /**
     * @return string
     */
    public function getBetStatus(): string
    {
        return $this->betStatus;
    }

    /**
     * @return float
     */
    public function getAmountWon(): float
    {
        return $this->amountWon;
    }

    /**
     * @return float
     */
    public function getAmountWonEur(): float
    {
        return $this->amountWonInEur;
    }

    /**
     * @return array
     */
    public function getGameIds(): array
    {
        return $this->gameIds;
    }

    /**
     * @return array
     */
    public function getRunCodes(): array
    {
        return $this->runCodes;
    }

    /**
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @return array
     */
    public function getBetItems(): array
    {
        return $this->betItems;
    }

    /**
     * @return array
     */
    public function getOddClasses(): array
    {
        return $this->oddClasses;
    }

    /**
     * @return float
     */
    public function getOddValue(): float
    {
        return $this->oddValue;
    }

    /**
     * @return float|null
     */
    public function getTieOddValue(): ?float
    {
        return $this->tieOddValue;
    }

    /**
     * @return int|null
     */
    public function getRoundNumber(): ?int
    {
        return $this->roundNumber;
    }
}