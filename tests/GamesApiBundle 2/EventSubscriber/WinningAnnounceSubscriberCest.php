<?php

namespace SymfonyTests\Unit\GamesApiBundle\EventSubscriber;

use Acme\SymfonyDb\Entity\Bet;
use Codeception\Example;
use Codeception\Stub;
use CodeigniterSymfonyBridge\PayData;
use CodeigniterSymfonyBridge\PayInWithBetEvent;
use CodeigniterSymfonyBridge\PayOutEvent;
use CodeigniterSymfonyBridge\PayOutWithBetEvent;
use CoreBundle\Enum\BetType;
use Exception;
use GamesApiBundle\EventSubscriber\WinningAnnounceSubscriber;
use GamesApiBundle\Service\WinningAnnounceService;
use Psr\Log\LoggerInterface;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class WinningAnnounceSubscriberCest
 *
 * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassBeforeLastUsed
 */
class WinningAnnounceSubscriberCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     */
    public function testGetSubscribedEvents(UnitTester $I): void
    {
        $events = WinningAnnounceSubscriber::getSubscribedEvents();
        $I->assertSame(2, count($events));
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testShouldPassSingleBetPayinToService(UnitTester $I): void
    {
        $bets = [new Bet()];
        $event = new PayInWithBetEvent(1, 1, new PayData(3, 's', 5, 2), $bets);

        $actualBets = $actualBetType = null;

        $service = Stub::makeEmpty(WinningAnnounceService::class, [
            'processIncomingBets' =>
                static function (array $bets, BetType $betType) use (&$actualBets, &$actualBetType) {
                    $actualBets = $bets;
                    $actualBetType = $betType;
                },
        ]);

        $this->stubsToVerify[] = $service;

        /** @var WinningAnnounceSubscriber $subscriber */
        $subscriber = Stub::make(WinningAnnounceSubscriber::class, [
            'winningAnnounceService' => $service,
        ]);

        $subscriber->onPostPayIn($event);

        $I->assertEquals(BetType::getSingle(), $actualBetType);
        $I->assertEquals($bets, $actualBets);

        $service = Stub::makeEmpty(WinningAnnounceService::class, [
            'processIncomingBets' => Stub\Expected::once(function () {
                throw new Exception();
            }),
        ]);
        $this->stubsToVerify[] = $service;

        $logger = Stub::makeEmpty(LoggerInterface::class, [
            'error' => Stub\Expected::once()
        ]);
        $this->stubsToVerify[] = $logger;

        $subscriber = new WinningAnnounceSubscriber(
            $service,
            $logger
        );

        $subscriber->onPostPayIn($event);
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testShouldPassCombinationBetsPayinToService(UnitTester $I): void
    {
        $bets = [new Bet(), new Bet()];
        $event = new PayInWithBetEvent(1, 1, new PayData(3, 'c', 5, 2), $bets);

        $actualBets = $actualBetType = null;

        $service = Stub::makeEmpty(WinningAnnounceService::class, [
            'processIncomingBets' =>
                static function (array $bets, BetType $betType) use (&$actualBets, &$actualBetType) {
                    $actualBets = $bets;
                    $actualBetType = $betType;
                },
        ]);

        $this->stubsToVerify[] = $service;

        /** @var WinningAnnounceSubscriber $subscriber */
        $subscriber = Stub::make(WinningAnnounceSubscriber::class, [
            'winningAnnounceService' => $service,
        ]);

        $subscriber->onPostPayIn($event);

        $I->assertEquals(BetType::getCombination(), $actualBetType);
        $I->assertEquals($bets, $actualBets);
    }

    /**
     * @throws Exception
     */
    public function testShouldLogPayinException(): void
    {
        $bets = [new Bet()];
        $event = new PayInWithBetEvent(1, 1, new PayData(3, 's', 5, 2), $bets);

        $service = Stub::makeEmpty(WinningAnnounceService::class, [
            'processIncomingBets' => Stub\Expected::once(function () {
                throw new Exception();
            }),
        ]);
        $this->stubsToVerify[] = $service;

        $logger = Stub::makeEmpty(LoggerInterface::class, [
            'error' => Stub\Expected::once()
        ]);
        $this->stubsToVerify[] = $logger;

        $subscriber = new WinningAnnounceSubscriber(
            $service,
            $logger
        );

        $subscriber->onPostPayIn($event);
    }

    /**
     * @param UnitTester $I
     * @param Example $example
     *
     * @throws Exception
     * @dataProvider betTypeProvider
     */
    public function testShouldPassCorrectBetTypeOnPostPayout(UnitTester $I, Example $example) : void
    {
        $bet = Stub::make(Bet::class, ['id' => 1]);
        /** @var PayOutEvent $event */
        $event = Stub::makeEmpty(
            PayOutWithBetEvent::class,
            [
                'getBetType' => $example['paydata_type'],
                'getBets' => [$bet],
            ]
        );

        $actualBetType = null;
        $bet = null;
        $service = Stub::makeEmpty(WinningAnnounceService::class, [
            'processPaidOutBet' => function (Bet $bet, BetType $betType) use (&$actualBet, &$actualBetType) {
                $actualBetType = $betType;
                $actualBet = $bet;
            }
        ]);

        /** @var WinningAnnounceSubscriber $subscriber */
        $subscriber = Stub::make(WinningAnnounceSubscriber::class, [
            'winningAnnounceService' => $service,
        ]);

        $subscriber->onPostPayOut($event);

        $I->assertEquals($example['bet_type'], $actualBetType);
    }

    /**
     * @return array[]
     */
    public function betTypeProvider() : array
    {
        return [
            ['paydata_type' => 'c', 'bet_type' => BetType::getCombination()],
            ['paydata_type' => 's', 'bet_type' => BetType::getSingle()],
            ['paydata_type' => 'sub', 'bet_type' => BetType::getSubscription()],
        ];
    }

    /**
     * @throws Exception
     */
    public function testOnPostPayOut(): void
    {
        $bet = Stub::make(Bet::class, ['id' => 1]);
        /** @var PayOutEvent $event */
        $event = new PayOutWithBetEvent(
            1,
            2,
            new PayData(3, 's', 5, 2),
            [$bet],
        );

        $service = Stub::makeEmpty(WinningAnnounceService::class, [
            'processPaidOutBet' => Stub\Expected::once(
                function (Bet $bet, BetType $betType) use (&$actualBet, &$actualBetType) {
                    $actualBetType = $betType;
                    $actualBet = $bet;
                }
            ),
        ]);
        $this->stubsToVerify[] = $service;

        /** @var WinningAnnounceSubscriber $subscriber */
        $subscriber = Stub::make(WinningAnnounceSubscriber::class, [
            'winningAnnounceService' => $service,
        ]);

        $subscriber->onPostPayOut($event);

        $service = Stub::makeEmpty(WinningAnnounceService::class, [
            'processPaidOutBet' => Stub\Expected::once(function () {
                throw new Exception();
            }),
        ]);
        $this->stubsToVerify[] = $service;

        $logger = Stub::makeEmpty(LoggerInterface::class, [
            'error' => Stub\Expected::once()
        ]);
        $this->stubsToVerify[] = $logger;

        $subscriber = new WinningAnnounceSubscriber(
            $service,
            $logger
        );

        $subscriber->onPostPayOut($event);
    }
}
