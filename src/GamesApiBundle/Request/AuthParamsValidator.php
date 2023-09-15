<?php

namespace GamesApiBundle\Request;

use CoreBundle\Service\AbstractRequestParamValidator;
use Symfony\Component\Validator\Constraints as Assert;
use CoreBundle\Exception\ValidationException;

/**
 * Class AuthParamsValidator
 */
class AuthParamsValidator extends AbstractRequestParamValidator
{
    /**
     * @param array $input
     *
     * @throws ValidationException
     */
    public function validate(array $input): void
    {
        $collections = [
            AuthParams::FIELD_PARTNER_CODE => [
                new Assert\NotBlank()
            ],
            AuthParams::FIELD_TOKEN => [
                new Assert\Optional()
            ],
            AuthParams::FIELD_LANGUAGE => [
                new Assert\NotBlank(),
            ],
            AuthParams::FIELD_TIMEZONE => [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'numeric']),
            ],
            AuthParams::FIELD_IS_MOBILE => [
                new Assert\Optional([new Assert\Type(['type' => 'bool']),]),
            ],
            AuthParams::FIELD_ODDS_FORMAT => [
                new Assert\Optional([
                    new Assert\Choice([
                        'strict' => true,
                        'choices' => [
                            AuthParams::ODDS_FORMAT_DECIMAL,
                            AuthParams::ODDS_FORMAT_FRACTIONAL,
                            AuthParams::ODDS_FORMAT_AMERICAN,
                            AuthParams::ODDS_FORMAT_HONGKONG,
                        ]
                    ])
                ]),
            ],
            AuthParams::FIELD_SID => [
                new Assert\Optional([new Assert\Type(['type' => 'string']),]),
            ],
        ];

        $constraints = new Assert\Collection($collections);

        $this->validateConstraints($input, [$constraints]);
    }
}