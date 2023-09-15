<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults;

use Acme\SymfonyDb\Entity\GameRun;
use CoreBundle\Service\Utility\RequestService;

/**
 * Class ResultsFilter
 */
final class ResultsVideoUrlProvider
{
    private RequestService $requestService;

    /**
     * ResultsVideoUrlProvider constructor.
     *
     * @param RequestService $requestService
     */
    public function __construct(RequestService $requestService)
    {
        $this->requestService = $requestService;
    }

    /**
     * @param GameRun $gameRun
     * @param string $rootDomain
     *
     * @return string|null
     */
    public function getVideoUrl(GameRun $gameRun, string $rootDomain): ?string
    {
        // Video not allowed to show for users.
        if ($gameRun->getVideoConfirmationRequired()) {
            return null;
        }

        // Video was not created/recorded.
        if (!$gameRun->getVideoUrl()) {
            return null;
        }

        $videoUrl = $this->requestService->getGameRunVideoUrl($rootDomain, $gameRun->getVideoUrl());

        return $videoUrl;
    }
}
