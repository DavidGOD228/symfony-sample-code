<?php

namespace GamesApiBundle\Controller;

use GamesApiBundle\Service\Translation\TranslationProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TranslationController
 */
class TranslationController extends AbstractController
{
    private TranslationProvider $translationProvider;

    /**
     * TranslationController constructor.
     *
     * @param TranslationProvider $provider
     */
    public function __construct(TranslationProvider $provider)
    {
        $this->translationProvider = $provider;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * CDN/Varnish cache didn't work here because Access-Control headers are lost in varnish cache.
     * We are using Access-Control for CORS requests from iframe
     */
    public function getAction(Request $request): JsonResponse
    {
        $languageCode = $request->get('languageCode');

        $translations = $this->translationProvider->getIframeTranslations($languageCode);

        $response = new JsonResponse([
            'translations' => $translations,
            'success' => true,
        ]);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * CDN/Varnish cache didn't work here because Access-Control headers are lost in varnish cache.
     * We are using Access-Control for CORS requests from iframe
     */
    public function getWidgetAction(Request $request): JsonResponse
    {
        $languageCode = $request->get('languageCode');

        $translations = $this->translationProvider->getWidgetTranslations($languageCode);

        $response = new JsonResponse($translations);

        return $response;
    }
}