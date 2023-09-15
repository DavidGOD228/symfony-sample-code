<?php

namespace GamesApiBundle\Request;

use CoreBundle\Exception\ValidationException;
use CoreBundle\Service\AbstractRequestParamValidator;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OddInfoParamsValidator
 */
class OddInfoParamsValidator extends AbstractRequestParamValidator
{
    /**
     * @param array $input
     *
     * @throws ValidationException
     */
    public function validate(array $input): void
    {
        $isArrayValid = new Assert\Collection([
            OddInfoParams::FIELD_KEY => [
                new Assert\NotNull(),
                new Assert\NotBlank(),
                new Assert\Type('array')
            ],
        ]);
        $isIdsValid = new Assert\Collection([
            OddInfoParams::FIELD_KEY => new Assert\All([
                new Assert\NotNull(),
                new Assert\NotBlank(),
                new Assert\Type('int'),
                new Assert\Regex('/^\d+$/')
            ]),
        ]);

        $this->validateConstraints($input, [$isArrayValid, $isIdsValid]);
    }
}