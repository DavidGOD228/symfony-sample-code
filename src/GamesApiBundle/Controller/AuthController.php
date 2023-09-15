<?php

namespace GamesApiBundle\Controller;

use Acme\SymfonyRequest\RequestValidationException;
use CoreBundle\Exception\ValidationException;
use CoreBundle\Session\UserSessionInterface;
use GamesApiBundle\DataObject\Auth\ErrorResponse;
use GamesApiBundle\Exception\AnonymousAuthenticationException;
use GamesApiBundle\Exception\AuthenticationException;
use GamesApiBundle\Request\AuthParams;
use GamesApiBundle\Service\Auth\AuthInfoResponseBuilder;
use GamesApiBundle\Service\AuthService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\SymfonyRequest\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AuthController
 */
final class AuthController extends AbstractController
{
    private UserSessionInterface $session;
    private AuthInfoResponseBuilder $authInfoBuilder;
    private LoggerInterface $logger;
    private AuthService $authService;

    /**
     * @param UserSessionInterface $session
     * @param AuthInfoResponseBuilder $authBuilder
     * @param LoggerInterface $logger
     * @param AuthService $authService
     */
    public function __construct(
        UserSessionInterface $session,
        AuthInfoResponseBuilder $authBuilder,
        LoggerInterface $logger,
        AuthService $authService
    )
    {
        $this->session = $session;
        $this->authInfoBuilder = $authBuilder;
        $this->logger = $logger;
        $this->authService = $authService;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request): JsonResponse
    {
        $this->logger->info('Auth: call.', ['request' => $request->getContent($request)]);
        $ip = $request->getClientIp();

        try {
            $input = $request->getJsonBody();
            $params = new AuthParams($input);
            $player = $this->authService->login($params, $ip);

            $this->logger->info(
                'Auth: success call.',
                ['request' => $request->getContent()] + ['playerId' => $player->getId()]
            );

            $response = $this->authInfoBuilder->build(
                $this->session->getSessionId(),
                $player
            );
            $code = Response::HTTP_OK;
        } catch (RequestValidationException $e) {
            $this->logger->warning($e->getMessage(), ['request' => $request->getContent()]);
            $code = Response::HTTP_BAD_REQUEST;
            $response = new ErrorResponse('bad_request');
            $this->authService->clearApiUserData();
        } catch (ValidationException $e) {
            $this->logger->warning('Auth: ' . $e->getMessage(), ['request' => $request->getContent()]);
            $code = Response::HTTP_BAD_REQUEST;
            $response = new ErrorResponse('cant_login');
            $this->authService->clearApiUserData();
        } catch (AnonymousAuthenticationException $e) {
            // Anonymous user should get unauthorized code for correct FE handling.
            $code = Response::HTTP_UNAUTHORIZED;
            $this->logger->warning('Auth: anonymous call.', ['request' => $request->getContent()]);
            $response = new ErrorResponse($e->getMessage());
            $this->authService->clearApiUserData();
        } catch (AuthenticationException | InvalidArgumentException $e) {
            $this->logger->warning('Auth: ' . $e->getCode(), ['request' => $request->getContent()]);
            $code = Response::HTTP_UNAUTHORIZED;
            $response = new ErrorResponse($e->getMessage());
            $this->authService->clearApiUserData();
        }

        return $this->json($response, $code);
    }
}
