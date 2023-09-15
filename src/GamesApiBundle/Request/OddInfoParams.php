<?php

namespace GamesApiBundle\Request;

use CoreBundle\Exception\ValidationException;

/**
 * Class OddInfoParams
 */
class OddInfoParams
{
    const FIELD_KEY = 'odd_ids';

    /**
     * @var int[]
     */
    public $oddIds;

    /**
     * OddInfoParams constructor.
     *
     * @param array $input
     *
     * @throws ValidationException
     */
    public function __construct(array $input)
    {
        $validator = new OddInfoParamsValidator();
        $validator->validate($input);

        $this->oddIds = $input[self::FIELD_KEY];
    }
}