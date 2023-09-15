<?php

declare(strict_types = 1);

namespace GamesApiBundle\DataObject\Gamification;

/**
 * Class ConfirmProfileCreationRequest
 */
final class ConfirmProfileCreationRequest implements AsyncRequestInterface
{
    public const DATE_TIME_FIELD = 'date_time';

    private string $dateTime;

    /**
     * ConfirmProfileCreationRequest constructor.
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->dateTime = $params[self::DATE_TIME_FIELD];
    }

    /**
     * @return string
     */
    public function getDateTime(): string
    {
        return $this->dateTime;
    }
}