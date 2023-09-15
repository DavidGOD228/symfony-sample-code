<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\Gamification;

use Acme\Contract\GameDefinition;
use Doctrine\ORM\Tools\ToolsException;
use GamesApiBundle\Service\Gamification\BetItemsFormatter\DefaultLotteryFormatter;
use GamesApiBundle\Service\Gamification\BetItemsFormatter\DiceDuelFormatter;
use GamesApiBundle\Service\Gamification\BetItemsFormatter\Lucky6Formatter;
use GamesApiBundle\Service\Gamification\BetItemsFormatter\NullFormatter;
use GamesApiBundle\Service\Gamification\BetItemsFormatterFactory;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class BetItemsFormatterFactoryCest
 */
final class BetItemsFormatterFactoryCest extends AbstractUnitTest
{
    private BetItemsFormatterFactory $factory;

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        /** @var BetItemsFormatterFactory $factory */
        $factory = $I->getContainer()->get(BetItemsFormatterFactory::class);
        $this->factory = $factory;
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
                DefaultLotteryFormatter::class => DefaultLotteryFormatter::class,
                DiceDuelFormatter::class => DiceDuelFormatter::class,
                Lucky6Formatter::class => Lucky6Formatter::class,
                NullFormatter::class => NullFormatter::class,
            ],
            BetItemsFormatterFactory::getSubscribedServices()
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testFactoryShouldReturnCorrectFormatter(UnitTester $I): void
    {
        $I->assertInstanceOf(
            DefaultLotteryFormatter::class,
            $this->factory->getFormatter(GameDefinition::LUCKY_7)
        );

        $I->assertInstanceOf(
            DefaultLotteryFormatter::class,
            $this->factory->getFormatter(GameDefinition::LUCKY_5)
        );

        $I->assertInstanceOf(
            DefaultLotteryFormatter::class,
            $this->factory->getFormatter(GameDefinition::WHEEL)
        );

        $I->assertInstanceOf(
            Lucky6Formatter::class,
            $this->factory->getFormatter(GameDefinition::LUCKY_6)
        );

        $I->assertInstanceOf(
            DiceDuelFormatter::class,
            $this->factory->getFormatter(GameDefinition::DICE_DUEL)
        );

        $I->assertInstanceOf(
            NullFormatter::class,
            $this->factory->getFormatter(123456)
        );
    }
}
