<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\Gamification;

use CoreBundle\Exception\ValidationException;
use Doctrine\ORM\Tools\ToolsException;
use GamesApiBundle\Service\Gamification\AvatarValidator;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class AvatarValidatorCest
 */
final class AvatarValidatorCest extends AbstractUnitTest
{
    private AvatarValidator $validator;

    /**
     * @param UnitTester $I
     *
     * @throws ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);

        /** @var AvatarValidator $validator */
        $validator = $I->getContainer()->get(AvatarValidator::class);
        $this->validator = $validator;
    }

    /**
     * @throws ValidationException
     */
    public function testValidatorShouldNotThrowExceptionOnExistingAvatar(): void
    {
        $this->validator->validateLevelAvailability('avatar_1', 1);
    }

    /**
     * @param UnitTester $I
     */
    public function testValidatorShouldThrowExceptionOnInvalidAvatar(UnitTester $I): void
    {
        $I->expectThrowable(
            new ValidationException('AVATAR_NOT_ALLOWED'),
            fn() => $this->validator->validateLevelAvailability('invalid_avatar', 1)
        );
    }

    /**
     * @param UnitTester $I
     */
    public function testValidatorShouldThrowExceptionOnUnavailableAvatar(UnitTester $I): void
    {
        $I->expectThrowable(
            new ValidationException('AVATAR_NOT_ALLOWED'),
            fn() => $this->validator->validateLevelAvailability('avatar_10', 3)
        );
    }
}