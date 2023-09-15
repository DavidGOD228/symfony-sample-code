<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Request;

use Acme\SymfonyRequest\Request;
use CoreBundle\Exception\ValidationException;
use CoreBundle\Request\Validator;
use Exception;
use GamesApiBundle\Service\BazaarResults\RequestValidator;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class BazaarResultsValidatorCest
 */
final class BazaarResultsValidatorCest extends AbstractUnitTest
{
    private RequestValidator $validator;

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        $container = $I->getContainer();

        $this->validator = new RequestValidator(
            $container->get(Validator::class)
        );
    }

    /**
     * @param UnitTester $I
     *
     * @throws Exception
     */
    public function testConstraints(UnitTester $I): void
    {
        $I->expectThrowable(
            new ValidationException('[timezone] This value should be of type numeric.'),
            function (): void {
                $request = new Request(['timezone' => 'aaa']);
                $this->validator->validate($request);
            }
        );

        $I->expectThrowable(
            new ValidationException('[timezone] This value should be between -12 and 12.'),
            function (): void {
                $request = new Request(['timezone' => '-13']);
                $this->validator->validate($request);
            }
        );
    }
}
