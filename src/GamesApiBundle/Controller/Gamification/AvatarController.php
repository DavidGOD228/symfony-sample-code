<?php

declare(strict_types = 1);

namespace GamesApiBundle\Controller\Gamification;

use GamesApiBundle\Service\Gamification\AvatarProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\SymfonyRequest\Request;

/**
 * Class AvatarController
 */
final class AvatarController extends AbstractController
{
    private AvatarProvider $provider;

    /**
     * AvatarController constructor.
     *
     * @param AvatarProvider $provider
     */
    public function __construct(
        AvatarProvider $provider
    )
    {
        $this->provider = $provider;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request): JsonResponse
    {
        $rootDomain = $request->getRootDomain();
        $avatars = $this->provider->getPreset($rootDomain);

        return $this->json($avatars);
    }
}
