<?php

namespace SymfonyTests\Unit\GamesApiBundle\Controller;

use Codeception\Stub;
use GamesApiBundle\Controller\TranslationController;
use GamesApiBundle\Service\Translation\TranslationProvider;
use Symfony\Component\HttpFoundation\Request;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class TranslationControllerCest
 */
class TranslationControllerCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testIframeFormat(UnitTester $I): void
    {
        /* @var TranslationProvider $translationProvider */
        $translationProvider = Stub::make(
            TranslationProvider::class,
            [
                'getIframeTranslations' => ['key' => 'value'],
            ]
        );
        $controller = new TranslationController($translationProvider);
        $request = new Request([], ['languageCode' => 'en']);
        $response = $controller->getAction($request);
        $I->assertEquals('{"translations":{"key":"value"},"success":true}', $response->getContent());
        $I->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testWidgetFormat(UnitTester $I): void
    {
        /* @var TranslationProvider $translationProvider */
        $translationProvider = Stub::make(
            TranslationProvider::class,
            [
                'getWidgetTranslations' => ['key' => 'value'],
            ]
        );
        $controller = new TranslationController($translationProvider);
        $request = new Request([], ['languageCode' => 'en']);
        $response = $controller->getWidgetAction($request);
        $I->assertEquals('{"key":"value"}', $response->getContent());
        $I->assertEquals(200, $response->getStatusCode());
    }
}