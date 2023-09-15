<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Service;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Combination;
use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Entity\TransactionPayout;
use Codeception\Stub;
use CoreBundle\Enum\BetType;
use CoreBundle\Service\CacheService;
use CoreBundle\Service\Event\EventBroadcaster;
use DateTimeImmutable;
use Exception;
use GamesApiBundle\Service\WinningAnnounceService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class WinningAnnounceServiceCest
 */
final class WinningAnnounceServiceCest extends AbstractUnitTest
{
    protected array $tables = [
        TransactionPayout::class,
        Combination::class,
    ];

    /**
     * {@inheritDoc}
     */
    protected function setUpFixtures(): void
    {
        parent::setUpFixtures();

        $gameIds = [
            GameDefinition::SPEEDY7,
            GameDefinition::WHEEL,
            GameDefinition::DICE_DUEL,
            GameDefinition::POKER,
            GameDefinition::BACCARAT,
        ];

        $this->fixtureBoostrapper->addBets($gameIds);
    }

    /**
     * @param UnitTester $I
     */
    public function testShouldNotBroadcastForBetsOnUnsupportedGame(UnitTester $I): void
    {
        $em = $this->getEntityManager();

        /** @var Bet $bet1 */
        $bet1 = $this->getEntityByReference('bet:11:1');
        $em->persist($bet1);

        /** @var Bet $bet2 */
        $bet2 = $this->getEntityByReference('bet:11:2');
        $em->persist($bet2);

        /** @var WinningAnnounceService $service */
        $service = $I->getContainer()->get(WinningAnnounceService::class);

        $service->processIncomingBets([$bet1, $bet2], BetType::getSingle());
        $service->processPaidOutBet($bet1);
        $service->processPaidOutBet($bet2);

        $service->processIncomingBets([$bet1, $bet2], BetType::getSingle());
        $service->processPaidOutBet($bet1);
        $service->processPaidOutBet($bet2);

        $actualEvents = $I->getWsRedis()->getPublishedEvents();
        $I->assertArrayNotHasKey('player_won', $actualEvents);
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testShouldBroadcastForBetsForCombinedAmount(UnitTester $I): void
    {
        $em = $this->getEntityManager();

        /** @var Bet $bet1 */
        $bet1 = $this->getEntityByReference('bet:10:1');
        $bet1->setAmountWon(1.234);
        $em->persist($bet1);

        /** @var Bet $bet2 */
        $bet2 = $this->getEntityByReference('bet:10:2');
        $bet2->setAmountWon(1.234);
        $em->persist($bet2);

        /** @var WinningAnnounceService $service */
        $service = $I->getContainer()->get(WinningAnnounceService::class);

        $service->processIncomingBets([$bet1, $bet2], BetType::getSingle());
        $service->processPaidOutBet($bet1);
        $service->processPaidOutBet($bet2);

        $actualEvents = $I->getWsRedis()->getPublishedEvents()['player_won'];
        $I->assertCount(1, $actualEvents);
        $I->assertStringContainsString('"amountWon":"2.468"', $actualEvents[0]);
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testShouldHaveSeparateBroadcastsForGameRuns(UnitTester $I): void
    {
        $nextGameRun = (new GameRun())
            ->setGame($this->getEntityByReference('game:10'))
            ->setTime(new DateTimeImmutable('yesterday -1 hour'))
            ->setCode(10 . 'DrawCode')
            ->setIsReturned(false)
            ->setVideoConfirmationRequired(false)
            ->setResultsEntered(false)
            ->setIsImported(true)
            ->setPublishedDate(new DateTimeImmutable('yesterday -1 hour'))
        ;

        $this->saveEntity($nextGameRun);

        /** @var Bet $bet1 */
        $bet1 = $this->getEntityByReference('bet:10:1');
        $bet1->setAmountWon(1.111);
        $this->saveEntity($bet1);

        /** @var Bet $bet2 */
        $bet2 = $this->getEntityByReference('bet:10:2');
        $bet2->setAmountWon(2.111);
        $bet2->setGameRun($nextGameRun);
        $this->saveEntity($bet2);

        /** @var WinningAnnounceService $service */
        $service = $I->getContainer()->get(WinningAnnounceService::class);
        $service->processIncomingBets([$bet1, $bet2], BetType::getSingle());
        $service->processPaidOutBet($bet1);
        $service->processPaidOutBet($bet2);

        $actualEvents = $I->getWsRedis()->getPublishedEvents()['player_won'];

        $I->assertCount(2, $actualEvents);
        $I->assertStringContainsString('"amountWon":"1.111"', $actualEvents[0]);
        $I->assertStringContainsString('"amountWon":"2.111"', $actualEvents[1]);
    }

    /**
     * @param UnitTester $I
     */
    public function testShouldNotBroadcastUntilAllBetsProcessed(UnitTester $I): void
    {
        /** @var Bet $bet1 */
        $bet1 = $this->getEntityByReference('bet:10:1');
        /** @var Bet $bet2 */
        $bet2 = $this->getEntityByReference('bet:10:2');

        /** @var WinningAnnounceService $service */
        $service = $I->getContainer()->get(WinningAnnounceService::class);

        $service->processIncomingBets([$bet1, $bet2], BetType::getSingle());

        $service->processPaidOutBet($bet1);
        $I->assertEmpty($I->getWsRedis()->getPublishedEvents());

        $service->processPaidOutBet($bet2);
        $I->assertCount(1, $I->getWsRedis()->getPublishedEvents()['player_won']);
    }

    /**
     * @param UnitTester $I
     */
    public function testShouldBroadcastReturnedBetForSingleRoundGame(UnitTester $I): void
    {
        /** @var Bet $bet */
        $bet = $this->getEntityByReference('bet:10:1');
        $bet->setIsReturned(true)
            ->setStatus(Bet::STATUS_RETURNED)
        ;
        $this->saveEntity($bet);

        /** @var WinningAnnounceService $service */
        $service = $I->getContainer()->get(WinningAnnounceService::class);

        $service->processPaidOutBet($bet);

        $I->assertEmpty($I->getTestLogger()->getRecords());
        $I->assertCount(1, $I->getWsRedis()->getPublishedEvents());
    }

    /**
     * @param UnitTester $I
     */
    public function testShouldNotBroadcastReturnedBetForMultiRoundGame(UnitTester $I): void
    {
        /** @var Bet $bet */
        $bet = $this->getEntityByReference('bet:5:1');
        $bet->setIsReturned(true)
            ->setStatus(Bet::STATUS_RETURNED)
        ;
        $this->saveEntity($bet);

        /** @var WinningAnnounceService $service */
        $service = $I->getContainer()->get(WinningAnnounceService::class);

        $service->processPaidOutBet($bet);

        $I->assertEmpty($I->getTestLogger()->getRecords());
        $I->assertEmpty($I->getWsRedis()->getPublishedEvents());
    }

    /**
     * @param UnitTester $I
     */
    public function testShouldBroadcastBacccaratTie(UnitTester $I): void
    {
        /** @var Bet $bet */
        $bet = $this->getEntityByReference('bet:6:valid');
        $bet->setIsReturned(true)
            ->setStatus(Bet::STATUS_RETURNED)
        ;
        $this->saveEntity($bet);

        /** @var WinningAnnounceService $service */
        $service = $I->getContainer()->get(WinningAnnounceService::class);

        $service->processPaidOutBet($bet);

        $I->assertEmpty($I->getTestLogger()->getRecords());
        $I->assertEquals(
            [
                "player_won" => [
                    '{"playerId":1,"gameId":6,"runId":5,"amountWon":"453"}',
                ],
            ],
            $I->getWsRedis()->getPublishedEvents()
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testShouldNotBroadcastReturnedGameRun(UnitTester $I): void
    {
        /** @var Bet $bet */
        $bet = $this->getEntityByReference('bet:6:valid');
        $bet->setIsReturned(true)
            ->setStatus(Bet::STATUS_RETURNED)
        ;
        $gameRun = $bet->getGameRun();
        $gameRun->setIsReturned(true);
        $this->saveEntity($gameRun);
        $this->saveEntity($bet);

        /** @var WinningAnnounceService $service */
        $service = $I->getContainer()->get(WinningAnnounceService::class);

        $service->processPaidOutBet($bet);

        $I->assertEmpty($I->getTestLogger()->getRecords());
        $I->assertEmpty($I->getWsRedis()->getPublishedEvents());
    }

    /**
     * @param UnitTester $I
     */
    public function testShouldNotBroadcastBetWithWithZeroWinning(UnitTester $I): void
    {
        /** @var Bet $bet */
        $bet = $this->getEntityByReference('bet:6:valid');
        $bet->setAmountWon(0);
        $this->saveEntity($bet);

        /** @var WinningAnnounceService $service */
        $service = $I->getContainer()->get(WinningAnnounceService::class);

        $service->processPaidOutBet($bet);

        $I->assertEmpty($I->getTestLogger()->getRecords());
        $I->assertEmpty($I->getWsRedis()->getPublishedEvents());
    }

    /**
     * @param UnitTester $I
     */
    public function testShouldBroadcastWithNoBetCountInCache(UnitTester $I): void
    {
        /** @var Bet $bet */
        $bet = $this->getEntityByReference('bet:10:valid');

        /** @var WinningAnnounceService $service */
        $service = $I->getContainer()->get(WinningAnnounceService::class);

        $service->processPaidOutBet($bet);

        $I->assertEmpty($I->getTestLogger()->getRecords());
        $I->assertEquals(
            [
                "player_won" => [
                    '{"playerId":1,"gameId":10,"runId":3,"amountWon":"453"}',
                ],
            ],
            $I->getWsRedis()->getPublishedEvents()
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testShouldNotBroadcastActiveBet(UnitTester $I): void
    {
        /** @var Bet $bet */
        $bet = $this->getEntityByReference('bet:10:valid');

        $bet->setStatus(Bet::STATUS_ACTIVE);
        $this->saveEntity($bet);

        /** @var WinningAnnounceService $service */
        $service = $I->getContainer()->get(WinningAnnounceService::class);

        $service->processPaidOutBet($bet);

        $I->assertEmpty($I->getTestLogger()->getRecords());
        $I->assertEmpty($I->getWsRedis()->getPublishedEvents());
    }

    /**
     * @param UnitTester $I
     */
    public function testShouldBroadcastCombinationWinAmountInsteadOfBetWinAmount(UnitTester $I): void
    {
        $combination = $this->getCombinationExample();

        /** @var Bet $nonCombinationBet */
        $nonCombinationBet = $this->getEntityByReference('bet:10:valid');
        $nonCombinationBet
            ->setStatus(Bet::STATUS_WON)
            ->setAmountWon(9.999)
        ;

        $this->saveEntity($nonCombinationBet);

        /** @var WinningAnnounceService $service */
        $service = $I->getContainer()->get(WinningAnnounceService::class);

        $service->processIncomingBets($combination->getBets()->toArray(), BetType::getCombination());
        $service->processIncomingBets([$nonCombinationBet], BetType::getSingle());

        $service->processPaidOutBet($combination->getFirstBet());
        $I->assertEmpty(
            $I->getWsRedis()->getPublishedEvents(),
            'Combination win should not have been broadcast before the last bet was paid out.'
        );

        $service->processPaidOutBet($nonCombinationBet);
        $I->assertEquals(
            [
                "player_won" => [
                    '{"playerId":1,"gameId":10,"runId":3,"amountWon":"19.999"}',
                ],
            ],
            $I->getWsRedis()->getPublishedEvents(),
            'Message should contain combined winning from combination and single bet.'
        );

        $I->assertEmpty($I->getTestLogger()->getRecords());
    }

    /**
     * @param UnitTester $I
     */
    public function testShouldNotBroadcastBetWinIfCombinationWasNotWon(UnitTester $I): void
    {
        $combination = $this->getCombinationExample();

        $combination
            ->setAmountWon(0);
        $this->saveEntity($combination);

        /** @var WinningAnnounceService $service */
        $service = $I->getContainer()->get(WinningAnnounceService::class);

        $service->processIncomingBets($combination->getBets()->toArray(), BetType::getCombination());

        $service->processPaidOutBet($combination->getFirstBet());
        $I->assertEmpty(
            $I->getWsRedis()->getPublishedEvents(),
            'No win should be broadcast because combination was not won.'
        );
        $I->assertEmpty($I->getTestLogger()->getRecords());
    }

    /**
     * @param UnitTester $I
     */
    public function testShouldBroadcastCombinationWinIfCombinationReturned(UnitTester $I): void
    {
        $combination = $this->getCombinationExample();
        $combination
            ->setAmount(3)
            ->setAmountWon(3)
        ;
        $this->saveEntity($combination);

        $combinationBet1 = $combination->getBets()->first();
        $combinationBet1
            ->setStatus(Bet::STATUS_RETURNED)
            ->setIsReturned(true)
            ->setAmount(1)
            ->setAmountWon(1)
        ;

        $this->saveEntity($combinationBet1);

        $combinationBet2 = $combination->getBets()->last();
        $combinationBet2
            ->setStatus(Bet::STATUS_RETURNED)
            ->setIsReturned(true)
            ->setAmount(2)
            ->setAmountWon(2)
        ;

        $this->saveEntity($combinationBet2);

        /** @var WinningAnnounceService $service */
        $service = $I->getContainer()->get(WinningAnnounceService::class);

        $service->processIncomingBets([$combinationBet1, $combinationBet2], BetType::getCombination());

        $service->processPaidOutBet($combination->getFirstBet());

        $I->assertEquals(
            [
                "player_won" => [
                    '{"playerId":1,"gameId":10,"runId":3,"amountWon":"3"}',
                ],
            ],
            $I->getWsRedis()->getPublishedEvents(),
            'Message should contain combination win amount.'
        );
        $I->assertEmpty($I->getTestLogger()->getRecords());
    }

    /**
     * @param UnitTester $I
     */
    public function testShouldBroadcastCombinationWinIfOnlyOneBetWasReturned(UnitTester $I): void
    {
        $combination = $this->getCombinationExample();

        $combinationBet2 = $combination->getBets()->last();
        $combinationBet2
            ->setStatus(Bet::STATUS_RETURNED)
            ->setIsReturned(true)
            ->setAmount(2)
            ->setAmountWon(2)
        ;

        $this->saveEntity($combinationBet2);

        /** @var WinningAnnounceService $service */
        $service = $I->getContainer()->get(WinningAnnounceService::class);

        $service->processIncomingBets($combination->getBets()->toArray(), BetType::getCombination());
        $service->processPaidOutBet($combination->getFirstBet());

        $I->assertEquals(
            [
                "player_won" => [
                    '{"playerId":1,"gameId":10,"runId":3,"amountWon":"10"}',
                ],
            ],
            $I->getWsRedis()->getPublishedEvents(),
            'Should have broadcast message for full combination win amount.'
        );
        $I->assertEmpty($I->getTestLogger()->getRecords());
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testShouldLogPublishingError(UnitTester $I): void
    {
        $broadcaster = Stub::make(
            EventBroadcaster::class,
            [
                'broadcast' => function () {
                    throw new Exception();
                },
            ]
        );

        $service = new WinningAnnounceService(
            $I->getContainer()->get(CacheService::class),
            $I->getContainer()->get(SerializerInterface::class),
            $broadcaster,
            $I->getContainer()->get(LoggerInterface::class)
        );

        /** @var Bet $bet */
        $bet = $this->getEntityByReference('bet:10:valid');
        $service->processPaidOutBet($bet);

        $I->assertEquals(
            'Failed to broadcast PlayerWonEvent: ',
            $I->getTestLogger()->getRecords()[0]['message']
        );
    }

    /**
     * @return Combination
     */
    private function getCombinationExample(): Combination
    {
        /** @var Player $player */
        $player = $this->getEntityByReference('player:1');

        /** @var Currency $currency */
        $currency = $this->getEntityByReference('currency:eur');

        $combination = (new Combination())
            ->setCurrency($currency)
            ->setAmount(3)
            ->setAmountWon(10)
            ->setOddValue(2.34)
            ->setOddValueFinal(2.35)
            ->setPlayer($player)
            ->setDateCreated(new DateTimeImmutable())
            ->setInvalid(false)
        ;

        /** @var Bet $combinationBet1 */
        $combinationBet1 = $this->getEntityByReference('bet:10:1');
        $combinationBet1->setCombination($combination)
            ->setAmountWon(1)
        ;

        /** @var Bet $combinationBet2 */
        $combinationBet2 = $this->getEntityByReference('bet:10:2');
        $combinationBet2->setCombination($combination)
            ->setAmountWon(2)
        ;

        $combination->addBet($combinationBet1)
            ->addBet($combinationBet2)
        ;
        $this->saveEntity($combination);

        return $combination;
    }
}
