<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\Gamification;

use GamesApiBundle\Service\Gamification\GameResultsFormatter\NullFormatter;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class NullFormatterCest
 */
final class NullFormatterCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     */
    public function testNullFormatterShouldReturnEmptyArray(UnitTester $I): void
    {
        $formatter = new NullFormatter();
        $results = ['1', '2', '3'];

        $I->assertEquals([], $formatter->format($results));
    }
}