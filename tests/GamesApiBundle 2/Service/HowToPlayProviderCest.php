<?php

namespace SymfonyTests\Unit\GamesApiBundle\Service;

use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\HowToPlayBlock;
use Acme\SymfonyDb\Entity\HowToPlayBlockContent;
use Acme\SymfonyDb\Entity\HowToPlayPreset;
use Acme\SymfonyDb\Entity\HowToPlayPresetBlock;
use Acme\SymfonyDb\Entity\Language;
use Acme\SymfonyDb\Entity\OddPreset;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\PartnerEnabledGame;
use Acme\SymfonyDb\Entity\Promotion;
use Acme\SymfonyDb\Entity\PromotionEnabledFor;
use Codeception\Stub;
use CoreBundle\Exception\CoreException;
use CoreBundle\Service\RepositoryProviderInterface;
use CoreBundle\Service\SerializerService;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\ToolsException;
use GamesApiBundle\DataObject\HowToPlay\HowToPlayBlockDTO;
use GamesApiBundle\Service\HowToPlayProvider;
use SymfonyTests\_support\CoreBundleMock\CacheServiceMock;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\GamesApiBundle\Fixture\HowToPlay\HowToPlayBlockContentFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\HowToPlay\HowToPlayBlockFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\HowToPlay\HowToPlayPresetFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\HowToPlay\PartnerEnabledGameFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\HowToPlay\PartnerFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\HowToPlay\PromotionEnabledForFixture;
use SymfonyTests\UnitTester;

/**
 * Class HowToPlayProviderCest
 */
class HowToPlayProviderCest extends AbstractUnitTest
{
    protected array $tables = [
        Partner::class,
        PartnerEnabledGame::class,
        Language::class,
        Game::class,
        OddPreset::class,
        HowToPlayPreset::class,
        HowToPlayBlock::class,
        HowToPlayBlockContent::class,
        HowToPlayPresetBlock::class,
        PromotionEnabledFor::class,
        GameRun::class,
        Promotion::class,
    ];

    protected array $fixtures = [
        PartnerFixture::class,
        PromotionEnabledForFixture::class,
        HowToPlayBlockFixture::class,
        HowToPlayBlockContentFixture::class,
        HowToPlayPresetFixture::class,
        PartnerEnabledGameFixture::class,
    ];

    private HowToPlayProvider $provider;
    private SerializerService $serializer;
    private Language $defaultLanguage;

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);
        $this->provider = new HowToPlayProvider(
            $this->getRepositoryProvider(),
            new CacheServiceMock()
        );
        $this->serializer = new SerializerService();

        /** @var Language $defaultLanguage */
        $defaultLanguage = $this->getEntityByReference('language:en');
        $this->defaultLanguage = $defaultLanguage;
    }

    /**
     * @inheritDoc
     */
    protected function setUpFixtures(): void
    {
        parent::setUpFixtures();

        $this->fixtureBoostrapper->addLanguages(
            ['en', 'lt', 'ru', 'ee', 'pl', 'lang_without_video', 'lang_without_video2'],
            [
                'ru' => ['fallback' => 'language:en'],
                'lang_without_video' => ['fallback' => 'language:en'],
                'lang_without_video2' => ['fallback' => 'language:lang_without_video'],
            ]
        );
        $this->fixtureBoostrapper->addGames([1, 2, 11, 12, 13]);
        $this->fixtureBoostrapper->addCurrencies(['eur']);
    }

    /**
     * Case 1:
     * Partner with no parent selected, has default preset assigned in English
     *
     * @param UnitTester $I
     */
    public function testNoParentWithSelectedDefaultPreset(UnitTester $I): void
    {
        $response = $this->getBlocks('en', 'partner-with-no-parent-and-default-preset');

        $expectedResponse =
            '[{"title":"General","content":"Some general info about the game in English","isExpandable":true}'
            . ',{"title":"Rules","content":"Game rules list in English","isExpandable":true}'
            . ',{"title":"Game overview","content":"http\/\/test\/video\/url.mp4","isExpandable":false}]';
        $actualResponse = $this->serializer->serialize($response);

        $I->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * Case 2:
     * Partner with parent selected, hasn't any preset selected
     * Parent partner has default preset assigned in English
     *
     * @param UnitTester $I
     */
    public function testParentExistsWithoutSelectedPreset(UnitTester $I): void
    {
        $response = $this->getBlocks('en', 'partner-with-parent-and-no-preset');

        $expectedResponse =
            '[{"title":"General","content":"Some general info about the game in English","isExpandable":true}'
            . ',{"title":"Rules","content":"Game rules list in English","isExpandable":true}'
            . ',{"title":"Game overview","content":"http\/\/test\/video\/url.mp4","isExpandable":false}]';
        $actualResponse = $this->serializer->serialize($response);

        $I->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * Case 3:
     * Partner with no parent selected, hasn't any preset selected
     *
     * @param UnitTester $I
     */
    public function testNoParentWithoutSelectedPreset(UnitTester $I): void
    {
        $response = $this->getBlocks('en', 'partner-with-no-parent-and-no-preset');

        $expectedResponse =
            '[{"title":"General","content":"Some general info about the game in English","isExpandable":true}'
            . ',{"title":"Rules","content":"Game rules list in English","isExpandable":true}'
            . ',{"title":"Game overview","content":"http\/\/test\/video\/url.mp4","isExpandable":false}]';
        $actualResponse = $this->serializer->serialize($response);

        $I->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * Case 4:
     * Partner with no parent selected, has custom preset selected
     * English language requested, content in english exists
     *
     * @param UnitTester $I
     */
    public function testNoParentWithSelectedCustomEnPreset(UnitTester $I): void
    {
        $response = $this->getBlocks('en', 'partner-with-no-parent-and-custom-preset');

        $expectedResponse =
            '[{"title":"General","content":"Some general info about the game in English","isExpandable":true}'
            . ',{"title":"Rules","content":"Game rules list in English","isExpandable":true}'
            . ',{"title":"Game overview","content":"http\/\/test\/video\/url.mp4","isExpandable":false}]';
        $actualResponse = $this->serializer->serialize($response);

        $I->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * Case 5:
     * Partner with no parent selected, has custom preset selected
     * Russian language requested, has English fallback, Rules block in Russian is missing, but exists in English
     *
     * @param UnitTester $I
     */
    public function testNoParentWithSelectedCustomRuPreset(UnitTester $I): void
    {
        $response = $this->getBlocks('ru', 'partner-with-no-parent-and-custom-preset');

        $expectedResponse =
            '[{"title":"\u0413\u0435\u043d\u0435\u0440\u0430\u043b\u044c\u043d\u044b\u0439",'
            . '"content":"Some general info about the game in Russian","isExpandable":true}'
            . ',{"title":"Rules","content":"Game rules list in English","isExpandable":true}'
            . ',{"title":"\u041e\u0431\u0437\u043e\u0440 \u0438\u0433\u0440\u044b",'
            . '"content":"http\/\/test\/video\/url-russian.mp4","isExpandable":false}]';
        $actualResponse = $this->serializer->serialize($response);

        $I->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * Case 6:
     * Partner with calculate RTP automatically enabled
     * Default preset exists with RTP block assigned
     *
     * @param UnitTester $I
     */
    public function testPartnerWithRtpBlock(UnitTester $I): void
    {
        $response = $this->getBlocks('en', 'partner-with-rtp-block');

        $expectedResponse = '[{"title":"Rules","content":"Game rules list in English","isExpandable":true}'
            . ',{"title":"Return-to-player","content":"Random RTP content: 95%","isExpandable":true}]';
        $actualResponse = $this->serializer->serialize($response);

        $I->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * Case 7:
     * Partner with calculate RTP automatically disabled
     * Default preset exists with RTP block assigned
     * No RTP provided for players
     *
     * @param UnitTester $I
     */
    public function testPartnerWithoutRtpBlock(UnitTester $I): void
    {
        /** @var PartnerEnabledGame $enabledGame */
        $enabledGame = $this->getEntityByReference('partner-enabled-game:partner-with-rtp-block');
        $enabledGame->getPartner()->setShowRtpInHtp(false);

        $response = $this->getBlocks('en', 'partner-with-rtp-block');
        $expectedResponse = '[{"title":"Rules","content":"Game rules list in English","isExpandable":true}]';
        $actualResponse = $this->serializer->serialize($response);

        $I->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * Case 8:
     * Partner with Subscriptions and Combinations enabled
     * Default preset exists with Subscriptions and Combinations blocks assigned
     *
     * @param UnitTester $I
     */
    public function testPartnerWithSubscriptionsAndCombinationsBlocks(UnitTester $I): void
    {
        $response = $this->getBlocks('en', 'partner-with-subs-and-combo-blocks');

        $expectedResponse =
            '[{"title":"Subscriptions","content":"Some content from block with subscriptions","isExpandable":true}'
            . ',{"title":"Combinations","content":"Some content from block with combinations","isExpandable":true}]';
        $actualResponse = $this->serializer->serialize($response);

        $I->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * Case 9:
     * Partner with Default preset selected
     * Polish language with no fallback requested
     *
     * @param UnitTester $I
     */
    public function testPartnerWithDefaultEmptyPreset(UnitTester $I): void
    {
        $response = $this->getBlocks('pl', 'partner-with-no-blocks');

        $expectedResponse = '[]';
        $actualResponse = $this->serializer->serialize($response);

        $I->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * Case 10:
     * Partner with show Promotion enabled for Speedy 7 game
     * Default preset exists with Jackpot block assigned
     *
     * @param UnitTester $I
     */
    public function testPartnerWithJackpotPromotionBlockSpeedy7(UnitTester $I): void
    {
        $response = $this->getBlocks('en', 'partner-with-jackpot-speedy7-block');

        $expectedResponse =
            '[{"title":"Jackpot","content":"Some content from block with jackpot for speedy7","isExpandable":true}]';
        $actualResponse = $this->serializer->serialize($response);

        $I->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * Case 11:
     * Partner with show Promotion enabled for Poker Headsup game
     * Default preset exists with Jackpot block assigned
     *
     * @param UnitTester $I
     */
    public function testPartnerWithJackpotPromotionBlockHeadsup(UnitTester $I): void
    {
        $response = $this->getBlocks('en', 'partner-with-jackpot-headsup-block');

        $expectedResponse =
            '[{"title":"Jackpot","content":"Some content from block with jackpot for headsup","isExpandable":true}]';
        $actualResponse = $this->serializer->serialize($response);

        $I->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * Case 12:
     * Partner with show Promotion enabled
     * Default preset exists with Cashback block assigned
     *
     * @param UnitTester $I
     */
    public function testPartnerWithCashbackPromotionBlock(UnitTester $I): void
    {
        $response = $this->getBlocks('en', 'partner-with-cashback-block');

        $expectedResponse =
            '[{"title":"Cashback","content":"Some content from block with cashback","isExpandable":true}]';
        $actualResponse = $this->serializer->serialize($response);

        $I->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * Case 13:
     * Partner with gamification feature enabled
     *
     * @param UnitTester $I
     */
    public function testPartnerWithGamificationBlock(UnitTester $I): void
    {
        $response = $this->getBlocks('en', 'partner-with-gamification-block');

        $expectedResponse =
            '[{"title":"Hero","content":"Some content from block with gamification","isExpandable":true}]';
        $actualResponse = $this->serializer->serialize($response);

        $I->assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * @param UnitTester $I
     */
    public function testOnScreenNotSupportedGame(UnitTester $I): void
    {
        /** @var PartnerEnabledGame $enabledGame */
        $enabledGame = $this->getEntityByReference('partner-enabled-game:1');
        $onScreen = $this->provider->getOnScreen($enabledGame, $this->defaultLanguage);
        $I->assertEmpty($onScreen->videoSrc);
    }

    /**
     * @param UnitTester $I
     */
    public function testOnScreenSupportedGame(UnitTester $I): void
    {
        /** @var PartnerEnabledGame $enabledGame */
        $enabledGame = $this->getEntityByReference('partner-enabled-game:partner-with-video-url');
        $onScreen = $this->provider->getOnScreen($enabledGame, $this->defaultLanguage);
        $I->assertNotEmpty($onScreen->videoSrc);
    }

    /**
     * @param UnitTester $I
     */
    public function testOnScreenNoVideo(UnitTester $I): void
    {
        /** @var PartnerEnabledGame $enabledGame */
        $enabledGame = $this->getEntityByReference('partner-enabled-game:partner-with-video-url');

        /** @var  HowToPlayBlockContent $blockContent */
        $blockContent = $this->getEntityByReference('htp-block-content:video-en');
        $blockContent->setContent('Text without video url "http://test.mp3" "https://test" "https://test.com"');

        $onScreen = $this->provider->getOnScreen($enabledGame, $this->defaultLanguage);
        $I->assertEquals('', $onScreen->videoSrc);
    }

    /**
     * @param UnitTester $I
     */
    public function testOnScreenWithVideo(UnitTester $I): void
    {
        /** @var PartnerEnabledGame $enabledGame */
        $enabledGame = $this->getEntityByReference('partner-enabled-game:partner-with-video-url');

        /** @var  HowToPlayBlockContent $blockContent */
        $blockContent = $this->getEntityByReference('htp-block-content:video-en');
        $blockContent->setContent('https://t.mp4');

        $onScreen = $this->provider->getOnScreen($enabledGame, $this->defaultLanguage);
        $I->assertEquals('https://t.mp4', $onScreen->videoSrc);
    }

    /**
     * @param UnitTester $I
     */
    public function testOnScreenVideOnFirstFallbackLanguage(UnitTester $I): void
    {
        /** @var PartnerEnabledGame $enabledGame */
        $enabledGame = $this->getEntityByReference('partner-enabled-game:partner-with-video-url');

        /** @var Language $mainLanguage */
        $mainLanguage = $this->getEntityByReference('language:lang_without_video');

        /** @var  HowToPlayBlockContent $blockContent */
        $blockContent = $this->getEntityByReference('htp-block-content:video-en');
        $blockContent->setContent('https://t.mp4');

        $onScreen = $this->provider->getOnScreen($enabledGame, $mainLanguage);
        $I->assertEquals('https://t.mp4', $onScreen->videoSrc);
    }

    /**
     * @param UnitTester $I
     */
    public function testOnScreenVideOnNonFirstFallbackLanguage(UnitTester $I): void
    {
        /** @var PartnerEnabledGame $enabledGame */
        $enabledGame = $this->getEntityByReference('partner-enabled-game:partner-with-video-url');

        /** @var Language $mainLanguage */
        $mainLanguage = $this->getEntityByReference('language:lang_without_video2');

        /** @var  HowToPlayBlockContent $blockContent */
        $blockContent = $this->getEntityByReference('htp-block-content:video-en');
        $blockContent->setContent('https://t.mp4');

        $onScreen = $this->provider->getOnScreen($enabledGame, $mainLanguage);
        $I->assertEquals('https://t.mp4', $onScreen->videoSrc);
    }

    /**
     * @param UnitTester $I
     *
     * @throws CoreException
     */
    public function testOnScreenCache(UnitTester $I): void
    {
        $this->provider = new HowToPlayProvider(
            $this->getMockedRepositoryProvider(),
            new CacheServiceMock()
        );

        /** @var PartnerEnabledGame $enabledGame */
        $enabledGame = $this->getEntityByReference('partner-enabled-game:partner-with-video-url');

        /** @var  HowToPlayBlockContent $blockContent */
        $blockContent = $this->getEntityByReference('htp-block-content:video-en');
        $blockContent->setContent('https://t.mp4');

        $onScreen = $this->provider->getOnScreen($enabledGame, $this->defaultLanguage);
        $I->assertEquals('https://t.mp4', $onScreen->videoSrc);

        $this->provider->getOnScreen($enabledGame, $this->defaultLanguage);
        $onScreen = $this->provider->getOnScreen($enabledGame, $this->defaultLanguage);
        $I->assertEquals('https://t.mp4', $onScreen->videoSrc);
    }

    /**
     * @param string $languageCode
     * @param string $enabledGameReference
     *
     * @return HowToPlayBlockDTO[]
     */
    private function getBlocks(
        string $languageCode,
        string $enabledGameReference
    ): array
    {
        /** @var Language $language */
        $language = $this->getEntityByReference('language:' . $languageCode);
        /** @var PartnerEnabledGame $enabledGame */
        $enabledGame = $this->getEntityByReference('partner-enabled-game:' . $enabledGameReference);

        $blocks = $this->provider->getBlocks($enabledGame, $language);

        return $blocks;
    }

    /**
     * @return RepositoryProviderInterface
     *
     * @throws CoreException
     */
    private function getMockedRepositoryProvider(): RepositoryProviderInterface
    {
        $metadata = $this->getEntityManager()->getClassMetadata(HowToPlayPreset::class);
        $originalRepository = $this->getRepositoryProvider()->getSlaveRepository(HowToPlayPreset::class);
        $repositoryMock = Stub::construct(
            EntityRepository::class,
            [
                $this->getEntityManager(),
                $metadata
            ],
            [
                'findOneBy' => Stub\Expected::exactly(
                    1, // One for correct value, all other should go to cache.
                    function (array $condition) use ($originalRepository) {
                        return $originalRepository->findOneBy($condition);
                    }
                )
            ]
        );
        $repositoryProvider = $this->getRepositoryProvider();
        $repositoryProvider->setRepository(HowToPlayPreset::class, $repositoryMock);

        return $repositoryProvider;
    }
}
