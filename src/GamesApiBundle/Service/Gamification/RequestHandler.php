<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification;

use Acme\Curl\CurlAdapterInterface;
use Acme\Curl\CurlException;
use Acme\Curl\CurlFactoryInterface;
use Acme\SymfonyDb\Entity\PlayerProfile;
use Eastwest\Json\Json;
use Eastwest\Json\JsonException;
use GamesApiBundle\DataObject\Gamification\ConfirmProfileCreationRequest;
use GamesApiBundle\DataObject\Gamification\GamificationProfile;
use GamesApiBundle\DataObject\Gamification\PayOutBetRequest;
use GamesApiBundle\DataObject\Gamification\PlaceBetRequest;
use GamesApiBundle\Exception\Gamification\CaptainUpException;
use GamesApiBundle\DataObject\Gamification\CreateProfileResponse;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class RequestHandler
 */
class RequestHandler
{
    private const TIMEOUT = 30;

    private CurlFactoryInterface $curlFactory;
    private LoggerInterface $logger;
    private RequestBuilder $requestBuilder;

    /**
     * RequestHandler constructor.
     *
     * @param CurlFactoryInterface $curlFactory
     * @param LoggerInterface $logger
     * @param RequestBuilder $requestBuilder
     */
    public function __construct(
        CurlFactoryInterface $curlFactory,
        LoggerInterface $logger,
        RequestBuilder $requestBuilder
    )
    {
        $this->curlFactory = $curlFactory;
        $this->logger = $logger;
        $this->requestBuilder = $requestBuilder;
    }

    /**
     * @param PlayerProfile $profile
     *
     * @return CreateProfileResponse
     *
     * @throws CaptainUpException
     */
    public function createProfile(PlayerProfile $profile): CreateProfileResponse
    {
        $requestData = $this->requestBuilder->buildProfile($profile);

        $method = RequestBuilder::METHOD_PLAYERS;
        $path = $this->requestBuilder->buildPath($method);

        $response = $this->sendPost($requestData, $path, $method);

        return new CreateProfileResponse(
            $requestData['user']['id'],
            $response->data->id,
            $this->requestBuilder->buildSignedUser($profile)
        );
    }

    /**
     * @param PlayerProfile $profile
     *
     * @return GamificationProfile
     *
     * @throws CaptainUpException
     */
    public function updateProfile(PlayerProfile $profile): GamificationProfile
    {
        $requestData = $this->requestBuilder->buildProfile($profile);

        $method = RequestBuilder::METHOD_PLAYERS;
        $path = $this->requestBuilder->buildPath($method);

        $this->sendPost($requestData, $path, $method);

        return new GamificationProfile(
            $requestData['user']['id'],
            $profile,
            $this->requestBuilder->buildSignedUser($profile)
        );
    }

    /**
     * @param PlayerProfile $profile
     *
     * @return string
     *
     * @throws CaptainUpException
     */
    public function retrieveProfileLevel(PlayerProfile $profile): string
    {
        $requestData = $this->requestBuilder->buildProfile($profile);

        $method = RequestBuilder::METHOD_PLAYERS;
        $path = $this->requestBuilder->buildPath($method);

        $response = $this->sendPost($requestData, $path, $method);

        return $response->data->level;
    }

    /**
     * @param PlayerProfile $profile
     *
     * @return GamificationProfile
     *
     * @throws CaptainUpException
     */
    public function blockProfile(PlayerProfile $profile): GamificationProfile
    {
        $requestData = $this->requestBuilder->buildProfileForStateChange($profile);

        $method = RequestBuilder::METHOD_PLAYER_BLOCK;
        $path = $this->requestBuilder->buildPath($method);

        $this->sendPost($requestData, $path, $method);

        return new GamificationProfile(
            $this->requestBuilder->buildInternalProfileId($profile),
            $profile,
            $this->requestBuilder->buildSignedUser($profile)
        );
    }

    /**
     * @param PlayerProfile $profile
     *
     * @return GamificationProfile
     *
     * @throws CaptainUpException - when API responds with error.
     */
    public function unblockProfile(PlayerProfile $profile): GamificationProfile
    {
        $requestData = $this->requestBuilder->buildProfileForStateChange($profile);

        $method = RequestBuilder::METHOD_PLAYER_BLOCK;
        $path = $this->requestBuilder->buildPath($method);

        $this->sendDelete($requestData, $path, $method);

        return new GamificationProfile(
            $this->requestBuilder->buildInternalProfileId($profile),
            $profile,
            $this->requestBuilder->buildSignedUser($profile)
        );
    }

    /**
     * @param PlayerProfile $profile
     *
     * @return GamificationProfile
     *
     * @throws CaptainUpException - when API responds with error.
     */
    public function deleteProfile(PlayerProfile $profile): GamificationProfile
    {
        $requestData = $this->requestBuilder->buildProfileForStateChange($profile);

        $method = RequestBuilder::METHOD_USERS;
        $path = $this->requestBuilder->buildPath($method);

        $this->sendDelete($requestData, $path, $method);

        return new GamificationProfile(
            $this->requestBuilder->buildInternalProfileId($profile),
            $profile,
            $this->requestBuilder->buildSignedUser($profile)
        );
    }

    /**
     * @param int $playerId
     * @param PlaceBetRequest $request
     *
     * @throws CaptainUpException
     */
    public function placeBet(int $playerId, PlaceBetRequest $request): void
    {
        $requestData = $this->requestBuilder->buildPlaceBet($playerId, $request);
        $path = $this->requestBuilder->buildPath(RequestBuilder::METHOD_ACTIONS);

        $this->sendPost($requestData, $path, RequestBuilder::PLACE_BET_ACTION);
    }

    /**
     * @param int $playerId
     * @param PayOutBetRequest $request
     *
     * @throws CaptainUpException
     */
    public function payOutBet(int $playerId, PayOutBetRequest $request): void
    {
        $requestData = $this->requestBuilder->buildPayOutBet($playerId, $request);
        $path = $this->requestBuilder->buildPath(RequestBuilder::METHOD_ACTIONS);

        $this->sendPost($requestData, $path, RequestBuilder::PAY_OUT_ACTION);
    }

    /**
     * @param int $playerId
     * @param ConfirmProfileCreationRequest $request
     *
     * @throws CaptainUpException
     */
    public function confirmProfileCreation(int $playerId, ConfirmProfileCreationRequest $request): void
    {
        $requestData = $this->requestBuilder->buildConfirmProfileCreation($playerId, $request);
        $path = $this->requestBuilder->buildPath(RequestBuilder::METHOD_ACTIONS);

        $this->sendPost($requestData, $path, RequestBuilder::CREATE_PROFILE_ACTION);
    }

    /**
     * @param array $requestData
     * @param string $path
     * @param string $action
     *
     * @return stdClass
     * @throws CaptainUpException
     */
    private function sendPost(array $requestData, string $path, string $action): stdClass
    {
        $curl = $this->getCurl();
        $request = http_build_query($requestData);

        try {
            $curl->post($path, $requestData);
            $response = Json::decode($curl->getRawResponse());
        } catch (JsonException|CurlException $e) {
            $response = (string) $curl->getRawResponse();
            $response = $this->filterResponse($response);

            $this->logger->error(
                'captain_up:' . $action . ': ' . $e->getMessage(),
                [
                    'request' => $request,
                    'response' => $response,
                ]
            );

            throw new CaptainUpException('captain_up:' . $action . ': ' . $e->getMessage());
        }

        $this->logger->info(
            'captain_up:' . $action . ': success',
            [
                'request' => $request,
                'response' => $curl->getRawResponse()
            ]
        );

        return $response;
    }

    /**
     * @param array $requestData
     * @param string $path
     * @param string $action
     *
     * @throws CaptainUpException
     */
    private function sendDelete(array $requestData, string $path, string $action): void
    {
        $curl = $this->getCurl();
        $request = http_build_query($requestData);

        try {
            $curl->delete($path, $requestData);
            Json::decode($curl->getRawResponse());
        } catch (JsonException|CurlException $e) {
            $this->logger->error(
                'captain_up:' . $action . ': ' . $e->getMessage(),
                [
                    'request' => $request,
                    'response' => $curl->getRawResponse(),
                ]
            );

            throw new CaptainUpException('captain_up:' . $action . ': ' . $e->getMessage());
        }

        $this->logger->info(
            'captain_up:' . $action . ': success',
            [
                'request' => $request,
                'response' => $curl->getRawResponse()
            ]
        );
    }

    /**
     * @return CurlAdapterInterface
     */
    private function getCurl(): CurlAdapterInterface
    {
        $curl = $this->curlFactory->getCurl();
        $curl->setTimeout(self::TIMEOUT);
        $curl->setConnectTimeout(self::TIMEOUT);
        $curl->setHeader('Content-type', 'application/x-www-form-urlencoded');

        return $curl;
    }
    /**
     * @param string $response
     *
     * @return string
     *
     * @todo: add tests, maybe share with web-api
     */
    private function filterResponse(string $response): string
    {
        $response = str_replace(["\r\n", "\n\r", "\n", "\r", "\t"], ' ', $response);

        // Only for html response.
        if (strpos($response, '<!DOCTYPE html') !== false) {
            $response = preg_replace('/<style(.*?)<\/style>/s', '', $response); // Long styles.
            $response = preg_replace('/<img(.*?)>/s', '', $response); // Images with base64 source.
            $response = preg_replace('/<script(.*?)<\/script>/s', '', $response); // Scripts not needed.
        }

        $response = substr($response, 0, 1024);

        return $response;
    }
}
