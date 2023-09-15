<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\GameRunResults;

use Acme\Contract\GameDefinition;
use GamesApiBundle\Service\GameRunResults\Formatter\AndarBaharFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\BaccaratFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\HeadsUpFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\LotteryFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\MatkaBazaarFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\NullFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\PokerFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\RpsFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\Speedy7Formatter;
use GamesApiBundle\Service\GameRunResults\Formatter\StsPokerFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\WarOfBetsFormatter;
use GamesApiBundle\Service\GameRunResults\FormatterFactory;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class FormatterFactoryCest
 */
final class FormatterFactoryCest extends AbstractUnitTest
{
    private FormatterFactory $factory;

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->factory = $I->getContainer()->get(FormatterFactory::class);
    }

    /**
     * @param UnitTester $I
     */
    public function testLazyLoadingServices(UnitTester $I): void
    {
        $I->assertInstanceOf(ServiceSubscriberInterface::class, $this->factory);

        $I->assertEquals(
            [
                LotteryFormatter::class => LotteryFormatter::class,
                Speedy7Formatter::class => Speedy7Formatter::class,
                RpsFormatter::class => RpsFormatter::class,
                PokerFormatter::class => PokerFormatter::class,
                StsPokerFormatter::class => StsPokerFormatter::class,
                BaccaratFormatter::class => BaccaratFormatter::class,
                AndarBaharFormatter::class => AndarBaharFormatter::class,
                HeadsUpFormatter::class => HeadsUpFormatter::class,
                WarOfBetsFormatter::class => WarOfBetsFormatter::class,
                MatkaBazaarFormatter::class => MatkaBazaarFormatter::class,
                NullFormatter::class => NullFormatter::class,
            ],
            FormatterFactory::getSubscribedServices()
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testFactoring(UnitTester $I): void
    {
        $I->assertInstanceOf(
            PokerFormatter::class,
            $this->factory->getFormatter(GameDefinition::POKER)
        );
        $I->assertInstanceOf(
            StsPokerFormatter::class,
            $this->factory->getFormatter(GameDefinition::STS_POKER)
        );
        $I->assertInstanceOf(
            WarOfBetsFormatter::class,
            $this->factory->getFormatter(GameDefinition::WAR)
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
            Speedy7Formatter::class,
            $this->factory->getFormatter(GameDefinition::SPEEDY7)
        );
        $I->assertInstanceOf(
            RpsFormatter::class,
            $this->factory->getFormatter(GameDefinition::RPS)
        );
        $I->assertInstanceOf(
            LotteryFormatter::class,
            $this->factory->getFormatter(GameDefinition::LUCKY_7)
        );
        $I->assertInstanceOf(
            HeadsUpFormatter::class,
            $this->factory->getFormatter(GameDefinition::HEADSUP)
        );
        $I->assertInstanceOf(
            MatkaBazaarFormatter::class,
            $this->factory->getFormatter(GameDefinition::MATKA)
        );
        $I->assertInstanceOf(
            NullFormatter::class,
            $this->factory->getFormatter(3258973)
        );
    }
}
