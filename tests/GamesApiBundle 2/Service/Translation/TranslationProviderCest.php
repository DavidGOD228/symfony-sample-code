<?php

namespace SymfonyTests\Unit\GamesApiBundle\Service\Translation;

use Acme\SymfonyDb\Entity\HeadsUpCombination;
use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyDb\Entity\OddGroup;
use Acme\SymfonyDb\Entity\PokerCombination;
use Acme\SymfonyDb\Entity\RulesText;
use Acme\SymfonyDb\Entity\TranslationNotification;
use GamesApiBundle\Service\Translation\TranslationProvider;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Translation\HeadsUpCombinationFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Translation\PokerCombinationFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Translation\RulesTextFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Translation\TranslationNotificationFixture;
use SymfonyTests\UnitTester;

/**
 * Class TranslationProviderCest
 */
class TranslationProviderCest extends AbstractUnitTest
{
    protected array $tables = [
        Odd::class,
        OddGroup::class,
        PokerCombination::class,
        HeadsUpCombination::class,
        RulesText::class,
        TranslationNotification::class,
    ];

    protected array $fixtures = [
        PokerCombinationFixture::class,
        HeadsUpCombinationFixture::class,
        TranslationNotificationFixture::class,
        RulesTextFixture::class,
    ];

    /**
     * @var TranslationProvider
     */
    private $service;

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);
        $this->service = $I->getContainer()->get(TranslationProvider::class);
    }

    /**
     * {@inheritDoc}
     */
    protected function setUpFixtures(): void
    {
        parent::setUpFixtures();
        $this->fixtureBoostrapper->addOdds([1, 3, 7, 10], false, 5);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \ReflectionException
     */
    public function testIframeTranslations(UnitTester $I): void
    {
        $translations = $this->service->getIframeTranslations('lt');
        $expected = require __DIR__ . '/../../Fixture/Translation/iframe.out.php';
        $I->assertEquals($expected, $translations);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \ReflectionException
     */
    public function testWidgetTranslations(UnitTester $I): void
    {
        $translations = $this->service->getWidgetTranslations('lt');
        $expected = require __DIR__ . '/../../Fixture/Translation/widget.out.php';
        $I->assertEquals($expected, $translations);
    }

    /**
     * @param UnitTester $I
     */
    public function testDbQueries(UnitTester $I): void
    {
        $logger = $I->getSqlLogger();

        $logger->queries = [];
        $this->service->getIframeTranslations('en');
        $queries = array_values($logger->queries);

        $I->assertRegExp('/^SELECT.*odds/', $queries[0]['sql']);
        $I->assertRegExp('/^SELECT.*rules_texts/', $queries[1]['sql']);
        $I->assertRegExp('/^SELECT.*headsup_combinations/', $queries[2]['sql']);
        $I->assertRegExp('/^SELECT.*poker_combinations/', $queries[3]['sql']);
        $I->assertRegExp('/^SELECT.*grouping_odd_groups/', $queries[4]['sql']);
        $I->assertRegExp('/^SELECT.*translation_notifications/', $queries[5]['sql']);

        $I->assertCount(6, $queries);
    }
}
