<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification;

use Acme\SymfonyDb\Entity\PlayerProfile;
use GamesApiBundle\DataObject\Gamification\ConfirmProfileCreationRequest;
use GamesApiBundle\DataObject\Gamification\PayOutBetRequest;
use GamesApiBundle\DataObject\Gamification\PlaceBetRequest;

/**
 * Class RequestBuilder
 */
final class RequestBuilder
{
    public const METHOD_PLAYERS = 'players';
    public const METHOD_PLAYER_BLOCK = 'players/block';
    public const METHOD_ACTIONS = 'actions';
    public const METHOD_USERS = 'users';

    public const PLACE_BET_ACTION = 'place_bet';
    public const PAY_OUT_ACTION = 'pay_out';
    public const CREATE_PROFILE_ACTION = 'profile_created';

    private CredentialsProvider $credentialsProvider;
    private UserHashGenerator $hashGenerator;

    /**
     * RequestBuilder constructor.
     *
     * @param CredentialsProvider $credentialsProvider
     * @param UserHashGenerator $hashGenerator
     */
    public function __construct(
        CredentialsProvider $credentialsProvider,
        UserHashGenerator $hashGenerator
    )
    {
        $this->credentialsProvider = $credentialsProvider;
        $this->hashGenerator = $hashGenerator;
    }

    /**
     * @param PlayerProfile $profile
     *
     * @return array
     */
    public function buildProfile(PlayerProfile $profile): array
    {
        $envPrefix = $this->credentialsProvider->getEnvPrefix();
        $profileForSigning = $this->hashGenerator->getProfileForSigning($profile, $envPrefix);
        $secret = $this->credentialsProvider->getAppSecret();

        $requestData = [
            'secret' => $secret,
            'user' => $profileForSigning,
        ];

        return $requestData;
    }

    /**
     * @param PlayerProfile $profile
     *
     * @return array
     */
    public function buildProfileForStateChange(PlayerProfile $profile): array
    {
        $secret = $this->credentialsProvider->getAppSecret();

        $requestData = [
            'secret' => $secret,
            'player' => $profile->getExternalId(),
        ];

        return $requestData;
    }

    /**
     * @param int $playerId
     * @param PlaceBetRequest $request
     *
     * @return array
     */
    public function buildPlaceBet(int $playerId, PlaceBetRequest $request): array
    {
        $envPrefix = $this->credentialsProvider->getEnvPrefix();

        $requestData = [
            'app' => $this->credentialsProvider->getAppKey(),
            'secret' => $this->credentialsProvider->getAppSecret(),
            'user' => $this->hashGenerator->getProfileId($playerId, $envPrefix),
            'action[name]' => self::PLACE_BET_ACTION,
            'action[entity][partner_code]' => $envPrefix . ':' . $request->getPartnerCode(),
            'action[entity][bet_type]' => $request->getBetType(),
            'action[entity][bet_amount]' => $request->getBetAmount(),
            'action[entity][bet_amount_eur]' => $request->getBetAmountInEur(),
            'action[entity][odd_value]' => $request->getOddValue(),
            'action[entity][tie_odd_value]' => $request->getTieOddValue(),
            'action[entity][currency_code]' => $request->getCurrencyCode(),
            'action[entity][bet_time]' => $request->getBetTime(),
            'action[entity][round_number]' => $request->getRoundNumber(),
            'action[entity][game_ids]' => $request->getGameIds(),
            'action[entity][run_codes]' => $request->getRunCodes(),
            'action[entity][bet_items]' => $request->getBetItems(),
            'action[entity][odd_classes]' => $request->getOddClasses(),
        ];

        return $requestData;
    }

    /**
     * @param int $playerId
     * @param PayOutBetRequest $request
     *
     * @return array
     */
    public function buildPayOutBet(int $playerId, PayOutBetRequest $request): array
    {
        $envPrefix = $this->credentialsProvider->getEnvPrefix();

        $requestData = [
            'app' => $this->credentialsProvider->getAppKey(),
            'secret' => $this->credentialsProvider->getAppSecret(),
            'user' => $this->hashGenerator->getProfileId($playerId, $envPrefix),
            'action[name]' => self::PAY_OUT_ACTION,
            'action[entity][partner_code]' => $envPrefix . ':' . $request->getPartnerCode(),
            'action[entity][bet_type]' => $request->getBetType(),
            'action[entity][bet_status]' => $request->getBetStatus(),
            'action[entity][amount_won]' => $request->getAmountWon(),
            'action[entity][amount_won_eur]' => $request->getAmountWonEur(),
            'action[entity][odd_value]' => $request->getOddValue(),
            'action[entity][tie_odd_value]' => $request->getTieOddValue(),
            'action[entity][currency_code]' => $request->getCurrencyCode(),
            'action[entity][bet_time]' => $request->getBetTime(),
            'action[entity][round_number]' => $request->getRoundNumber(),
            'action[entity][game_ids]' => $request->getGameIds(),
            'action[entity][run_codes]' => $request->getRunCodes(),
            'action[entity][bet_items]' => $request->getBetItems(),
            'action[entity][odd_classes]' => $request->getOddClasses(),
            'action[entity][game_results]' => $request->getResults(),
        ];

        return $requestData;
    }

    /**
     * @param int $playerId
     * @param ConfirmProfileCreationRequest $request
     *
     * @return array
     */
    public function buildConfirmProfileCreation(int $playerId, ConfirmProfileCreationRequest $request): array
    {
        $envPrefix = $this->credentialsProvider->getEnvPrefix();

        $requestData = [
            'app' => $this->credentialsProvider->getAppKey(),
            'secret' => $this->credentialsProvider->getAppSecret(),
            'user' => $this->hashGenerator->getProfileId($playerId, $envPrefix),
            'action[name]' => self::CREATE_PROFILE_ACTION,
            'action[date_time]' => $request->getDateTime(),
        ];

        return $requestData;
    }

    /**
     * @param string $method
     *
     * @return string
     */
    public function buildPath(string $method): string
    {
        $apiUrl = $this->credentialsProvider->getApiUrl();
        $appKey = $this->credentialsProvider->getAppKey();

        if ($method === self::METHOD_ACTIONS) {
            $path = $apiUrl . '/' . $method;
        } else {
            $path = $apiUrl . '/app/' . $appKey . '/' . $method;
        }

        return $path;
    }

    /**
     * @param PlayerProfile $profile
     *
     * @return string
     */
    public function buildInternalProfileId(PlayerProfile $profile): string
    {
        $internalProfileId = $this->hashGenerator->getProfileId(
            $profile->getPlayer()->getId(),
            $this->credentialsProvider->getEnvPrefix()
        );

        return $internalProfileId;
    }

    /**
     * @param PlayerProfile $profile
     *
     * @return string
     */
    public function buildSignedUser(PlayerProfile $profile): string
    {
        $envPrefix = $this->credentialsProvider->getEnvPrefix();
        $profileForSigning = $this->hashGenerator->getProfileForSigning($profile, $envPrefix);
        $secret = $this->credentialsProvider->getAppSecret();
        $signedUser = $this->hashGenerator->generate($profileForSigning, $secret);

        return $signedUser;
    }
}
