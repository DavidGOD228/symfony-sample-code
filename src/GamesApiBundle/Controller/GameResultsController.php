<?php

declare(strict_types=1);

namespace GamesApiBundle\Controller;

use Acme\SymfonyRequest\Request;
use CoreBundle\Exception\ValidationException;
use CoreBundle\Service\PartnerService;
use GamesApiBundle\DataObject\GameRunResults\ResultsParams;
use GamesApiBundle\Service\GameRunResults\ResultsProvider;
use GamesApiBundle\Service\GameRunResults\ResultsParamsValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class GameResultsController
 */
final class GameResultsController extends AbstractController
{
    /**
     * Historical-based value. No known business-features behind "30".
     */
    private const RESULTS_LIMIT_PER_PAGE = 30;

    private ResultsProvider $provider;
    private ResultsParamsValidator $validator;
    private PartnerService $partnerService;

    /**
     * GameResultsController constructor.
     *
     * @param ResultsProvider $provider
     * @param ResultsParamsValidator $validator
     * @param PartnerService $partnerService
     */
    public function __construct(
        ResultsProvider $provider,
        ResultsParamsValidator $validator,
        PartnerService $partnerService
    )
    {
        $this->provider = $provider;
        $this->validator = $validator;
        $this->partnerService = $partnerService;
    }

    /**
     * @param Request $request
     * @param string $partnerCode
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function resultsAction(Request $request, string $partnerCode): JsonResponse
    {
        $this->validator->validate($request);

        $params = new ResultsParams($request);
        $partner = $this->partnerService->getPartnerByPartnerApiCodeStrict($partnerCode);
        $results = $this->provider->get(
            $params,
            $partner,
            $request->getRootDomain(),
            self::RESULTS_LIMIT_PER_PAGE
        );

        return $this->json($results);
    }

    /**
     * @param Request $request
     * @param string $partnerCode
     * @param string $runCode
     *
     * @return JsonResponse
     * @throws ValidationException
     */
    public function gameRunResultAction(Request $request, string $partnerCode, string $runCode): JsonResponse
    {
        $partner = $this->partnerService->getPartnerByPartnerApiCodeStrict($partnerCode);
        $results = $this->provider->getByGameRunCode(
            $partner,
            $runCode,
            $request->getRootDomain()
        );

        return $this->json($results);
    }
}
