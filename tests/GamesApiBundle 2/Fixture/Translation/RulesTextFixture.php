<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Translation;

use Acme\SymfonyDb\Entity\RulesText;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class RulesTextFixture
 */
class RulesTextFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $rulesText1 = (new RulesText())
            ->setCode('rules.jackpot.11.01')
            ->setPublishedDate(CarbonImmutable::now());

        $rulesText2 = (new RulesText())
            ->setCode('rules.jackpot.11.02')
            ->setPublishedDate(CarbonImmutable::now());

        $rulesTextInvalid = (new RulesText())
            ->setCode('rules.jackpot.11')
            ->setPublishedDate(CarbonImmutable::now());

        $manager->persist($rulesText1);
        $manager->persist($rulesText2);
        $manager->persist($rulesTextInvalid);
        $manager->flush();
    }
}
