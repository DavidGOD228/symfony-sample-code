<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\Gamification;

use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Entity\PlayerProfile;
use Codeception\Stub;
use CoreBundle\Constraint\UniqueEntityPropertyConstraintValidator;
use CoreBundle\Exception\ValidationException;
use Doctrine\ORM\EntityManagerInterface;
use GamesApiBundle\Service\Gamification\RequestValidator;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\PartnerFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\PlayerFixture;
use SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification\PlayerProfileFixture;
use SymfonyTests\UnitTester;

/**
 * Class RequestValidatorCest
 */
final class RequestValidatorCest extends AbstractUnitTest
{
    protected array $tables = [
        Partner::class,
        Player::class,
        PlayerProfile::class,
    ];

    protected array $fixtures = [
        PartnerFixture::class,
        PlayerFixture::class,
        PlayerProfileFixture::class,
    ];

    /**
     * {@inheritDoc}
     */
    protected function setUpFixtures(): void
    {
        parent::setUpFixtures();
        $this->fixtureBoostrapper->addPartners(1);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    protected function setUp(UnitTester $I): void
    {
        parent::setUp($I);
        $this->fixtureBoostrapper->addPartners(1);

        $repository = $this->getRepositoryProvider()->getMasterRepository(PlayerProfile::class);

        $em = Stub::makeEmpty(EntityManagerInterface::class, [
            'getRepository' => $repository
        ]);
        $uniqueEntityValidator = new UniqueEntityPropertyConstraintValidator($em);

        $I->getContainer()->set(UniqueEntityPropertyConstraintValidator::class, $uniqueEntityValidator);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testValidateCreateRequest(UnitTester $I): void
    {
        /** @var RequestValidator $validator */
        $validator = $I->getContainer()->get(RequestValidator::class);

        $requestBody = [];
        $I->expectThrowable(
            new ValidationException('This field is missing.'),
            fn () => $validator->validateCreateRequest($requestBody)
        );

        $requestBody = ['name' => ['a', 'b']];
        $I->expectThrowable(
            new ValidationException('This value should be of type string.'),
            fn () => $validator->validateCreateRequest($requestBody)
        );

        $requestBody = ['name' => 'z'];
        $I->expectThrowable(
            new ValidationException('gamification.wrong_length'),
            fn () => $validator->validateCreateRequest($requestBody)
        );

        $requestBody = ['name' => 'zzsdsdyY(&*YRUIahfjkbamcrhw3r92,3xro2'];
        $I->expectThrowable(
            new ValidationException('gamification.wrong_length'),
            fn () => $validator->validateCreateRequest($requestBody)
        );

        $requestBody = ['name' => 'hdhdh±-LL'];
        $I->expectThrowable(
            new ValidationException('gamification.invalid_characters'),
            fn () => $validator->validateCreateRequest($requestBody)
        );

        $requestBody = ['name' => 'profile3'];
        $I->expectThrowable(
            new ValidationException('gamification.not_unique'),
            fn () => $validator->validateCreateRequest($requestBody)
        );

        $requestBody = ['name' => 'zzsdsdyY(&*YR'];
        $validator->validateCreateRequest($requestBody);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testValidateBlockRequest(UnitTester $I): void
    {
        /** @var RequestValidator $validator */
        $validator = $I->getContainer()->get(RequestValidator::class);

        $requestBody = [];
        $I->expectThrowable(
            new ValidationException('This field is missing.'),
            fn () => $validator->validateBlockRequest($requestBody)
        );

        $requestBody = ['reason' => ['a', 'b']];
        $I->expectThrowable(
            new ValidationException('This value should be of type string.'),
            fn () => $validator->validateBlockRequest($requestBody)
        );

        $requestBody = ['reason' => ''];
        $I->expectThrowable(
            new ValidationException('This value should not be blank.'),
            fn () => $validator->validateBlockRequest($requestBody)
        );

        $requestBody = ['reason' => 'wrong_key'];
        $I->expectThrowable(
            new ValidationException('The value you selected is not a valid choice.'),
            fn () => $validator->validateBlockRequest($requestBody)
        );

        $requestBody = ['reason' => 'annoys_notifications'];
        $validator->validateBlockRequest($requestBody);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testValidateUpdateRequest(UnitTester $I): void
    {
        /** @var RequestValidator $validator */
        $validator = $I->getContainer()->get(RequestValidator::class);

        $requestBody = [];
        $I->expectThrowable(
            new ValidationException('This field is missing.'),
            fn () => $validator->validateUpdateRequest($requestBody)
        );

        $requestBody = ['avatar' => ['a', 'b']];
        $I->expectThrowable(
            new ValidationException('This value should be of type string.'),
            fn () => $validator->validateUpdateRequest($requestBody)
        );

        $requestBody = ['avatar' => 'wrong_image'];
        $I->expectThrowable(
            new ValidationException('The value you selected is not a valid choice.'),
            fn () => $validator->validateUpdateRequest($requestBody)
        );

        $requestBody = ['avatar' => 'avatar_12'];
        $validator->validateUpdateRequest($requestBody);
    }

    /**
     * @param UnitTester $I
     *
     * @throws ValidationException
     */
    public function testValidateDeleteRequest(UnitTester $I): void
    {
        /** @var RequestValidator $validator */
        $validator = $I->getContainer()->get(RequestValidator::class);

        $requestBody = [];
        $I->expectThrowable(
            new ValidationException('This field is missing.'),
            fn () => $validator->validateDeleteRequest($requestBody)
        );

        $requestBody = ['reason' => ['a', 'b']];
        $I->expectThrowable(
            new ValidationException('This value should be of type string.'),
            fn () => $validator->validateDeleteRequest($requestBody)
        );

        $requestBody = ['reason' => ''];
        $I->expectThrowable(
            new ValidationException('This value should not be blank.'),
            fn () => $validator->validateDeleteRequest($requestBody)
        );

        $requestBody = ['reason' => 'wrong_key'];
        $I->expectThrowable(
            new ValidationException('The value you selected is not a valid choice.'),
            fn () => $validator->validateDeleteRequest($requestBody)
        );

        $requestBody = ['reason' => 'other'];
        $validator->validateDeleteRequest($requestBody);
    }
}
