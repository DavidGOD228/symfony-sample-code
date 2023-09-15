<?php

namespace SymfonyTests\Unit\GamesApiBundle\Request;

use GamesApiBundle\Request\AuthParamsValidator;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;
use CoreBundle\Exception\ValidationException;

/**
 * Class AuthParamsValidatorCest
 */
class AuthParamsValidatorCest extends AbstractUnitTest
{
    /**
     * @var array
     */
    private $body = [
        'partner_code' => 'test',
        'token' => '123',
        'language' => 'en',
        'timezone' => '3',
        'is_mobile' => false,
        'odds_format' => 'american',
    ];

    /**
     * @param UnitTester $I
     */
    public function testMissingField(UnitTester $I): void
    {
        $validator = new AuthParamsValidator();

        $testBody = $this->body;
        unset($testBody['partner_code']);

        $I->expectThrowable(
            new ValidationException('[partner_code] This field is missing.'),
            function () use ($validator, $testBody)
            {
                $validator->validate($testBody);
            }
        );

        $testBody = $this->body;
        unset($testBody['language']);

        $I->expectThrowable(
            new ValidationException('[language] This field is missing.'),
            function () use ($validator, $testBody)
            {
                $validator->validate($testBody);
            }
        );

        $testBody = $this->body;
        unset($testBody['timezone']);

        $I->expectThrowable(
            new ValidationException('[timezone] This field is missing.'),
            function () use ($validator, $testBody)
            {
                $validator->validate($testBody);
            }
        );

        $testBody = $this->body;
        $testBody['timezone'] = 'text';

        $I->expectThrowable(
            new ValidationException('[timezone] This value should be of type numeric.'),
            function () use ($validator, $testBody)
            {
                $validator->validate($testBody);
            }
        );

        $testBody = $this->body;
        $testBody['is_mobile'] = 'false';

        $I->expectThrowable(
            new ValidationException('[is_mobile] This value should be of type bool.'),
            function () use ($validator, $testBody)
            {
                $validator->validate($testBody);
            }
        );

        $testBody = $this->body;
        $testBody['odds_format'] = 'wrong format';

        $I->expectThrowable(
            new ValidationException('[odds_format] The value you selected is not a valid choice.'),
            function () use ($validator, $testBody)
            {
                $validator->validate($testBody);
            }
        );
    }
}