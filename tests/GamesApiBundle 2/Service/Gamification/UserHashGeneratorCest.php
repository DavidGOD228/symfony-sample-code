<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Service\Gamification;

use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Entity\PlayerProfile;
use Codeception\Example;
use DateTimeImmutable;
use GamesApiBundle\Service\Gamification\UserHashGenerator;
use SymfonyTests\_support\Doctrine\EntityHelper;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\UnitTester;

/**
 * Class UserHashGeneratorCest
 */
final class UserHashGeneratorCest extends AbstractUnitTest
{
    /**
     * @param UnitTester $I
     */
    public function testGetProfileForSigning(UnitTester $I): void
    {
        $player = (new Player())
            ->setPartner((new Partner())->setApiCode('api-code'))
            ->setTag('existing')
            ->setTaggedAt(new DateTimeImmutable())
        ;
        EntityHelper::setId($player, 123);

        $profile = (new PlayerProfile())
            ->setName('Mark')
            ->setAvatarUrl('https://blah-blah')
            ->setPlayer(
                $player
            );

        $service = new UserHashGenerator();
        $forSigning = $service->getProfileForSigning($profile, 'test01');
        $I->assertEquals(
            [
                'id' => 'test01:3903703ea8df6d25adb3d3c776360f27a393806008defeac00a15be1d8c209b8d1c8408fbdfa' .
                    'd398c2e1d2a9b983f15255a27d862766407feb6c1888d38ea751',
                'name' => 'Mark',
                'image' => 'https://blah-blah',
                'partner_code' => 'test01:api-code',
            ],
            $forSigning
        );
    }

    /**
     * @param UnitTester $I
     * @param Example $example
     *
     * @dataProvider pageProvider
     */
    public function testGenerate(UnitTester $I, Example $example): void
    {
        $service = new UserHashGenerator();

        $signature = $service->generate($example['data'], $example['secret']);
        $I->assertEquals(
            $example['expected'],
            $signature
        );
    }

    /**
     * @return array
     *
     * phpcs:disable Generic.Files.LineLength.TooLong - long strings more readable here then concat or files.
     */
    protected function pageProvider(): array
    {
        return [
            [ // Simple check that signature is correct
                'data' => ['a' => 5, 'b' => 7],
                'secret' => 'blah',
                'expected' => 'MGFhYTNhNmE2MGM3ODQwODI2NDBlOTM5MGFkZDg0MzQzM2RhOTU5NTMzNjYyMGE0NWE5MzkxZTQwYjliYTcy' .
                    'ZGE4NDVhYWE2NTg3ODEwNjJmZTViYjc3MWNiMjgwNDg0ZThiZTllZDE1NmJmYTA0MGNjNzNlMTE3MGYwOTk5OGI',
            ],
            [ // Check that keys order doesn't matter
                'data' => ['b' => 7, 'a' => 5],
                'secret' => 'blah',
                'expected' => 'MGFhYTNhNmE2MGM3ODQwODI2NDBlOTM5MGFkZDg0MzQzM2RhOTU5NTMzNjYyMGE0NWE5MzkxZTQwYjliYTc' .
                    'yZGE4NDVhYWE2NTg3ODEwNjJmZTViYjc3MWNiMjgwNDg0ZThiZTllZDE1NmJmYTA0MGNjNzNlMTE3MGYwOTk5OGI',
            ],
            [ // Check that secret does matter
                'data' => ['b' => 7, 'a' => 5],
                'secret' => 'blah2',
                'expected' => 'NDFmNmU3NzY0ZmQ2OGM1ZTM0NTYwOTk0MjUzNWI3NmI3ZWFjMThhNThiYzg4NDIyZTc2Zjg4ODQxMDAyYmM3' .
                    'MDVhMDJkNmQ5ZjYzOWIzYTUyNjhlNmNkNDcxOWE1MjdkNGU5ZGM3MThmZjQyM2JiNjkzYmU1ZDc4NTkyMmRhZTc',
            ],
            [ // Check whitespaces
                'data' => ['b' => "\t", 'a' => "\n\r"],
                'secret' => 'blah2',
                'expected' => 'ZGM4N2Q3NGY3MmI2YThmOGU5NThmZDZhNDgxOGZlNTExNDQzMGVjODIwNTE4MzY0MGUwMDk4YWQ1YjI2NmZj' .
                    'YjhmNmY5OTEyZmVhYWRlMzhkMDJhN2NiNjJmM2MwMmNiZGNmM2U2OTRjMzJhNzcwMmNkN2Y5ODM4OGE1YmJkNTA',
            ],
        ];
    }
}
