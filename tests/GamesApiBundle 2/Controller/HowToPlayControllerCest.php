<?php

namespace SymfonyTests\Unit\GamesApiBundle\Controller;

use Codeception\Stub;
use CoreBundle\Exception\ValidationException;
use CoreBundle\Service\GameService;
use CoreBundle\Service\LanguageService;
use CoreBundle\Service\PartnerService;
use GamesApiBundle\Controller\HowToPlayController;
use GamesApiBundle\DataObject\HowToPlay\HowToPlayBlockDTO;
use GamesApiBundle\DataObject\HowToPlay\HowToPlayOnScreen;
use GamesApiBundle\Service\HowToPlayProvider;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class HowToPlayControllerCest
 */
class HowToPlayControllerCest extends AbstractUnitTest
{
    /**
     * @inheritDoc
     */
    protected function setUpFixtures(): void
    {
        parent::setUpFixtures();
        $this->fixtureBoostrapper->addPartners(1, true);
        $this->fixtureBoostrapper->addLanguages(['en']);
        $this->fixtureBoostrapper->addGames([1]);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testHowToPlay(UnitTester $I): void
    {
        $howToPlayBlock1 = new HowToPlayBlockDTO('Some title', 'Some content', true);
        $howToPlayBlock2 = new HowToPlayBlockDTO('Another title', 'Another content', false);
        $blocks = [$howToPlayBlock1, $howToPlayBlock2];

        /* @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        $apiCode = $partner->getApiCode();

        /* @var Language $language */
        $language = $this->getEntityByReference('language:en');
        $languageCode = $language->getCode();

        /* @var HowToPlayProvider $howToPlayProvider */
        $howToPlayProvider = Stub::make(
            HowToPlayProvider::class,
            ['getBlocks' => $blocks]
        );

        $controller = new HowToPlayController(
            new PartnerService($this->getRepositoryProvider()),
            new LanguageService($this->getRepositoryProvider()),
            new GameService($this->getRepositoryProvider()),
            $howToPlayProvider
        );
        $controller->setContainer($I->getContainer());

        $expectedResponse = '{"blocks":[{"title":"Some title","content":"Some content","expandable":true},'
            . '{"title":"Another title","content":"Another content","expandable":false}]}';
        $actualResponse = $controller->howToPlayAction($apiCode, 1, $languageCode)->getContent();
        $I->assertEquals(
            $expectedResponse,
            $actualResponse
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testHowToPlayShouldUseDefaultLanguageIfLanguageNotFound(UnitTester $I): void
    {
        $howToPlayBlock1 = new HowToPlayBlockDTO('Some title', 'Some content', true);
        $howToPlayBlock2 = new HowToPlayBlockDTO('Another title', 'Another content', false);
        $blocks = [$howToPlayBlock1, $howToPlayBlock2];

        /* @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        $apiCode = $partner->getApiCode();

        /* @var HowToPlayProvider $howToPlayProvider */
        $howToPlayProvider = Stub::make(
            HowToPlayProvider::class,
            ['getBlocks' => $blocks]
        );

        $controller = new HowToPlayController(
            new PartnerService($this->getRepositoryProvider()),
            new LanguageService($this->getRepositoryProvider()),
            new GameService($this->getRepositoryProvider()),
            $howToPlayProvider
        );
        $controller->setContainer($I->getContainer());

        $expectedResponse = '{"blocks":[{"title":"Some title","content":"Some content","expandable":true},'
            . '{"title":"Another title","content":"Another content","expandable":false}]}';
        $actualResponse = $controller->howToPlayAction($apiCode, 1, 'nonexistent language')->getContent();
        $I->assertEquals(
            $expectedResponse,
            $actualResponse
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testHowToPlayOnScreen(UnitTester $I): void
    {
        /* @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        $apiCode = $partner->getApiCode();

        /** @var HowToPlayProvider $howToPlayProvider */
        $howToPlayProvider = Stub::makeEmpty(HowToPlayProvider::class, [
            'getOnScreen' => new HowToPlayOnScreen('https://video-url.mp4'),
        ]);

        $controller = new HowToPlayController(
            new PartnerService($this->getRepositoryProvider()),
            new LanguageService($this->getRepositoryProvider()),
            new GameService($this->getRepositoryProvider()),
            $howToPlayProvider
        );
        $controller->setContainer($I->getContainer());

        $response = $controller->howToPlayOnScreenAction(
            $apiCode,
            1,
            'en'
        );
        $I->assertEquals(
            '{"videoSrc":"https:\/\/video-url.mp4"}',
            $response->getContent()
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testHowToPlayOnScreenShouldUseDefaultLanguageIfLanguageNotFound(UnitTester $I): void
    {
        /* @var Partner $partner */
        $partner = $this->getEntityByReference('partner:1');
        $apiCode = $partner->getApiCode();

        /** @var HowToPlayProvider $howToPlayProvider */
        $howToPlayProvider = Stub::makeEmpty(HowToPlayProvider::class, [
            'getOnScreen' => new HowToPlayOnScreen('https://video-url.mp4'),
        ]);

        $controller = new HowToPlayController(
            new PartnerService($this->getRepositoryProvider()),
            new LanguageService($this->getRepositoryProvider()),
            new GameService($this->getRepositoryProvider()),
            $howToPlayProvider
        );
        $controller->setContainer($I->getContainer());

        $response = $controller->howToPlayOnScreenAction(
            $apiCode,
            1,
            'nonexistent language'
        );
        $I->assertEquals(
            '{"videoSrc":"https:\/\/video-url.mp4"}',
            $response->getContent()
        );
    }
}