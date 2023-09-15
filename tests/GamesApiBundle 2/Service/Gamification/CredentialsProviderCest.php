<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\Gamification;

use GamesApiBundle\Service\Gamification\CredentialsProvider;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class CredentialsProviderCest
 */
final class CredentialsProviderCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     */
    public function testGetters(UnitTester $I): void
    {
        $provider = new CredentialsProvider(
            'my-key',
            'my-token',
            'my-secret',
            'my-api-url',
            'my-sdk-url',
            'my-env-prefix'
        );
        $I->assertEquals('my-key', $provider->getAppKey());
        $I->assertEquals('my-token', $provider->getClientToken());
        $I->assertEquals('my-secret', $provider->getAppSecret());
        $I->assertEquals('my-api-url', $provider->getApiUrl());
        $I->assertEquals('my-sdk-url', $provider->getSdkUrl());
        $I->assertEquals('my-env-prefix', $provider->getEnvPrefix());
    }
}