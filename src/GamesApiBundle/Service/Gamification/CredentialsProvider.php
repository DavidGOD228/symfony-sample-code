<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification;

/**
 * Class CredentialsProvider
 */
final class CredentialsProvider
{
    private string $appKey;

    private string $clientToken;

    private string $appSecret;

    private string $apiUrl;

    private string $sdkUrl;

    private string $envPrefix;

    /**
     * CredentialsProvider constructor.
     *
     * @param string $appKey
     * @param string $clientToken
     * @param string $appSecret
     * @param string $apiUrl
     * @param string $sdkUrl
     * @param string $envPrefix
     */
    public function __construct(
        string $appKey,
        string $clientToken,
        string $appSecret,
        string $apiUrl,
        string $sdkUrl,
        string $envPrefix
    )
    {
        $this->appKey = $appKey;
        $this->clientToken = $clientToken;
        $this->appSecret = $appSecret;
        $this->apiUrl = $apiUrl;
        $this->sdkUrl = $sdkUrl;
        $this->envPrefix = $envPrefix;
    }

    /**
     * @return string
     */
    public function getAppKey(): string
    {
        return $this->appKey;
    }

    /**
     * @return string
     */
    public function getClientToken(): string
    {
        return $this->clientToken;
    }

    /**
     * @return string
     */
    public function getAppSecret(): string
    {
        return $this->appSecret;
    }

    /**
     * @return string
     */
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    /**
     * @return string
     */
    public function getSdkUrl(): string
    {
        return $this->sdkUrl;
    }

    /**
     * @return string
     */
    public function getEnvPrefix(): string
    {
        return $this->envPrefix;
    }
}