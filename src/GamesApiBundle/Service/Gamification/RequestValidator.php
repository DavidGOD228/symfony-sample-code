<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification;

use Acme\SymfonyDb\Entity\PlayerProfile;
use CoreBundle\Constraint\UniqueEntityPropertyConstraint;
use CoreBundle\Exception\ValidationException;
use CoreBundle\Request\Validator;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class RequestValidator
 */
final class RequestValidator
{
    public const FIELD_NAME = 'name';
    public const FIELD_AVATAR = 'avatar';
    public const FIELD_REASON = 'reason';

    public const BLOCK_REASONS_KEYS = [
        'see_no_value',
        'annoys_notifications',
        'not_understand',
        'other',
    ];

    private Validator $validator;
    private AvatarProvider $avatarProvider;

    /**
     * RequestValidator constructor.
     *
     * @param Validator $validator
     * @param AvatarProvider $avatarProvider
     */
    public function __construct(
        Validator $validator,
        AvatarProvider $avatarProvider
    )
    {
        $this->validator = $validator;
        $this->avatarProvider = $avatarProvider;
    }

    /**
     * @param array $requestBody
     *
     * @throws ValidationException
     *
     * TODO: Improve validation after analysis - https://jira.Acme.tv/browse/CORE-2616
     */
    public function validateCreateRequest(array $requestBody): void
    {
        $constraints = new Assert\Collection([
            self::FIELD_NAME => [
                new Assert\NotBlank([
                    'message' => 'gamification.not_unique',
                ]),
                new Assert\Type(['type' => 'string']),
                new UniqueEntityPropertyConstraint([
                    'entityClass' => PlayerProfile::class,
                    'field' => self::FIELD_NAME,
                    'message' => 'gamification.not_unique',
                ]),
                new Assert\Length([
                    'min' => 4,
                    'max' => 15,
                    'minMessage' => 'gamification.wrong_length',
                    'maxMessage' => 'gamification.wrong_length',
                ]),
                new Assert\Regex([
                    'pattern' => '/^[a-zA-Z0-9\!\@\#\$\%\^\&\*\(\)\-\_\=\+\{\}\[\]\;\:\|\,\.\?\/\~]+$/',
                    'message' => 'gamification.invalid_characters',
                ]),
            ],
        ]);

        $this->validator->validateConstraintsWithExceptionWithoutField($requestBody, $constraints);
    }

    /**
     * @param array $requestBody
     *
     * @throws ValidationException
     */
    public function validateUpdateRequest(array $requestBody): void
    {
        $choices = $this->avatarProvider->getAvailableAvatars();

        $constraints = new Assert\Collection([
            self::FIELD_AVATAR => [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'string']),
                new Assert\Choice([
                    'strict' => true,
                    'choices' => $choices
                ])
            ],
        ]);

        $this->validator->validateConstraintsWithExceptionWithoutField($requestBody, $constraints);
    }

    /**
     * @param array $requestBody
     *
     * @throws ValidationException
     */
    public function validateBlockRequest(array $requestBody): void
    {
        $constraints = new Assert\Collection([
            self::FIELD_REASON => [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'string']),
                new Assert\Choice([
                    'strict' => true,
                    'choices' => self::BLOCK_REASONS_KEYS
                ])
            ],
        ]);

        $this->validator->validateConstraintsWithExceptionWithoutField($requestBody, $constraints);
    }

    /**
     * @param array $requestBody
     *
     * @throws ValidationException
     */
    public function validateDeleteRequest(array $requestBody): void
    {
        $constraints = new Assert\Collection([
            self::FIELD_REASON => [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'string']),
                new Assert\Choice([
                    'strict' => true,
                    'choices' => self::BLOCK_REASONS_KEYS
                ])
            ],
        ]);

        $this->validator->validateConstraintsWithExceptionWithoutField($requestBody, $constraints);
    }
}
