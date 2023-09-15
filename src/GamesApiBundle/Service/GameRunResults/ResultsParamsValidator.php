<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults;

use CoreBundle\Constraint\GameConstraint;
use CoreBundle\Constraint\GamesListConstraint;
use CoreBundle\Exception\ValidationException;
use CoreBundle\Request\Validator;
use GamesApiBundle\DataObject\GameRunResults\ResultsParams;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ResultsParamsValidator
 */
final class ResultsParamsValidator
{
    private Validator $validator;

    /**
     * ResultsParamsValidator constructor.
     *
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Request $request
     *
     * @throws ValidationException
     */
    public function validate(Request $request): void
    {
        $collections = [
            ResultsParams::FIELD_TIMEZONE => [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'numeric']),
                new Assert\Range(['min' => -12, 'max' => 12]),
            ],
            ResultsParams::FIELD_PAGE => [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'numeric']),
                new Assert\Range(['min' => 1]),
            ],
            ResultsParams::FIELD_DATE => [
                new Assert\Optional([
                    new Assert\DateTime('Y-m-d'),
                ]),
            ],
            ResultsParams::FIELD_GAME_ID => [
                new Assert\Optional([
                    new GameConstraint()
                ]),
            ],
            ResultsParams::FIELD_GAMES_IDS => [
                new Assert\Optional([
                    new Assert\NotBlank(),
                    new GamesListConstraint(),
                ]),
            ],
        ];

        $constraints = new Assert\Collection($collections);
        $constraints->allowExtraFields = true;

        $this->validator->validateConstraints($request->query->all(), $constraints);
    }
}