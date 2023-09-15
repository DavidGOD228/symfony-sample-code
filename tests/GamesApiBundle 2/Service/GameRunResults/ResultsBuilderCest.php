<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\GameRunResults;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\AbCard;
use Acme\SymfonyDb\Entity\AbRunRoundCard;
use Acme\SymfonyDb\Entity\BaccaratCard;
use Acme\SymfonyDb\Entity\BaccaratRunCard;
use Acme\SymfonyDb\Entity\BazaarRun;
use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameItem;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\GameRunResult;
use Acme\SymfonyDb\Entity\GameRunResultItem;
use Acme\SymfonyDb\Entity\HeadsUpCard;
use Acme\SymfonyDb\Entity\HeadsUpRunCard;
use Acme\SymfonyDb\Entity\MatkaCard;
use Acme\SymfonyDb\Entity\MatkaRunCard;
use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyDb\Entity\PokerCard;
use Acme\SymfonyDb\Entity\PokerRunCard;
use Acme\SymfonyDb\Entity\RpsRunRoundCard;
use Acme\SymfonyDb\Entity\Speedy7Card;
use Acme\SymfonyDb\Entity\Speedy7RunRound;
use Acme\SymfonyDb\Entity\StsPokerRunCard;
use Acme\SymfonyDb\Entity\WarCard;
use Acme\SymfonyDb\Entity\WarRunCard;
use Acme\SymfonyDb\Type\DealedToType;
use DateTimeImmutable;
use CoreBundle\Service\CacheServiceInterface;
use Eastwest\Json\Json;
use GamesApiBundle\Service\GameRunResults\ResultsBuilder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use SymfonyTests\_support\Doctrine\EntityHelper;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\GamesApiBundle\Fixture\GameRunResults\OddFixture;
use SymfonyTests\UnitTester;

/**
 * Class ResultsFormatterCest
 */
final class ResultsBuilderCest extends AbstractUnitTest
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
    ];

    protected array $fixtures = [
        OddFixture::class,
    ];

    private ResultsBuilder $resultsBuilder;

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $this->resultsBuilder = $I->getContainer()->get(ResultsBuilder::class);
    }

    /**
     * {@inheritDoc}
     */
    protected function setUpFixtures(): void
    {
        parent::setUpFixtures();
        $this->fixtureBoostrapper->addGames([GameDefinition::BACCARAT, GameDefinition::POKER]);
    }

    /**
     * @param UnitTester $I
     */
    public function testWar(UnitTester $I): void
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

        $results = $this->resultsBuilder->build([$gameRun], 'Acme.local');
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.war.json',
            Json::encode($results[0])
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testHeadsUp(UnitTester $I): void
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

        foreach ($cards as $dealtTo => $cardIds) {
            foreach ($cardIds as $cardId) {
                $card = (new HeadsUpCard())->setSuit('d')->setValue('10');
                $runCard = (new HeadsUpRunCard())->setDealtTo($dealtTo)->setHeadsUpCard($card);
                $gameRun->addHeadsUpRunCard($runCard);

                EntityHelper::setId($card, $cardId);
            }
        }

        $results = $this->resultsBuilder->build([$gameRun], 'Acme.local');
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.headsup.json',
            Json::encode($results[0])
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testAndarBahar(UnitTester $I): void
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
            ->setTime(new DateTimeImmutable('2023-01-01 00:00:00'))
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

        $results = $this->resultsBuilder->build([$gameRun], 'Acme.local');
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.ab.json',
            Json::encode($results[0])
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testMatka(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::MATKA);
        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2023-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game)
            ->setBazaarRun(null)
            ->addMatkaRunCard((new MatkaRunCard())->setCard((new MatkaCard())->setValue('5')->setSuit('c')))
            ->addMatkaRunCard((new MatkaRunCard())->setCard((new MatkaCard())->setValue('5')->setSuit('c')))
            ->addMatkaRunCard((new MatkaRunCard())->setCard((new MatkaCard())->setValue('3')->setSuit('h')));

        $results = $this->resultsBuilder->build([$gameRun], 'Acme.local');
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.matka.json',
            Json::encode($results[0])
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testMatkaWithBazaar(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::MATKA);

        $openingRun = GameRun::createFromId(1)
            ->setCode('100')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2023-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game)
            ->addMatkaRunCard((new MatkaRunCard())->setCard((new MatkaCard())->setValue('5')->setSuit('c')))
            ->addMatkaRunCard((new MatkaRunCard())->setCard((new MatkaCard())->setValue('5')->setSuit('c')))
            ->addMatkaRunCard((new MatkaRunCard())->setCard((new MatkaCard())->setValue('3')->setSuit('h')));

        $closingRun = GameRun::createFromId(2)
            ->setCode('101')
            ->setIsReturned(false)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2023-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game)
            ->addMatkaRunCard((new MatkaRunCard())->setCard((new MatkaCard())->setValue('5')->setSuit('c')))
            ->addMatkaRunCard((new MatkaRunCard())->setCard((new MatkaCard())->setValue('5')->setSuit('c')))
            ->addMatkaRunCard((new MatkaRunCard())->setCard((new MatkaCard())->setValue('3')->setSuit('h')));

        $bazaarRun = (new BazaarRun())
            ->setTitle('Milan Morning')
            ->setCode('100:101')
            ->setIsReturned(false)
        ;

        $bazaarRun->setClosingRun($closingRun);
        $bazaarRun->setOpeningRun($openingRun);

        $openingRun->setBazaarRun($bazaarRun);
        $closingRun->setBazaarRun($bazaarRun);

        $results = $this->resultsBuilder->build([$openingRun], 'Acme.local');
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.matka-with-opening-bazaar.json',
            Json::encode($results[0])
        );

        $results = $this->resultsBuilder->build([$closingRun], 'Acme.local');
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.matka-with-closing-bazaar.json',
            Json::encode($results[0])
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testPoker(UnitTester $I): void
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
                    $this->getPokerCard($cardId)->setSuit($suit)->setValue($value)
                );
            $gameRun->addPokerRunCard($runCard);
        }

        $results = $this->resultsBuilder->build([$gameRun], 'Acme.local');
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.poker.json',
            Json::encode($results[0])
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testBaccarat(UnitTester $I): void
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
            (new BaccaratRunCard())->setDealtTo(DealedToType::DEALER)->setCard(
                (new BaccaratCard())->setSuit('h')->setValue('3')->setScore(3)
            ),
            (new BaccaratRunCard())->setDealtTo(DealedToType::DEALER)->setCard(
                (new BaccaratCard())->setSuit('d')->setValue('j')->setScore(0)
            ),
            (new BaccaratRunCard())->setDealtTo(DealedToType::DEALER)->setCard(
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

        $results = $this->resultsBuilder->build([$gameRun], 'Acme.local');
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.baccarat.json',
            Json::encode($results[0])
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testStsPoker(UnitTester $I): void
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

        $results = $this->resultsBuilder->build([$gameRun], 'Acme.local');
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.sts-poker.json',
            Json::encode($results[0])
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testSpeedy7(UnitTester $I): void
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

        $results = $this->resultsBuilder->build([$gameRun], 'Acme.local');
        $I->assertStringEqualsFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.speedy7.json',
            Json::encode($results[0], JSON_PRETTY_PRINT)
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testSpeedy7WithNotAllCardsShouldProvideResults(UnitTester $I): void
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
                (new Speedy7RunRound())->setRoundNumber(3)
            );

        $results = $this->resultsBuilder->build([$gameRun], 'Acme.local');
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.speedy7-partial.json',
            Json::encode($results[0])
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testRps(UnitTester $I): void
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
                (new RpsRunRoundCard())->setDealtTo('zone1')->setCard('rock'),
            )->addRpsRunRoundCard(
                (new RpsRunRoundCard())->setDealtTo('zone2')->setCard('paper'),
            );

        $results = $this->resultsBuilder->build([$gameRun], 'Acme.local');
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.rps.json',
            Json::encode($results[0])
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testReturnedRunShouldNotProvideDetails(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::SPEEDY7);

        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(true)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game);

        $results = $this->resultsBuilder->build([$gameRun], 'Acme.local');
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.returned.json',
            Json::encode($results[0])
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testVideoDisplayProhibitedNotShowingVideo(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::SPEEDY7);

        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(true)
            ->setResultsEntered(false)
            ->setTime(new DateTimeImmutable('2020-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setIsImported(false)
            ->setVideoConfirmationRequired(true)
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game);

        $results = $this->resultsBuilder->build([$gameRun], 'Acme.local');
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.no-video.json',
            Json::encode($results[0])
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testNotExistingVideoShouldNotBeProvided(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::SPEEDY7);

        $gameRun = (new GameRun())
            ->setCode('100')
            ->setIsReturned(true)
            ->setResultsEntered(false)
            ->setIsImported(false)
            ->setVideoConfirmationRequired(false)
            ->setVideoUrl(null)
            ->setTime(new DateTimeImmutable('2020-01-01 00:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setGame($game)
        ;

        $results = $this->resultsBuilder->build([$gameRun], 'Acme.local');
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.no-video.json',
            Json::encode($results[0])
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testShouldReturnNoCardsForUnknownGame(UnitTester $I): void
    {
        $game = Game::createFromId(-1);

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

        $results = $this->resultsBuilder->build([$gameRun], 'Acme.local');

        $I->assertCount(1, $results);
        $I->assertJsonStringEqualsJsonFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.unknown-game.json',
            Json::encode($results[0])
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function testCaching(UnitTester $I): void
    {
        $game = Game::createFromId(GameDefinition::LUCKY_7);
        $gameRunResult = (new GameRunResult())
            ->addGameRunResultItem(
                (new GameRunResultItem())->setGameItem(
                    (new GameItem())->setNumber(7)->setColor('red')->setGame($game)
                )
            )
            ->addGameRunResultItem(
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
            ->setVideoUrl('/blah/video.mp4')
            ->setGame($game)
            ->addGameRunResult($gameRunResult);

        $results = $this->resultsBuilder->build([$gameRun], 'Acme.local');

        /** @var SerializerInterface $serializer */
        $serializer = $I->getContainer()->get(SerializerInterface::class);
        $I->assertStringEqualsFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.lottery.json',
            $serializer->serialize($results, JsonEncoder::FORMAT, ['json_encode_options' => JSON_PRETTY_PRINT])
        );

        /** @var CacheServiceInterface $cacheService */
        $cacheService = $I->getContainer()->get(CacheServiceInterface::class);
        $cache = $cacheService->get('game-run-results:v2:100');
        $I->assertStringEqualsFile(
            __DIR__ . '/../../Fixture/GameRunResults/result.cached',
            $cache
        );

        $gameRun->removeGameRunResult($gameRunResult);
        $resultsFromCache = $this->resultsBuilder->build([$gameRun], 'Acme.local');
        // GameRun was modified, but in cache it have same results.
        $I->assertEquals($resultsFromCache, $results);
    }

    /**
     * @param int $id
     *
     * @return PokerRunCard
     */
    private function getPokerRunCard(int $id): PokerRunCard
    {
        return EntityHelper::getEntityWithId(PokerRunCard::class, $id);
    }

    /**
     * @param int $id
     *
     * @return PokerCard
     */
    private function getPokerCard(int $id): PokerCard
    {
        return EntityHelper::getEntityWithId(PokerCard::class, $id);
    }

    /**
     * @param int $id
     *
     * @return StsPokerRunCard
     */
    private function getStsPokerRunCard(int $id): StsPokerRunCard
    {
        return EntityHelper::getEntityWithId(StsPokerRunCard::class, $id);
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
