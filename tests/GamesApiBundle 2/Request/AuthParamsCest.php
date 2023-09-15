<?php

namespace SymfonyTests\Unit\GamesApiBundle\Request;

use GamesApiBundle\Request\AuthParams;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;
use CoreBundle\Exception\ValidationException;

/**
 * Class AuthParamsCest
 */
class AuthParamsCest extends AbstractUnitTest
{
    /**
     * @var array
     */
    private $body = [
        'partner_code' => 'test',
        'token' => '123',
        'language' => 'en',
        'timezone' => '3',
        'is_mobile' => true,
        'odds_format' => 'hongkong',
    ];

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testGetters(UnitTester $I): void
    {
        $params = new AuthParams($this->body);

        $I->assertEquals($this->body['partner_code'], $params->getPartnerCode());
        $I->assertEquals($this->body['token'], $params->getToken());
        $I->assertEquals($this->body['language'], $params->getLanguage());
        $I->assertEquals($this->body['timezone'], $params->getTimezone());
        $I->assertEquals($this->body['is_mobile'], $params->getIsMobile());
        $I->assertEquals($this->body['odds_format'], $params->getOddsFormat());
    }
}