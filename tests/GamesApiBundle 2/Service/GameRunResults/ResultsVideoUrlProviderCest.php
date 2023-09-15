<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\GameRunResults;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameRun;
use GamesApiBundle\Service\GameRunResults\ResultsVideoUrlProvider;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;
use DateTimeImmutable;

/**
 * Class ResultsVideoUrlProviderCest
 */
final class ResultsVideoUrlProviderCest extends AbstractUnitTest
{
    private ResultsVideoUrlProvider $service;

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->service = $I->getContainer()->get(ResultsVideoUrlProvider::class);
    }

    /**
     * @param UnitTester $I
     */
    public function testNoUrlShouldBeProvidedIfConfirmationRequired(UnitTester $I): void
    {
        $gameRun = (new GameRun())
            ->setVideoUrl('any')
            ->setVideoConfirmationRequired(true);
        $rootDomain = 'mono.Acme.local';

        $I->assertNull(
            $this->service->getVideoUrl($gameRun, $rootDomain)
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testNoUrlShouldBeProvidedIfNoUrlSet(UnitTester $I): void
    {
        $gameRun = (new GameRun())
            ->setVideoUrl('')
            ->setVideoConfirmationRequired(false);
        $rootDomain = 'mono.Acme.local';

        $I->assertNull(
            $this->service->getVideoUrl($gameRun, $rootDomain)
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testAndarBaharUrlShouldBeProvidedIfGameRunAfterBrandLimitation(UnitTester $I): void
    {
        $gameRun = (new GameRun())
            ->setVideoUrl('/some.mp4')
            ->setGame(Game::createFromId(GameDefinition::ANDAR_BAHAR))
            ->setTime(new DateTimeImmutable('2022-01-01'))
            ->setVideoConfirmationRequired(false);
        $rootDomain = 'mono.Acme.local';

        $I->assertEquals(
            'https://video.Acme.tv/some.mp4',
            $this->service->getVideoUrl($gameRun, $rootDomain)
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testAndarBaharUrlShouldBeProvidedIfPartnerWasBranded(UnitTester $I): void
    {
        $gameRun = (new GameRun())
            ->setVideoUrl('/some.mp4')
            ->setGame(Game::createFromId(GameDefinition::ANDAR_BAHAR))
            ->setTime(new DateTimeImmutable('2021-03-01'))
            ->setVideoConfirmationRequired(false);
        $rootDomain = 'mono.Acme.local';

        $I->assertEquals(
            'https://video.Acme.tv/some.mp4',
            $this->service->getVideoUrl($gameRun, $rootDomain)
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testAnyGameVideoShouldBeAvailableExceptCasesAbove(UnitTester $I): void
    {
        $gameRun = (new GameRun())
            ->setVideoUrl('/some.mp4')
            ->setGame(Game::createFromId(GameDefinition::SPEEDY7))
            ->setTime(new DateTimeImmutable('2021-03-01'))
            ->setVideoConfirmationRequired(false);
        $rootDomain = 'mono.Acme.local';

        $I->assertEquals(
            'https://video.Acme.tv/some.mp4',
            $this->service->getVideoUrl($gameRun, $rootDomain)
        );
    }
}
