<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\Gamification;

use Acme\Contract\GameDefinition;
use GamesApiBundle\Service\Gamification\GameResultsFormatterFactory;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\AndarBaharFormatter;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\BaccaratFormatter;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\HeadsUpFormatter;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\LotteryFormatter;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\NullFormatter;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\PokerFormatter;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\RpsFormatter;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\WarFormatter;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class GameResultsFormatterFactoryCest
 */
final class GameResultsFormatterFactoryCest extends AbstractUnitTest
{
    private GameResultsFormatterFactory $factory;

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->factory = $I->getContainer()->get(GameResultsFormatterFactory::class);
    }

    /**
     * @param UnitTester $I
     */
    public function testLazyLoading(UnitTester $I): void
    {
        $I->assertInstanceOf(
            ServiceSubscriberInterface::class,
            $this->factory
        );

        $I->assertEquals(
            [
                LotteryFormatter::class => LotteryFormatter::class,
                RpsFormatter::class => RpsFormatter::class,
                PokerFormatter::class => PokerFormatter::class,
                BaccaratFormatter::class => BaccaratFormatter::class,
                AndarBaharFormatter::class => AndarBaharFormatter::class,
                HeadsUpFormatter::class => HeadsUpFormatter::class,
                WarFormatter::class => WarFormatter::class,
                NullFormatter::class => NullFormatter::class,
            ],
            GameResultsFormatterFactory::getSubscribedServices()
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testFactoring(UnitTester $I): void
    {
        $I->assertInstanceOf(
            LotteryFormatter::class,
            $this->factory->getFormatter(GameDefinition::LUCKY_7)
        );
        $I->assertInstanceOf(
            RpsFormatter::class,
            $this->factory->getFormatter(GameDefinition::RPS)
        );
        $I->assertInstanceOf(
            PokerFormatter::class,
            $this->factory->getFormatter(GameDefinition::POKER)
        );
        $I->assertInstanceOf(
            BaccaratFormatter::class,
            $this->factory->getFormatter(GameDefinition::BACCARAT)
        );
        $I->assertInstanceOf(
            AndarBaharFormatter::class,
            $this->factory->getFormatter(GameDefinition::ANDAR_BAHAR)
        );
        $I->assertInstanceOf(
            HeadsUpFormatter::class,
            $this->factory->getFormatter(GameDefinition::HEADSUP)
        );
        $I->assertInstanceOf(
            WarFormatter::class,
            $this->factory->getFormatter(GameDefinition::WAR)
        );
        $I->assertInstanceOf(
            NullFormatter::class,
            $this->factory->getFormatter(GameDefinition::SPEEDY7)
        );

        $I->assertInstanceOf(
            NullFormatter::class,
            $this->factory->getFormatter(2312412421412)
        );
    }
}
