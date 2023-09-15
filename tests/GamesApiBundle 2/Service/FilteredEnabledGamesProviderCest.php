<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Service;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\PartnerEnabledGame;
use Codeception\Stub;
use CoreBundle\Service\GameService;
use GamesApiBundle\Service\Initial\Component\FilteredEnabledGamesProvider;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class FilteredEnabledGamesProviderCest
 */
final class FilteredEnabledGamesProviderCest extends AbstractUnitTest
{
    private FilteredEnabledGamesProvider $service;

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $gameService =  Stub::makeEmpty(GameService::class, [
            'getPartnerEnabledGames' => [
                GameDefinition::WHEEL => new PartnerEnabledGame(),
                GameDefinition::MATKA => new PartnerEnabledGame(),
            ]
        ]);

        $this->service = new FilteredEnabledGamesProvider($gameService);
    }

    /**
     * @param UnitTester $I
     */
    public function testMissingHeaderShouldNotFilter(UnitTester $I) : void
    {
        $enabledGames = $this->service->getFilteredEnabledGames(new Partner(), null);

        $I->assertCount(2, $enabledGames);
    }

    /**
     * @param UnitTester $I
     */
    public function testOldVersionShouldFilterWheelAndMatka(UnitTester $I) : void
    {
        $enabledGames = $this->service->getFilteredEnabledGames(new Partner(), '1.6.0');

        $I->assertCount(0, $enabledGames);
    }

    /**
     * @param UnitTester $I
     */
    public function testOldVersionShouldFilterWheel(UnitTester $I) : void
    {
        $enabledGames = $this->service->getFilteredEnabledGames(new Partner(), '1.7.0');

        $I->assertCount(0, $enabledGames);
    }

    /**
     * @param UnitTester $I
     */
    public function testNewVersionShouldNotFilterMatka(UnitTester $I) : void
    {
        $enabledGames = $this->service->getFilteredEnabledGames(new Partner(), '1.7.1');

        $I->assertCount(2, $enabledGames);
    }

    /**
     * @param UnitTester $I
     */
    public function testNewVersionShouldNotFilterWheelAndMatka(UnitTester $I) : void
    {
        $enabledGames = $this->service->getFilteredEnabledGames(new Partner(), '1.7.1');

        $I->assertCount(2, $enabledGames);
    }
}
