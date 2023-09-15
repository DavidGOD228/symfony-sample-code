<?php
/**
 * Created by PhpStorm.
 * User: j.palamar
 * Date: 2019-09-11
 * Time: 15:16
 */

namespace GamesApiBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException as BaseAuthenticationException;
use Throwable;

/**
 * Class AuthenticationException
 */
class AuthenticationException extends BaseAuthenticationException
{
    /**
     * AuthenticationException constructor.
     *
     * @param string $message
     * @param int $code
     * @param int $baseCode
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", int $code = 0, int $baseCode = 0, Throwable $previous = null)
    {
        $this->code = $code;
        parent::__construct($message, $baseCode, $previous);
    }
}