<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\Gamification;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Combination;
use Acme\SymfonyDb\Entity\PlayerProfile;
use Acme\SymfonyDb\Entity\PokerRunRound;
use Acme\SymfonyDb\Entity\RpsBet;
use Acme\SymfonyDb\Entity\RpsRunRound;
use Acme\SymfonyDb\Entity\RpsRunRoundCard;
use Acme\SymfonyDb\Entity\Subscription;
use CodeigniterSymfonyBridge\PayData;
use CoreBundle\Exception\ValidationException;
use Doctrine\ORM\Tools\ToolsException;
use GamesApiBundle\Service\Gamification\PlaceBetRequestBuilder;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\BetFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\DataProvider;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\PartnerFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\PlayerFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\PlayerProfileFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\PokerRunRoundFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\RpsBetFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\RpsRunRoundCardFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\RpsRunRoundFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\SubscriptionFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\CombinationFixture;
use SymfonyTests\UnitTester;

/**
 * Class PlaceBetRequestBuilderCest
 */
final class PlaceBetRequestBuilderCest extends AbstractUnitTest
{
    protected array $tables = [
        Bet::class,
        PlayerProfile::class,
        Subscription::class,
        Combination::class,
        PokerRunRound::class,
        RpsBet::class,
        RpsRunRound::class,
        RpsRunRoundCard::class,
    ];

    protected array $fixtures = [
        PartnerFixture::class,
        PlayerFixture::class,
        PlayerProfileFixture::class,
        RpsRunRoundFixture::class,
        RpsRunRoundCardFixture::class,
        RpsBetFixture::class,
        SubscriptionFixture::class,
        CombinationFixture::class,
        BetFixture::class,
        PokerRunRoundFixture::class,
    ];

    private PlaceBetRequestBuilder $requestBuilder;

    /**
     * {@inheritDoc}
     */
    protected function setUpFixtures(): void
    {
        parent::setUpFixtures();
        $gameIds = [
            GameDefinition::LUCKY_7,
            GameDefinition::POKER,
            GameDefinition::RPS,
        ];


        $this->fixtureBoostrapper->addGames($gameIds);
        $this->fixtureBoostrapper->addPlayers(1);
        $this->fixtureBoostrapper->addPartners(1);
        $this->fixtureBoostrapper->addLanguages(['en']);
        $this->fixtureBoostrapper->addBets($gameIds, new \DateTimeImmutable('2021-03-08 00:00:00'));
    }

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        /** @var PlaceBetRequestBuilder $requestBuilder */
        $requestBuilder = $I->getContainer()->get(PlaceBetRequestBuilder::class);
        $this->requestBuilder = $requestBuilder;
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testPayinSingle(UnitTester $I): void
    {
        $bet = (new DataProvider(GameDefinition::LUCKY_5))->getNewBet();
        $request = $this->requestBuilder->buildSingle($bet, PayData::TYPE_SINGLE);

        $I->assertEquals('partner-code', $request->getPartnerCode());
        $I->assertEquals('s', $request->getBetType());
        $I->assertEquals(1.0, $request->getBetAmount());
        $I->assertEquals(2.0, $request->getBetAmountInEur());
        $I->assertEquals(1.05, $request->getOddValue());
        $I->assertNull($request->getTieOddValue());
        $I->assertEquals('LT', $request->getCurrencyCode());
        $I->assertIsString($request->getBetTime());
        $I->assertNull($request->getRoundNumber());
        $I->assertEquals([3], $request->getGameIds());
        $I->assertEquals(['54327'], $request->getRunCodes());
        $I->assertEquals(['3-MY_ODD'], $request->getOddClasses());
        $I->assertEquals(null, $request->getTieOddValue());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testPayinCombination(UnitTester $I): void
    {
        $combination = (new DataProvider(GameDefinition::LUCKY_5))->getNewCombination();

        $request = $this->requestBuilder->buildCombination($combination, PayData::TYPE_COMBO);

        $I->assertEquals(['54327', '54327'], $request->getRunCodes());
        $I->assertEquals(['3-MY_ODD', '3-MY_ODD'], $request->getOddClasses());
        $I->assertEquals([3, 3], $request->getGameIds());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testPartnerPayinWithoutApiCode(UnitTester $I): void
    {
        $bet = (new DataProvider(GameDefinition::LUCKY_5))->getNewBet();
        $bet->getPlayer()->getPartner()->setApiCode(null);

        $request = $this->requestBuilder->buildSingle($bet, PayData::TYPE_SINGLE);
        $expectedPartnerCode = '';

        $I->assertEquals($expectedPartnerCode, $request->getPartnerCode());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testPayinWithRoundNumber(UnitTester $I): void
    {
        $bet = (new DataProvider(GameDefinition::POKER))->getNewBet();
        $bet->setRunRoundId(1);

        $request = $this->requestBuilder->buildSingle($bet, PayData::TYPE_SINGLE);

        $I->assertEquals(3, $request->getRoundNumber());
    }
}
