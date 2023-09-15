<?php

namespace GamesApiBundle\Controller;

use CoreBundle\Exception\ValidationException;
use GamesApiBundle\Service\TopWon\TopWonAmountsProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class TopWonAmountsController
 */
class TopWonAmountsController extends AbstractController
{
    private TopWonAmountsProvider $topWonAmountsProvider;

    /**
     * TopWonAmountsController constructor.
     *
     * @param TopWonAmountsProvider $topWonAmountsService
     */
    public function __construct(
        TopWonAmountsProvider $topWonAmountsService
    )
    {
        $this->topWonAmountsProvider = $topWonAmountsService;
    }

    /**
     * @param string $gameId
     *
     * @throws ValidationException
     *
     * @return JsonResponse
     */
    public function getByGameIdAction(string $gameId): JsonResponse
    {
        $gameId = (int) $gameId;
        if (!$gameId) {
            throw new ValidationException("INVALID_GAME_ID");
        }
        $topWonAmounts = $this->topWonAmountsProvider->getTopWonAmountByGameId($gameId);

        return $this->json($topWonAmounts);
    }
}