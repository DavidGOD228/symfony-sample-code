<?php

namespace GamesApiBundle\Request;

use CoreBundle\Exception\ValidationException;

/**
 * Class AuthParams
 */
class AuthParams
{
    public const FIELD_PARTNER_CODE = 'partner_code';
    public const FIELD_TOKEN = 'token';
    public const FIELD_LANGUAGE = 'language';
    public const FIELD_TIMEZONE = 'timezone';
    public const FIELD_IS_MOBILE = 'is_mobile';
    public const FIELD_ODDS_FORMAT = 'odds_format';
    public const FIELD_SID = 'sid';

    public const ODDS_FORMAT_DECIMAL = 'decimal';
    public const ODDS_FORMAT_FRACTIONAL = 'fractional';
    public const ODDS_FORMAT_AMERICAN = 'american';
    public const ODDS_FORMAT_HONGKONG = 'hongkong';

    /**
     * @var string
     */
    private $partnerCode;

    /**
     * @var string|null
     */
    private $token;

    /**
     * @var string
     */
    private $language;

    /**
     * @var float
     */
    private $timezone;

    /**
     * @var bool
     */
    private $isMobile;

    /**
     * @var string
     */
    private $oddsFormat;

    /**
     * @var string|null
     */
    private $sid;

    /**
     * Params constructor.
     *
     * @param array $input
     *
     * @throws ValidationException
     */
    public function __construct(array $input)
    {
        $validator = new AuthParamsValidator();
        $validator->validate($input);
        $this->partnerCode = $input[self::FIELD_PARTNER_CODE];
        $this->token = $input[self::FIELD_TOKEN] ?? null;
        $this->language = $input[self::FIELD_LANGUAGE];
        $this->timezone = $input[self::FIELD_TIMEZONE];
        $this->isMobile = $input[self::FIELD_IS_MOBILE] ?? false;
        $this->oddsFormat = $input[self::FIELD_ODDS_FORMAT] ?? self::ODDS_FORMAT_DECIMAL;
        $this->sid = $input[self::FIELD_SID] ?? null;
    }

    /**
     * @return string
     */
    public function getPartnerCode(): string
    {
        return $this->partnerCode;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return float
     */
    public function getTimezone(): float
    {
        return $this->timezone;
    }

    /**
     * @return bool
     */
    public function getIsMobile(): bool
    {
        return $this->isMobile;
    }

    /**
     * @return string
     */
    public function getOddsFormat(): string
    {
        return $this->oddsFormat;
    }

    /**
     * @return string|null
     */
    public function getSid(): ?string
    {
        return $this->sid;
    }
}