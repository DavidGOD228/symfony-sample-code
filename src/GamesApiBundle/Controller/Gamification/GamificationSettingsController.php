<?php

declare(strict_types=1);

namespace GamesApiBundle\Controller\Gamification;

use CoreBundle\Exception\MissingSessionKeyException;
use GamesApiBundle\Exception\Gamification\GamificationNotEnabledException;
use GamesApiBundle\Service\Gamification\GamificationSettingsResponseBuilder;
use GamesApiBundle\Service\PlayerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class GamificationSettingsController
 */
final class GamificationSettingsController extends AbstractController
{
    private PlayerService $playerService;
    private GamificationSettingsResponseBuilder $gamificationSettingsResponseBuilder;

    /**
     * Constructor.
     *
     * @param PlayerService $playerService
     * @param GamificationSettingsResponseBuilder $gamificationSettingsResponseBuilder
     */
    public function __construct(
        PlayerService $playerService,
        GamificationSettingsResponseBuilder $gamificationSettingsResponseBuilder
    )
    {
        $this->playerService = $playerService;
        $this->gamificationSettingsResponseBuilder = $gamificationSettingsResponseBuilder;
    }

    /**
     * @return JsonResponse
     * @throws MissingSessionKeyException if there is no player in session
     * @throws NotFoundHttpException if partner does not have gamification enabled
     */
    public function settingsAction(): JsonResponse
    {
        $player = $this->playerService->getPlayerFromSession();

        try {
            $response = $this->gamificationSettingsResponseBuilder->build($player);

            return $this->json($response);
        } catch (GamificationNotEnabledException $exception) {
            throw new NotFoundHttpException();
        }
    }
}