<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture;

use Acme\SymfonyDb\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class CurrencyFixture
 */
class CurrencyFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $currency = Currency::createFromId(2)
            ->setName('Rub')
            ->setTemplate('X')
            ->setCode('rub')
            ->setPrecision(2)
            ->setApproximateRate(2)
            ->setIsEnabled(true)
            ->setIsVisible(true)
        ;

        $manager->persist($currency);
        $manager->flush();
        $this->addReference('currency:rub', $currency);

        $currency = Currency::createFromId(3)
            ->setName('test')
            ->setTemplate('X')
            ->setCode('test')
            ->setPrecision(2)
            ->setApproximateRate(2)
            ->setIsEnabled(false)
            ->setIsVisible(false)
        ;

        $manager->persist($currency);
        $manager->flush();
        $this->addReference('currency:test', $currency);

        $currency = Currency::createFromId(4)
            ->setName('demo')
            ->setTemplate('X')
            ->setCode('demo')
            ->setPrecision(2)
            ->setApproximateRate(1)
            ->setIsEnabled(true)
            ->setIsVisible(false)
        ;

        $manager->persist($currency);
        $manager->flush();
        $this->addReference('currency:demo', $currency);
    }
}
