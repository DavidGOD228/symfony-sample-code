<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\Gamification;

use DateTimeImmutable;
use GamesApiBundle\DataObject\Gamification\ConfirmProfileCreationRequest;

/**
 * Class ConfirmPlayerProfileCreationRequestBuilder
 */
final class ConfirmPlayerProfileCreationRequestBuilder
{
    /**
     * @param DateTimeImmutable $dateTime
     *
     * @return ConfirmProfileCreationRequest
     */
    public function build(DateTimeImmutable $dateTime): ConfirmProfileCreationRequest
    {
        $params[ConfirmProfileCreationRequest::DATE_TIME_FIELD] = $this->formatDataTime($dateTime);

        return new ConfirmProfileCreationRequest($params);
    }

    /**
     * @param DateTimeImmutable $dateTime
     *
     * @return string
     */
    private function formatDataTime(DateTimeImmutable $dateTime): string
    {
        return $dateTime->format('Y-m-d\TH:i:sP');
    }
}