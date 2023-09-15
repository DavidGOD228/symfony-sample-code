<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\Gamification;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\AbCard;
use Acme\SymfonyDb\Entity\AbRunRoundCard;
use Acme\SymfonyDb\Entity\BaccaratCard;
use Acme\SymfonyDb\Entity\BaccaratRunCard;
use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\BetItem;
use Acme\SymfonyDb\Entity\Combination;
use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameItem;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\GameRunResult;
use Acme\SymfonyDb\Entity\GameRunResultItem;
use Acme\SymfonyDb\Entity\HeadsUpCard;
use Acme\SymfonyDb\Entity\HeadsUpRunCard;
use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Entity\PokerCard;
use Acme\SymfonyDb\Entity\PokerRunCard;
use Acme\SymfonyDb\Entity\RpsBet;
use Acme\SymfonyDb\Entity\RpsRunRoundCard;
use Acme\SymfonyDb\Entity\Speedy7Card;
use Acme\SymfonyDb\Entity\Speedy7RunRound;
use Acme\SymfonyDb\Entity\StsPokerRunCard;
use Acme\SymfonyDb\Entity\WarCard;
use Acme\SymfonyDb\Entity\WarRunCard;
use Acme\SymfonyDb\Type\BetStatusType;
use Acme\SymfonyDb\Type\DealedToType;
use DateTimeImmutable;
use CodeigniterSymfonyBridge\PayData;
use CoreBundle\Exception\ValidationException;
use Doctrine\ORM\Tools\ToolsException;
use Exception;
use GamesApiBundle\Service\Gamification\PayOutRequestBuilder;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use SymfonyTests\_support\Doctrine\EntityHelper;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\GamesApiBundle\Fixture\GameRunResults\OddFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\DataProvider;
use SymfonyTests\UnitTester;

/**
 * Class PayOutRequestBuilderCest
 */
final class PayOutRequestBuilderCest extends AbstractUnitTest
{
    private const POKER_PLAYERS_COUNT = 6;
    private const POKER_CARDS_PER_PLAYER = 2;

    private const POKER_CARD_VALUES = [
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        8 => 8,
        9 => 9,
        10 => 10,
        11 => 'j',
        12 => 'q',
        13 => 'k',
        14 => 'a'
    ];

    protected array $tables = [
        Odd::class,
        RpsBet::class,
        BetItem::class,
        GameRunResultItem::class,
    ];

    protected array $fixtures = [
        OddFixture::class,
    ];

    private PayOutRequestBuilder $requestBuilder;

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $container = $I->getContainer();

        /** @var PayOutRequestBuilder $requestBuilder */
        $requestBuilder = $container->get(PayOutRequestBuilder::class);
        $this->requestBuilder = $requestBuilder;

        $request = Request::create('', 'GET', [], [], [], ['HTTP_HOST' => 'Acme.local']);
        $I->getRequestStack()->push($request);
    }

    /**
     * {@inheritDoc}
     */
    protected function setUpFixtures(): void
    {
        parent::setUpFixtures();

        $gameIds = [
            GameDefinition::LUCKY_7,
            GameDefinition::LUCKY_5,
            GameDefinition::POKER,
            GameDefinition::BACCARAT,
            GameDefinition::WAR,
            GameDefinition::SPEEDY7,
            GameDefinition::HEADSUP,
            GameDefinition::ANDAR_BAHAR,
            GameDefinition::STS_POKER,
            GameDefinition::RPS,
        ];

        $this->fixtureBoostrapper->addCurrencies(['eur']);
        $this->fixtureBoostrapper->addRunRounds($gameIds);
        $this->fixtureBoostrapper->addBets($gameIds);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testLotteryResults(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::LUCKY_7);
        $gameRunResult = (new GameRunResult());
        $gameRunResult->addGameRunResultItem(
            (new GameRunResultItem())->setGameItem(
                (new GameItem())->setNumber(7)->setColor('red')->setGame($game)
            )
        );
        $gameRunResult->addGameRunResultItem(
            (new GameRunResultItem())->setGameItem(
                (new GameItem())->setNumber(22)->setColor('blue')->setGame($game)
            )
        );
        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setGame($game)
            ->addGameRunResult($gameRunResult);

        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:1:1");
        $bet->setRunRoundId(1);
        $bet->setGameRun($gameRun);
        $bet->setOdd((new Odd())->setGame($game)->setClass('c'));

        $request = $this->requestBuilder->buildSingle($bet, PayData::TYPE_SINGLE);
        $expectedResults = [
            '1-ball_7_red',
            '1-ball_22_blue',
        ];

        $I->assertEquals($expectedResults, $request->getResults());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testAbResults(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::ANDAR_BAHAR);
        $runCards = [
            (new AbRunRoundCard())->setNumber(1)->setDealtTo('joker')->setCard(
                (new AbCard())->setSuit('d')->setValue('10')
            ),
            (new AbRunRoundCard())->setNumber(2)->setDealtTo('bahar')->setCard(
                (new AbCard())->setSuit('d')->setValue('10')
            ),
            (new AbRunRoundCard())->setNumber(3)->setDealtTo('andar')->setCard(
                (new AbCard())->setSuit('d')->setValue('10')
            ),
        ];

        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game)
        ;
        foreach ($runCards as $index => $runCard) {
            EntityHelper::setId($runCard, $index);
            EntityHelper::setId($runCard->getCard(), $index);
            $gameRun->addAndarBaharRunCard($runCard);
        }

        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:1:1");
        $bet->setRunRoundId(1);
        $bet->setOdd((new Odd())->setGame($game)->setClass('c'));
        $bet->setGameRun($gameRun);

        $request = $this->requestBuilder->buildSingle($bet, PayData::TYPE_SINGLE);
        $expectedResults = [
            '13-andar_Td',
            '13-bahar_Td',
            '13-joker_Td',
            '13-winner_andar',
        ];

        $I->assertEquals($expectedResults, $request->getResults());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testAbWithoutCardsForAndar(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::ANDAR_BAHAR);
        $runCards = [
            (new AbRunRoundCard())->setNumber(1)->setDealtTo('joker')->setCard(
                (new AbCard())->setSuit('d')->setValue('10')
            ),
            (new AbRunRoundCard())->setNumber(2)->setDealtTo('bahar')->setCard(
                (new AbCard())->setSuit('d')->setValue('10')
            ),
        ];

        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game)
        ;
        foreach ($runCards as $index => $runCard) {
            EntityHelper::setId($runCard, $index);
            EntityHelper::setId($runCard->getCard(), $index);
            $gameRun->addAndarBaharRunCard($runCard);
        }

        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:1:1");
        $bet->setRunRoundId(1);
        $bet->setOdd((new Odd())->setGame($game)->setClass('c'));
        $bet->setGameRun($gameRun);

        $request = $this->requestBuilder->buildSingle($bet, PayData::TYPE_SINGLE);
        $expectedResults = [
            '13-bahar_Td',
            '13-joker_Td',
            '13-winner_bahar',
        ];

        $I->assertEquals($expectedResults, $request->getResults());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testAbWithoutCardsForBahar(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::ANDAR_BAHAR);
        $runCards = [
            (new AbRunRoundCard())->setNumber(1)->setDealtTo('joker')->setCard(
                (new AbCard())->setSuit('d')->setValue('10')
            ),
            (new AbRunRoundCard())->setNumber(3)->setDealtTo('andar')->setCard(
                (new AbCard())->setSuit('d')->setValue('10')
            ),
        ];

        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game)
        ;
        foreach ($runCards as $index => $runCard) {
            EntityHelper::setId($runCard, $index);
            EntityHelper::setId($runCard->getCard(), $index);
            $gameRun->addAndarBaharRunCard($runCard);
        }

        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:1:1");
        $bet->setRunRoundId(1);
        $bet->setOdd((new Odd())->setGame($game)->setClass('c'));
        $bet->setGameRun($gameRun);

        $request = $this->requestBuilder->buildSingle($bet, PayData::TYPE_SINGLE);
        $expectedResults = [
            '13-andar_Td',
            '13-joker_Td',
            '13-winner_andar',
        ];

        $I->assertEquals($expectedResults, $request->getResults());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testBaccaratResults(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::BACCARAT);

        $runCards = [
            (new BaccaratRunCard())->setDealtTo(DealedToType::PLAYER)->setCard(
                (new BaccaratCard())->setSuit('h')->setValue('j')->setScore(0)
            ),
            (new BaccaratRunCard())->setDealtTo(DealedToType::PLAYER)->setCard(
                (new BaccaratCard())->setSuit('s')->setValue('j')->setScore(0)
            ),
            (new BaccaratRunCard())->setDealtTo(DealedToType::PLAYER)->setCard(
                (new BaccaratCard())->setSuit('d')->setValue('3')->setScore(3)
            ),
            (new BaccaratRunCard())->setDealtTo('banker')->setCard(
                (new BaccaratCard())->setSuit('h')->setValue('3')->setScore(3)
            ),
            (new BaccaratRunCard())->setDealtTo('banker')->setCard(
                (new BaccaratCard())->setSuit('d')->setValue('j')->setScore(0)
            ),
            (new BaccaratRunCard())->setDealtTo('banker')->setCard(
                (new BaccaratCard())->setSuit('h')->setValue('3')->setScore(3)
            ),
        ];

        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game)
        ;
        foreach ($runCards as $index => $runCard) {
            EntityHelper::setId($runCard, $index);
            EntityHelper::setId($runCard->getCard(), $index);
            $gameRun->addBaccaratRunCard($runCard);
        }

        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:1:1");
        $bet->setRunRoundId(1);
        $bet->setOdd((new Odd())->setGame($game)->setClass('c'));
        $bet->setGameRun($gameRun);

        $request = $this->requestBuilder->buildSingle($bet, PayData::TYPE_SINGLE);
        $expectedResults = [
            '6-player_Jh',
            '6-player_Js',
            '6-player_3d',
            '6-banker_3h',
            '6-banker_Jd',
            '6-banker_3h',
            '6-winner_banker',
            '6-wonSideOdds_PAIR_PLAYER',
            '6-wonSideOdds_PAIR_ANY',
            '6-wonSideOdds_HAND_BIG',
        ];

        $I->assertEquals($expectedResults, $request->getResults());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testHeadsUpResults(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::HEADSUP);

        $cards = [
            DealedToType::PLAYER => [109, 113],
            DealedToType::DEALER => [114, 207],
            DealedToType::BOARD => [213, 209, 314, 409, 407]
        ];

        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game)
        ;

        $runCards = [];
        foreach ($cards as $dealtTo => $cardIds) {
            foreach ($cardIds as $cardId) {
                $card = (new HeadsUpCard())->setSuit('d')->setValue('10');
                $runCard = (new HeadsUpRunCard())->setDealtTo($dealtTo)->setHeadsUpCard($card);
                $gameRun->addHeadsUpRunCard($runCard);

                EntityHelper::setId($card, $cardId);
            }
        }

        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:1:1");
        $bet->setRunRoundId(1);
        $bet->setOdd((new Odd())->setGame($game)->setClass('c'));
        $bet->setGameRun($gameRun);

        $request = $this->requestBuilder->buildSingle($bet, PayData::TYPE_SINGLE);
        $expectedResults = [
            '12-player_Td',
            '12-player_Td',
            '12-dealer_Td',
            '12-dealer_Td',
            '12-board_Td',
            '12-board_Td',
            '12-board_Td',
            '12-board_Td',
            '12-board_Td',
            '12-winner_player',
            '12-wonCombination_FULL_HOUSE',
        ];

        $I->assertEquals($expectedResults, $request->getResults());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testPokerResults(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::POKER);

        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game)
        ;

        $cardIds = [213, 104, 305, 106, 407, 108, 412, 110, 111, 112, 113, 202, 203, 204, 303, 206, 207];
        foreach ($cardIds as $index => $cardId) {
            $suit = $index % 2 ? 'spades' : 'hearts';
            $value = self::POKER_CARD_VALUES[$cardId % 100];

            $dealtTo = $this->getPokerDealtTo($index);
            $runCard = $this->getPokerRunCard($index)
                ->setIsConfirmed(true)
                ->setDealtTo($dealtTo)
                ->setPokerCard(
                    $this->getPokerCard($cardId)
                        ->setSuit($suit)
                        ->setValue($value)
                );
            $gameRun->addPokerRunCard($runCard);
        }

        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:1:1");
        $bet->setRunRoundId(1);
        $bet->setOdd((new Odd())->setGame($game)->setClass('c'));
        $bet->setGameRun($gameRun);

        $request = $this->requestBuilder->buildSingle($bet, PayData::TYPE_SINGLE);
        $expectedResults = [
            '5-hand1_Kh',
            '5-hand1_Qh',
            '5-hand2_4s',
            '5-hand2_Ts',
            '5-hand3_5h',
            '5-hand3_Jh',
            '5-hand4_6s',
            '5-hand4_Qs',
            '5-hand5_7h',
            '5-hand5_Kh',
            '5-hand6_8s',
            '5-hand6_2s',
            '5-table_3h',
            '5-table_4s',
            '5-table_3h',
            '5-table_6s',
            '5-table_7h',
            '5-wonHands_1',
            '5-wonCombination_FLUSH',
        ];

        $I->assertEquals($expectedResults, $request->getResults());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testPokerStsResults(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::STS_POKER);

        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game)
        ;

        $cardIds = [213, 104, 305, 106, 407, 108, 412, 110, 111, 112, 113, 202, 203, 204, 303, 206, 207];
        foreach ($cardIds as $index => $cardId) {
            $suit = $index % 2 ? 'spades' : 'hearts';
            $value = self::POKER_CARD_VALUES[$cardId % 100];
            $dealtTo = $this->getPokerDealtTo($index);
            $runCard = $this->getStsPokerRunCard($index)
                            ->setIsConfirmed(true)
                            ->setDealtTo($dealtTo)
                            ->setCard(
                                $this->getPokerCard($cardId)->setSuit($suit)->setValue($value)
                            );
            $gameRun->addStsPokerRunCard($runCard);
        }

        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:1:1");
        $bet->setRunRoundId(1);
        $bet->setOdd((new Odd())->setGame($game)->setClass('c'));
        $bet->setGameRun($gameRun);

        $request = $this->requestBuilder->buildSingle($bet, PayData::TYPE_SINGLE);
        $expectedResults = [
            '14-hand1_Kh',
            '14-hand1_Qh',
            '14-hand2_4s',
            '14-hand2_Ts',
            '14-hand3_5h',
            '14-hand3_Jh',
            '14-hand4_6s',
            '14-hand4_Qs',
            '14-hand5_7h',
            '14-hand5_Kh',
            '14-hand6_8s',
            '14-hand6_2s',
            '14-table_3h',
            '14-table_4s',
            '14-table_3h',
            '14-table_6s',
            '14-table_7h',
            '14-wonHands_1',
            '14-wonCombination_FLUSH',
        ];

        $I->assertEquals($expectedResults, $request->getResults());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testRpsResults(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::RPS);

        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game)
            ->addRpsRunRoundCard(
                (new RpsRunRoundCard())->setDealtTo('zone1')->setCard('rock')
            )->addRpsRunRoundCard(
                (new RpsRunRoundCard())->setDealtTo('zone2')->setCard('paper'),
            );

        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:1:1");
        $bet
            ->setRunRoundId(1)
            ->setOdd(
                (new Odd())->setGame($game)->setClass('c')
            )
            ->setGameRun($gameRun)
            ->setRpsBet(
                (new RpsBet())->setPushValue(1.3)
            )
        ;

        $request = $this->requestBuilder->buildSingle($bet, PayData::TYPE_SINGLE);
        $expectedResults = [
            '15-zone1_R',
            '15-zone2_P',
        ];

        $I->assertEquals($expectedResults, $request->getResults());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testSpeedy7Results(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::SPEEDY7);

        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game)
            ->addSpeedy7RunRound(
                (new Speedy7RunRound())->setRoundNumber(1)->setCard(
                    (new Speedy7Card())->setValue('7')->setSuit('spades')
                )
            )->addSpeedy7RunRound(
                (new Speedy7RunRound())->setRoundNumber(2)->setCard(
                    (new Speedy7Card())->setValue('A')->setSuit('hearts')
                )
            )->addSpeedy7RunRound(
                (new Speedy7RunRound())->setRoundNumber(3)->setCard(
                    (new Speedy7Card())->setValue('3')->setSuit('diamonds')
                )
            );

        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:1:1");
        $bet->setRunRoundId(1);
        $bet->setOdd((new Odd())->setGame($game)->setClass('c'));
        $bet->setGameRun($gameRun);

        $request = $this->requestBuilder->buildSingle($bet, PayData::TYPE_SINGLE);
        $expectedResults = [];

        $I->assertEquals($expectedResults, $request->getResults());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testWarResults(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::WAR);

        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game)
        ;

        $runCards = [
            (new WarRunCard())->setDealtTo(DealedToType::DEALER)->setCard(
                (new WarCard())->setSuit('d')->setValue('10')
            ),
            (new WarRunCard())->setDealtTo(DealedToType::PLAYER)->setCard(
                (new WarCard())->setSuit('d')->setValue('10')
            ),
        ];
        foreach ($runCards as $index => $runCard) {
            EntityHelper::setId($runCard, $index);
            EntityHelper::setId($runCard->getCard(), $index);
            $gameRun->addWarRunCard($runCard);
        }

        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:1:1");
        $bet->setRunRoundId(1);
        $bet->setOdd((new Odd())->setGame($game)->setClass('c'));
        $bet->setGameRun($gameRun);

        $request = $this->requestBuilder->buildSingle($bet, PayData::TYPE_SINGLE);
        $expectedResults = [
            '8-dealer_Td',
            '8-player_Td',
            '8-winner_war',
        ];

        $I->assertEquals($expectedResults, $request->getResults());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testUnknownGameResults(UnitTester $I): void
    {
        $game = Game::createFromId(421412);
        $odd = (new Odd())->setGame($game)->setClass('c');
        $currency = Currency::createFromId(1)->setCode('eur');
        $partner = (new Partner())->setApiCode('some');
        $player = (new Player())
            ->setPartner($partner)
            ->setTag('existing')
            ->setTaggedAt(new DateTimeImmutable())
        ;
        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game);
        $bet = (new Bet())
            ->setOddValue(1.23)
            ->setAmountWon(2.46)
            ->setCurrency($currency)
            ->setOdd($odd)
            ->setGameRun($gameRun)
            ->setPlayer($player)
            ->setStatus(BetStatusType::ACTIVE)
            ->setTime(new DateTimeImmutable())
        ;

        $I->expectThrowable(
            new InvalidArgumentException('UNSUPPORTED_GAME:421412'),
            function () use ($bet): void {
                $this->requestBuilder->buildSingle($bet, PayData::TYPE_SINGLE);
            }
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testPartnerPayOutWithoutApiCode(UnitTester $I): void
    {
        $bet = (new DataProvider(GameDefinition::LUCKY_5))
            ->getNewBet();
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
    public function testBuildCombination(UnitTester $I): void
    {
        $gameLucky7 = Game::createFromId(GameDefinition::LUCKY_7);
        $gameRunLucky7 = (new GameRun())
            ->setCode('3DrawCode')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setGame($gameLucky7)
            ->addGameRunResult(
                (new GameRunResult())
                    ->addGameRunResultItem(
                        (new GameRunResultItem())->setGameItem(
                            (new GameItem())->setNumber(7)->setColor('red')->setGame($gameLucky7)
                        )
                    )
                    ->addGameRunResultItem(
                        (new GameRunResultItem())->setGameItem(
                            (new GameItem())->setNumber(22)->setColor('blue')->setGame($gameLucky7)
                        )
                    )
            );

        $gameLucky5 = Game::createFromId(GameDefinition::LUCKY_5);
        $gameRunLucky5 = (new GameRun())
            ->setCode('1DrawCode')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setGame($gameLucky5)
            ->addGameRunResult(
                (new GameRunResult())->addGameRunResultItem(
                    (new GameRunResultItem())->setGameItem(
                        (new GameItem())->setNumber(5)->setColor('red')->setGame($gameLucky5)
                    ),
                )->addGameRunResultItem(
                    (new GameRunResultItem())->setGameItem(
                        (new GameItem())->setNumber(23)->setColor('blue')->setGame($gameLucky5)
                    ),
                )
            );

        $bet1 = $this->getEntityByReference("bet:3:1");
        $bet1->setGameRun($gameRunLucky7);
        $bet2 = $this->getEntityByReference("bet:1:1");
        $bet2->setGameRun($gameRunLucky5);

        $betAmount = $bet1->getAmount() + $bet2->getAmount();
        $amountWon = $bet1->getAmountWon() + $bet2->getAmountWon();
        $oddValue = round($bet1->getOddValue() * $bet2->getOddValue(), 2);

        $combination = (new Combination())
            ->setPlayer($bet1->getPlayer())
            ->setAmountWon($amountWon)
            ->setAmount($betAmount)
            ->setCurrency($bet1->getCurrency())
            ->setDateCreated($bet1->getCreatedAt())
            ->setOddValue($oddValue)
        ;

        $combination->addBet($bet1);
        $combination->addBet($bet2);

        $request = $this->requestBuilder->buildCombination($combination, PayData::TYPE_COMBO);

        $I->assertEquals('test1', $request->getPartnerCode());
        $I->assertEquals(PayData::TYPE_COMBO, $request->getBetType());
        $I->assertEquals(906, $request->getAmountWon());
        $I->assertEquals(906, $request->getAmountWonEur());
        $I->assertEquals(1.21, $request->getOddValue());
        $I->assertNull($request->getTieOddValue());
        $I->assertEquals('eur', $request->getCurrencyCode());
        $I->assertIsString($request->getBetTime());
        $I->assertEquals(null, $request->getRoundNumber());
        $I->assertEquals([3, 1], $request->getGameIds());
        $I->assertEquals(['3DrawCode', '1DrawCode'], $request->getRunCodes());
        $I->assertEquals(['3-BALLX1_YES', '1-BALLX1_YES'], $request->getOddClasses());
        $I->assertEquals(null, $request->getTieOddValue());
        $I->assertEquals('won', $request->getBetStatus());

        $expectedResults = [
            '1-ball_7_red',
            '1-ball_22_blue',
            '3-ball_5_red',
            '3-ball_23_blue',
        ];

        $I->assertEquals($expectedResults, $request->getResults());
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testReturnedGameRunsResults(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::WAR);

        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(true)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game)
        ;

        $runCards = [
            (new WarRunCard())->setDealtTo(DealedToType::DEALER)->setCard(
                (new WarCard())->setSuit('d')->setValue('10')
            ),
            (new WarRunCard())->setDealtTo(DealedToType::PLAYER)->setCard(
                (new WarCard())->setSuit('d')->setValue('10')
            ),
        ];
        foreach ($runCards as $index => $runCard) {
            EntityHelper::setId($runCard, $index);
            EntityHelper::setId($runCard->getCard(), $index);
            $gameRun->addWarRunCard($runCard);
        }

        /** @var Bet $bet */
        $bet = $this->getEntityByReference("bet:1:1");
        $bet->setRunRoundId(1);
        $bet->setOdd((new Odd())->setGame($game)->setClass('c'));
        $bet->setGameRun($gameRun);

        $request = $this->requestBuilder->buildSingle($bet, PayData::TYPE_SINGLE);
        $expectedResults = ['8-cancel'];

        $I->assertEquals($expectedResults, $request->getResults());
    }

    /**
     * @param int $id
     *
     * @return PokerRunCard
     */
    private function getPokerRunCard(int $id): PokerRunCard
    {
        /** @var PokerRunCard $runCard */
        $runCard = EntityHelper::getEntityWithId(PokerRunCard::class, $id);

        return $runCard;
    }

    /**
     * @param int $id
     *
     * @return PokerCard
     */
    private function getPokerCard(int $id): PokerCard
    {
        /** @var PokerCard $runCard */
        $runCard = EntityHelper::getEntityWithId(PokerCard::class, $id);

        return $runCard;
    }

    /**
     * @param int $id
     *
     * @return StsPokerRunCard
     */
    private function getStsPokerRunCard(int $id): StsPokerRunCard
    {
        /** @var StsPokerRunCard $runCard */
        $runCard = EntityHelper::getEntityWithId(StsPokerRunCard::class, $id);

        return $runCard;
    }

    /**
     * @param int $cardIndex
     *
     * @return string
     */
    private function getPokerDealtTo(int $cardIndex) : string
    {
        if ($cardIndex >= self::POKER_PLAYERS_COUNT * self::POKER_CARDS_PER_PLAYER) {
            return DealedToType::BOARD;
        }

        return (string) ($cardIndex % self::POKER_PLAYERS_COUNT + 1);
    }
}
