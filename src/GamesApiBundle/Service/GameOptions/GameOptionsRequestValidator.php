<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\GameOptions;

use CoreBundle\Exception\ValidationException;
use CoreBundle\Request\Validator;
use GamesApiBundle\DataObject\GameOptions\GameOptionsRequest;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class GameOptionsRequestValidator
 */
final class GameOptionsRequestValidator
{
    private Validator $validator;

    /**
     * GameSettingsRequestValidator constructor.
     *
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param array $input
     *
     * @throws ValidationException
     */
    public function validate(array $input): void
    {
        $constraints = new Assert\Collection([
            GameOptionsRequest::FIELD_KEY => [
                new Assert\Required([
                    new Assert\Type('array'),
                    new Assert\NotBlank(),
                    new Assert\All([
                        new Assert\NotNull(),
                        new Assert\Type('numeric')
                    ]),
                ]),
            ],
        ]);

        $constraints->allowExtraFields = true;

        $this->validator->validateConstraints($input, $constraints);
    }
}