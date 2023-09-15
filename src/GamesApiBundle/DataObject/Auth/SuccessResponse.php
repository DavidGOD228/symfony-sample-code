<?php

namespace GamesApiBundle\DataObject\Auth;

use JsonSerializable;
use Exception;

/**
 * Class SuccessResponse
 */
class SuccessResponse implements JsonSerializable
{
    /**
     * @var string
     */
    protected $sessionId;

    /**
     * SuccessResponse constructor.
     *
     * @param string $sessionId
     */
    public function __construct(string $sessionId)
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function jsonSerialize()
    {
        return [
            'auth' => $this->sessionId,
        ];
    }
}