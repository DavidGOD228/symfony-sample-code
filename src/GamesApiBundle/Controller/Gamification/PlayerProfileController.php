<?php

declare(strict_types = 1);

namespace GamesApiBundle\Controller\Gamification;

use Acme\Semaphore\SemaphoreException;
use Acme\Semaphore\SemaphoreInterface;
use CoreBundle\Exception\MissingSessionKeyException;
use CoreBundle\Exception\ValidationException;
use GamesApiBundle\Exception\Gamification\CaptainUpException;
use GamesApiBundle\Service\Gamification\PlayerProfileService;
use GamesApiBundle\Service\Gamification\RequestValidator;
use GamesApiBundle\Service\PlayerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class PlayerProfileController
 */
final class PlayerProfileController extends AbstractController
{
    private RequestValidator $validator;
    private PlayerService $playerService;
    private PlayerProfileService $profileService;
    private SemaphoreInterface $semaphoreService;
    private SerializerInterface $serializer;

    /**
     * Agree with FL possible request frequency.
     * Adding it to avoid bruteforce lookup for nicknames.
     * */
    private const VALIDATION_FREQUENCY_GAP = 250;

    /**
     * PlayerProfileController constructor.
     *
     * @param RequestValidator $validator
     * @param PlayerService $playerService
     * @param PlayerProfileService $profileService
     * @param SemaphoreInterface $semaphoreService
     * @param SerializerInterface $serializer
     */
    public function __construct(
        RequestValidator $validator,
        PlayerService $playerService,
        PlayerProfileService $profileService,
        SemaphoreInterface $semaphoreService,
        SerializerInterface $serializer
    )
    {
        $this->validator = $validator;
        $this->playerService = $playerService;
        $this->profileService = $profileService;
        $this->semaphoreService = $semaphoreService;
        $this->serializer = $serializer;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws MissingSessionKeyException - when not logged in.
     * @throws ValidationException - when request validation failed.
     * @throws CaptainUpException - when API responds with error.
     * @throws SemaphoreException - when another request from this player in progress.
     */
    public function createAction(Request $request): JsonResponse
    {
        $requestBody = $request->getJsonBody();
        $this->validator->validateCreateRequest($requestBody);

        $player = $this->playerService->getPlayerFromSession();

        $key = 'createProfile:' . $player->getId();
        $this->semaphoreService->acquireLockStrict($key);

        try {
            $profile = $this->profileService->create(
                $player,
                $requestBody[RequestValidator::FIELD_NAME],
                $request->getRootDomain()
            );
        } catch (CaptainUpException $e) {
            throw $e;
        } finally {
            // In case of CaptainUp API failure - releasing lock too.
            $this->semaphoreService->releaseLock($key);
        }

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($profile, JsonEncoder::FORMAT)
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws MissingSessionKeyException - when not logged in.
     * @throws SemaphoreException - when another request from this player in progress.
     */
    public function validateNicknameAction(Request $request): JsonResponse
    {
        $requestBody = $request->getJsonBody();
        $key = 'validateNickname:' . $this->playerService->getPlayerFromSession()->getId();

        $this->semaphoreService->acquireLockStrictMilliseconds($key, self::VALIDATION_FREQUENCY_GAP);

        $response = ['isAvailable' => true];
        try {
            $this->validator->validateCreateRequest($requestBody);
        } catch (ValidationException $e) {
            $response = ['isAvailable' => false];
        }

        return $this->json($response);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws MissingSessionKeyException - when not logged in.
     * @throws ValidationException - when request validation failed.
     * @throws CaptainUpException - when API responds with error.
     * @throws SemaphoreException - when another request from this player in progress.
     */
    public function updateAction(Request $request): JsonResponse
    {
        $requestBody = $request->getJsonBody();
        $this->validator->validateUpdateRequest($requestBody);

        $player = $this->playerService->getPlayerFromSession();

        $key = 'updateProfile:' . $player->getId();
        $this->semaphoreService->acquireLockStrict($key);

        try {
            $profile = $this->profileService->update(
                $player,
                $requestBody[RequestValidator::FIELD_AVATAR],
                $request->getRootDomain()
            );
        } catch (CaptainUpException $e) {
            throw $e;
        } finally {
            // In case of CaptainUp API failure - releasing lock too.
            $this->semaphoreService->releaseLock($key);
        }

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($profile, JsonEncoder::FORMAT)
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws CaptainUpException
     * @throws MissingSessionKeyException
     * @throws SemaphoreException
     * @throws ValidationException
     */
    public function blockAction(Request $request): JsonResponse
    {
        $requestBody = $request->getJsonBody();
        $this->validator->validateBlockRequest($requestBody);

        $player = $this->playerService->getPlayerFromSession();

        $key = 'blockProfile:' . $player->getId();
        $this->semaphoreService->acquireLockStrict($key);

        try {
            $profile = $this->profileService->block($player, $requestBody[RequestValidator::FIELD_REASON]);
        } catch (CaptainUpException $e) {
            throw $e;
        } finally {
            $this->semaphoreService->releaseLock($key);
        }

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($profile, JsonEncoder::FORMAT)
        );
    }

    /**
     * @return JsonResponse
     *
     * @throws CaptainUpException
     * @throws MissingSessionKeyException
     * @throws SemaphoreException
     * @throws ValidationException
     */
    public function unblockAction(): JsonResponse
    {
        $player = $this->playerService->getPlayerFromSession();

        $key = 'unblockProfile:' . $player->getId();
        $this->semaphoreService->acquireLockStrict($key);

        try {
            $profile = $this->profileService->unblock($player);
        } catch (CaptainUpException $e) {
            throw $e;
        } finally {
            $this->semaphoreService->releaseLock($key);
        }

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($profile, JsonEncoder::FORMAT)
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws CaptainUpException
     * @throws MissingSessionKeyException
     * @throws SemaphoreException
     * @throws ValidationException
     */
    public function deleteAction(Request $request): JsonResponse
    {
        $requestBody = $request->getJsonBody();
        $this->validator->validateDeleteRequest($requestBody);

        $player = $this->playerService->getPlayerFromSession();

        $key = 'deleteProfile:' . $player->getId();
        $this->semaphoreService->acquireLockStrict($key);

        try {
            $profile = $this->profileService->delete($player, $requestBody[RequestValidator::FIELD_REASON]);
        } catch (CaptainUpException $e) {
            throw $e;
        } finally {
            $this->semaphoreService->releaseLock($key);
        }

        return JsonResponse::fromJsonString(
            $this->serializer->serialize($profile, JsonEncoder::FORMAT)
        );
    }
}
