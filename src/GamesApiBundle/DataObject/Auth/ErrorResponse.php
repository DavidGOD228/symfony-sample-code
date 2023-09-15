<?php

namespace GamesApiBundle\DataObject\Auth;

use JsonSerializable;
use Exception;

/**
 * Class ErrorResponse
 */
class ErrorResponse implements JsonSerializable
{
    private const FIELD_ERROR = 'error';

    /**
     * @var string
     */
    private $error;

    /**
     * ErrorResponse constructor.
     *
     * @param string $error
     */
    public function __construct(string $error)
    {
        $this->error = $error;
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function jsonSerialize()
    {
        return [
            self::FIELD_ERROR => $this->error
        ];
    }
}