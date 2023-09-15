<?php

namespace SymfonyTests\Unit\GamesApiBundle\Request;

use CoreBundle\Exception\ValidationException;
use GamesApiBundle\Request\OddInfoParams;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class OddInfoParamsCest
 */
class OddInfoParamsCest extends AbstractUnitTest
{
    protected const TYPE_NOT_EXPECTED = 'not_expected';
    protected const TYPE_IS_MISSING = 'is_missing';
    protected const TYPE_NOT_NULL = 'not_null';
    protected const TYPE_NOT_BLANK = 'not_blank';
    protected const TYPE_NOT_ARRAY = 'not_array';
    protected const TYPE_NOT_STRING = 'not_string';
    protected const TYPE_NOT_VALID = 'not_valid';
    protected const TYPE_IS_NOT_INT = 'is_not_int';

    protected const TYPES_MESSAGES = [
        self::TYPE_NOT_EXPECTED => 'This field was not expected.',
        self::TYPE_IS_MISSING => 'This field is missing.',
        self::TYPE_NOT_NULL => 'This value should not be null.',
        self::TYPE_NOT_BLANK => 'This value should not be blank.',
        self::TYPE_NOT_ARRAY => 'This value should be of type array.',
        self::TYPE_NOT_STRING => 'This value should be of type string.',
        self::TYPE_NOT_VALID => 'This value is not valid.',
        self::TYPE_IS_NOT_INT => 'This value should be of type int.',
    ];

    /**
     * @param string $key
     * @param string $type
     * @param string|null $index
     *
     * @return ValidationException
     */
    protected function getException(string $key, string $type, ?string $index = ''): ValidationException
    {
        if (isset(self::TYPES_MESSAGES[$type])) {
            return new ValidationException('[' . $key . ']' . $index . ' ' . self::TYPES_MESSAGES[$type]);
        }

        return new ValidationException('Type of error was not found');
    }


    /**
     *  @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testParams(UnitTester $I)
    {
        $cases = [];

        // CASE 0: empty array.
        $cases[] = [
            'input' => [],
            'exception' => $this->getException(OddInfoParams::FIELD_KEY, self::TYPE_IS_MISSING),
            'ids' => null,
        ];

        // CASE 1: array with blank value.
        $cases[] = [
            'input' => [''],
            'exception' => $this->getException(OddInfoParams::FIELD_KEY, self::TYPE_IS_MISSING),
            'ids' => null,
        ];

        // CASE 2: array with additional wrong field.
        $cases[] = [
            'input' => ['not_expected_key' => 1, 'odd_ids' => [1]],
            'exception' => $this->getException('not_expected_key', self::TYPE_NOT_EXPECTED),
            'ids' => null,
        ];

        // CASE 3: null.
        $cases[] = [
            'input' => ['odd_ids' => null],
            'exception' => $this->getException(OddInfoParams::FIELD_KEY, self::TYPE_NOT_NULL),
            'ids' => null,
        ];

        // CASE 4: string.
        $cases[] = [
            'input' => ['odd_ids' => '1,2,3'],
            'exception' => $this->getException(OddInfoParams::FIELD_KEY, self::TYPE_NOT_ARRAY),
            'ids' => null,
        ];

        // CASE 5: empty.
        $cases[] = [
            'input' => ['odd_ids' => []],
            'exception' => $this->getException(OddInfoParams::FIELD_KEY, self::TYPE_NOT_BLANK),
            'ids' => null,
        ];

        // CASE 6: empty string.
        $cases[] = [
            'input' => ['odd_ids' => ['']],
            'exception' => $this->getException(OddInfoParams::FIELD_KEY, self::TYPE_NOT_BLANK, '[0]'),
            'ids' => null,
        ];

        // CASE 7: array with not integers.
        $cases[] = [
            'input' => ['odd_ids' => ['1', 'asdaw']],
            'exception' => $this->getException(OddInfoParams::FIELD_KEY, self::TYPE_IS_NOT_INT, '[0]'),
            'ids' => null,
        ];

        // CASE 8: array with not integers.
        $cases[] = [
            'input' => ['odd_ids' => [1, "' trying 1 = 1 \\ . ;% "]],
            'exception' => $this->getException(OddInfoParams::FIELD_KEY, self::TYPE_IS_NOT_INT, '[1]'),
            'ids' => null,
        ];

        // CASE 9: string.
        $cases[] = [
            'input' => ['odd_ids' => ['1', '2', '3']],
            'exception' => $this->getException(OddInfoParams::FIELD_KEY, self::TYPE_IS_NOT_INT, '[0]'),
            'ids' => null,
        ];

        // CASE 10: valid request.
        $cases[] = [
            'input' => ['odd_ids' => [1, 2, 3]],
            'exception' => null,
            'ids' => [1, 2, 3],
        ];

        foreach ($cases as $case) {
            $validationException = null;
            $ids = null;

            try {
                $oddInfoParams = new OddInfoParams($case['input']);
                $ids = $oddInfoParams->oddIds;
            } catch (ValidationException $exception) {
                $validationException = $exception;
            }

            $I->assertEquals($case['exception'], $validationException);
            $I->assertSame($case['ids'], $ids);
        }
    }
}