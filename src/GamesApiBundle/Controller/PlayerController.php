<?php

namespace GamesApiBundle\Controller;

use Acme\SymfonyDb\Entity\Partner;
use CoreBundle\Controller\AbstractJsonApiController;
use CoreBundle\Exception\MissingSessionKeyException;
use CoreBundle\Service\PartnerService;
use CoreBundle\Session\UserSessionInterface;
use GamesApiBundle\DataObject\PlayerBalance;
use GamesApiBundle\Service\PlayerBalanceService;
use GamesApiBundle\Service\PlayerService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PlayerController
 */
class PlayerController extends AbstractJsonApiController
{
    private UserSessionInterface $session;
    private PlayerBalanceService $playerBalanceService;
    private PlayerService $playerService;
    private PartnerService $partnerService;

    /**
     * PlayerController constructor.
     *
     * @param UserSessionInterface $session
     * @param PlayerBalanceService $playerBalanceService
     * @param PlayerService $playerService
     * @param PartnerService $partnerService
     */
    public function __construct(
        UserSessionInterface $session,
        PlayerBalanceService $playerBalanceService,
        PlayerService $playerService,
        PartnerService $partnerService
    )
    {
        $this->session = $session;
        $this->playerBalanceService = $playerBalanceService;
        $this->playerService = $playerService;
        $this->partnerService = $partnerService;
    }

    /**
     * @return JsonResponse
     *
     * @throws \CoreBundle\Exception\MissingSessionKeyException
     */
    public function balanceAction(): JsonResponse
    {
        try {
            $player = $this->playerService->getPlayerFromSession();
            $balanceState = $this->playerBalanceService->getPlayerBalanceState($player);
        } catch (MissingSessionKeyException $e) {
            $balanceState = new PlayerBalance(false);
        }

        return $this->json($balanceState);
    }

    /**
     * @return JsonResponse
     *
     * @throws \CoreBundle\Exception\CoreException
     */
    public function refreshBalanceAction(): JsonResponse
    {
        try {
            $player = $this->playerService->getPlayerFromSession();
            $balance = $this->playerBalanceService->refreshPlayerBalance($player);
        } catch (MissingSessionKeyException $e) {
            $balance = false;
        }

        return $this->json($balance);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function refreshTokenAction(Request $request): JsonResponse
    {
        try {
            $player = $this->playerService->getPlayerFromSession();
            $isLoggedIn = $this->playerService->refreshToken($player, $request->getClientIp());
        } catch (MissingSessionKeyException $e) {
            $isLoggedIn = false;
        }

        return $this->json($isLoggedIn);
    }

    /**
     * @param int $gameId
     *
     * @return JsonResponse
     */
    public function reInitGameSessionAction(int $gameId): JsonResponse
    {
        try {
            $token = $this->session->getClientToken();
            $isMobile = $this->session->isMobile();
            $isFreePlay = $this->session->isFreePlay();
            $partnerId = $this->session->getPartnerId();
            /** @var Partner $partner - we have in session 100% existing partner. */
            $partner = $this->partnerService->getPartner($partnerId);
            $isReInitialized = $this->playerService->reinitSession($partner, $token, $gameId, $isMobile, $isFreePlay);
        } catch (MissingSessionKeyException $e) {
            $isReInitialized = false;
        }

        return $this->json($isReInitialized);
    }
}
