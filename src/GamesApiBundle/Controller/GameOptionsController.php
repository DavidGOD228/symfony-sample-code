<?php

declare(strict_types=1);

namespace GamesApiBundle\Controller;

use CoreBundle\Exception\ValidationException;
use CoreBundle\Service\PartnerService;
use GamesApiBundle\DataObject\GameOptions\GameOptionsRequest;
use GamesApiBundle\Service\GameOptions\GameOptionsRequestValidator;
use GamesApiBundle\Service\GameOptions\GameOptionsProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GameOptionsController
 */
final class GameOptionsController extends AbstractController
{
    private GameOptionsRequestValidator $validator;
    private PartnerService $partnerService;
    private GameOptionsProvider $provider;

    /**
     * GameSettingsController constructor.
     *
     * @param GameOptionsRequestValidator $validator
     * @param PartnerService $partnerService
     * @param GameOptionsProvider $provider
     */
    public function __construct(
        GameOptionsRequestValidator $validator,
        PartnerService $partnerService,
        GameOptionsProvider $provider
    )
    {
        $this->validator = $validator;
        $this->partnerService = $partnerService;
        $this->provider = $provider;
    }

    /**
     * @param Request $request
     * @param string $partnerCode
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function gameOptionsAction(Request $request, string $partnerCode): JsonResponse
    {
        $requestParams = $request->query->all();
        $this->validator->validate($requestParams);

        $params = new GameOptionsRequest($requestParams);
        $partner = $this->partnerService->getPartnerByPartnerApiCodeStrict($partnerCode);
        $settings = $this->provider->get($params, $partner);

        return $this->json($settings);
    }
}