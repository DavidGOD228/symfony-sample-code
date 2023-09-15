<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service;

use Acme\SymfonyDb\Entity\Country;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\PartnerFirewall;
use Acme\SymfonyDb\Entity\User;
use Acme\SymfonyDb\Entity\UserRole;
use Codeception\Stub;
use GamesApiBundle\Service\PlayerService;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\CoreBundle\Fixture\CountryFixture;
use SymfonyTests\Unit\CoreBundle\Fixture\PartnerFirewallFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\UserFixture;
use SymfonyTests\UnitTester;

/**
 * Class PlayerServiceCest
 */
final class PlayerServiceCest extends AbstractUnitTest
{
    private PlayerService $service;

    protected array $tables = [
        PartnerFirewall::class,
        Country::class,
        User::class,
        UserRole::class
    ];

    protected array $fixtures = [
        CountryFixture::class,
        UserFixture::class,
        PartnerFirewallFixture::class
    ];

    /** @inheritDoc */
    protected function setUpFixtures(): void
    {
        $this->fixtureBoostrapper->addPartners(2, true);
        $this->fixtureBoostrapper->addPlayers();
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->service = $I->getContainer()->get(PlayerService::class);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testShouldReInitSession(UnitTester $I): void
    {
        /* @var \Acme\SymfonyDb\Entity\Partner $partner */
        $partner = Stub::makeEmpty(Partner::class, ['getApiId' => 'isoftbet']);

        $I->assertTrue($this->service->shouldReinitSession($partner));
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testShouldNotReInitSession(UnitTester $I): void
    {
        /* @var \Acme\SymfonyDb\Entity\Partner $partner */
        $partner = $this->getEntityByReference('partner:1');

        $I->assertFalse($this->service->shouldReinitSession($partner));
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testReInitSession(UnitTester $I): void
    {
        /* @var \Acme\SymfonyDb\Entity\Partner $partner */
        $partner = $this->getEntityByReference('partner:1');

        $I->assertFalse(
            $this->service->reinitSession($partner, '123', 1, false, false)
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testRefreshTokenAuthWhenPartnerBlocked(UnitTester $I): void
    {
        $ltIp = '195.14.183.126';
        /** @var \Acme\SymfonyDb\Entity\Partner $partner1 */
        $partner1 = $this->getEntityByReference('partner:1');
        /** @var \Acme\SymfonyDb\Entity\Partner $partner2 */
        $partner2 = $this->getEntityByReference('partner:2');
        /** @var \Acme\SymfonyDb\Entity\Player $player */
        $player = $this->getEntityByReference('player:1');

        $player->setPartner($partner1);
        $actual = $this->service->refreshToken($player, $ltIp);
        $I->assertTrue($actual);

        /**
         * For Partner 2 LT already is blocked
         *
         * @see PartnerFirewallFixture
         * */
        $player->setPartner($partner2);
        $actual = $this->service->refreshToken($player, $ltIp);
        $I->assertFalse($actual);
    }
}
