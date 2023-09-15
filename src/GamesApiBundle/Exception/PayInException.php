<?php

namespace GamesApiBundle\Exception;

use CoreBundle\Exception\CoreException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PayInException
 */
class PayInException extends CoreException
{
    /**
     * While we don't know better http code - let it be 400.
     * With 5xx could be issues with proxies which could handle it and provide html page instead. Not confirmed.
     *
     * @var int
     */
    public static $httpCode = Response::HTTP_BAD_REQUEST;

    /**
     * PayInException constructor.
     *
     * Should be created by named static constructors.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    private function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return static
     */
    public static function createTimeout(): self
    {
        return new self('transaction_failed_bet_timeout');
    }

    /**
     * @param int $message
     *
     * @return static
     */
    public static function createPartnerDisplayable(int $message): self
    {
        return new self($message);
    }

    /**
     * @return static
     */
    public static function createPartnerNotAccepted(): self
    {
        return new self('transaction_failed_bet_not_accepted');
    }
}