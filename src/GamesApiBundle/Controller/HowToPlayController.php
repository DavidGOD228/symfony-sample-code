<?php

declare(strict_types=1);

namespace GamesApiBundle\Controller;

use CoreBundle\Exception\ValidationException;
use CoreBundle\Service\GameService;
use CoreBundle\Service\LanguageService;
use CoreBundle\Service\PartnerService;
use GamesApiBundle\Service\HowToPlayProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class HowToPlayController
 */
final class HowToPlayController extends AbstractController
{
    private PartnerService $partnerService;
    private LanguageService $languageService;
    private GameService $gameService;
    private HowToPlayProvider $howToPlayProvider;

    /**
     * @param PartnerService $partnerService
     * @param LanguageService $languageService
     * @param GameService $gameService
     * @param HowToPlayProvider $howToPlayProvider
     */
    public function __construct(
        PartnerService $partnerService,
        LanguageService $languageService,
        GameService $gameService,
        HowToPlayProvider $howToPlayProvider
    )
    {
        $this->partnerService = $partnerService;
        $this->languageService = $languageService;
        $this->gameService = $gameService;
        $this->howToPlayProvider = $howToPlayProvider;
    }

    /**
     * @param string $apiCode
     * @param int $gameId
     * @param string $languageCode
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function howToPlayAction(string $apiCode, int $gameId, string $languageCode): JsonResponse
    {
        $partner = $this->partnerService->getPartnerByPartnerApiCodeStrict($apiCode);
        $allowedGame = $this->gameService->getEnabledGameStrict($partner, $gameId);

        $language = $this->languageService->getLanguageByCodeOrDefaultLanguage($languageCode);
        $blocks = $this->howToPlayProvider->getBlocks($allowedGame, $language);

        return $this->json(['blocks' => $blocks]);
    }

    /**
     * @param string $apiCode
     * @param int $gameId
     * @param string $languageCode
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function howToPlayOnScreenAction(
        string $apiCode,
        int $gameId,
        string $languageCode
    ): JsonResponse
    {
        $partner = $this->partnerService->getPartnerByPartnerApiCodeStrict($apiCode);
        $allowedGame = $this->gameService->getEnabledGameStrict($partner, $gameId);
        $language = $this->languageService->getLanguageByCodeOrDefaultLanguage($languageCode);
        $onScreen = $this->howToPlayProvider->getOnScreen($allowedGame, $language);

        return $this->json($onScreen);
    }
}