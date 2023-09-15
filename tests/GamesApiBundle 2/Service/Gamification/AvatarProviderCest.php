<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\Gamification;

use Doctrine\ORM\Tools\ToolsException;
use Eastwest\Json\Json;
use GamesApiBundle\Service\Gamification\AvatarProvider;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;
use Acme\SymfonyRequest\Request;

/**
 * Class AvatarProviderCest
 */
final class AvatarProviderCest extends AbstractUnitTest
{
    private const DOMAIN = 'Acmetv.eu';

    private AvatarProvider $provider;

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        /** @var AvatarProvider $provider */
        $provider = $I->getContainer()->get(AvatarProvider::class);
        $this->provider = $provider;

        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            ['SERVER_NAME' => self::DOMAIN]
        );

        $I->getRequestStack()->push($request);
    }

    /**
     * @param UnitTester $I
     */
    public function testGet(UnitTester $I): void
    {
        $expectedResponse = Json::decode(
            file_get_contents(__DIR__ . '/../../Fixture/Gamification/response/avatars-data.response'),
            true
        );

        $I->assertEquals($expectedResponse, $this->provider->getPreset(self::DOMAIN));
    }

    /**
     * @param UnitTester $I
     */
    public function testGetDefaultUrl(UnitTester $I): void
    {
        $I->assertEquals(
            'https://static.Acmetv.eu/gamification/player_profile_avatars/avatar_1.jpg',
            $this->provider->getDefaultUrl(self::DOMAIN)
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testGetAvatars(UnitTester $I): void
    {
        $expectedResponse = [
            'avatar_1',
            'avatar_2',
            'avatar_3',
            'avatar_4',
            'avatar_5',
            'avatar_6',
            'avatar_7',
            'avatar_8',
            'avatar_9',
            'avatar_10',
            'avatar_11',
            'avatar_12',
            'avatar_13',
            'avatar_14',
        ];

        $I->assertEquals($expectedResponse, $this->provider->getAvailableAvatars());
    }
}
