<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\GameRunResults;

use Acme\Contract\GameDefinition;
use CoreBundle\Exception\ValidationException;
use CoreBundle\Request\Validator;
use Doctrine\ORM\Tools\ToolsException;
use GamesApiBundle\Service\GameRunResults\ResultsParamsValidator;
use Symfony\Component\HttpFoundation\Request;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class ResultsParamsValidatorCest
 */
final class ResultsParamsValidatorCest extends AbstractUnitTest
{
    private ResultsParamsValidator $validator;

    private array $requestBody = [
        'timezone' => '0',
        'date' => '2020-01-01',
        'page' => '1',
    ];

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        /** @var Validator $validator */
        $validator = $I->getContainer()->get(Validator::class);
        $this->validator = new ResultsParamsValidator($validator);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testValidate(UnitTester $I): void
    {
        $rawRequest = $this->requestBody;
        unset($rawRequest['timezone']);
        $this->verifyException($I, $rawRequest, '[timezone] This field is missing.');

        $rawRequest['timezone'] = '13';
        $this->verifyException($I, $rawRequest, '[timezone] This value should be between -12 and 12.');

        $rawRequest = $this->requestBody;
        unset($rawRequest['page']);
        $this->verifyException($I, $rawRequest, '[page] This field is missing.');

        $rawRequest = $this->requestBody;
        $rawRequest['game_id'] = '9999';
        $this->verifyException($I, $rawRequest, '[game_id] UNKNOWN_GAME');

        $rawRequest = $this->requestBody;
        $rawRequest['games_ids'] = 'zzz';
        $this->verifyException($I, $rawRequest, '[games_ids] INVALID_PARAMETER_FORMAT');

        $rawRequest = $this->requestBody;
        $rawRequest['games_ids'] = GameDefinition::LUCKY_6 . ',';
        $this->verifyException($I, $rawRequest, '[games_ids] INVALID_PARAMETER_FORMAT');

        $rawRequest = $this->requestBody;
        $rawRequest['games_ids'] = implode(',', [ GameDefinition::BACCARAT, 444 ]);
        $this->verifyException($I, $rawRequest, '[games_ids] UNKNOWN_GAME_ID_444');

        // Success
        $request = new Request($this->requestBody);
        $this->validator->validate($request);

        $request = $this->requestBody;
        $request['games_ids'] = implode(',', [ GameDefinition::LUCKY_7 ]);
        $this->validator->validate(new Request($request));

        $request = $this->requestBody;
        $request['games_ids'] = implode(',', [ GameDefinition::BACCARAT, GameDefinition::LUCKY_7 ]);
        $this->validator->validate(new Request($request));
    }

    /**
     * @param UnitTester $I
     * @param array $rawRequest
     * @param string $message
     */
    private function verifyException(UnitTester $I, array $rawRequest, string $message): void
    {
        $request = new Request($rawRequest);
        $I->expectThrowable(
            new ValidationException($message),
            function () use ($request) {
                $this->validator->validate($request);
            }
        );
    }
}
