<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\BetAmounts;

use Acme\SymfonyDb\Entity\Currency;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class CurrencyFixture
 */
final class CurrencyFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $fixtures = [
            'currency:eur' => [
                'id' => 1,
                'code' => 'eur',
                'rate' => 1
            ],
            'currency:usd' => [
                'id' => 2,
                'code' => 'usd',
                'rate' => 0.83
            ]
        ];

        foreach ($fixtures as $ref => $params) {
            $currency = $this->getEntity(
                $params['id'],
                $params['code'],
                $params['rate']
            );

            $this->addReference($ref, $currency);
            $manager->persist($currency);
        }

        $manager->flush();
    }

    /**
     * @param int $id
     * @param string $code
     * @param float $rate
     *
     * @return Currency
     */
    private function getEntity(int $id, string $code, float $rate): Currency
    {
        return Currency::createFromId($id)
            ->setName(ucfirst($code))
            ->setTemplate($code . 'X')
            ->setPrecision(2)
            ->setCode(strtolower($code))
            ->setApproximateRate($rate)
            ->setIsEnabled(true)
            ->setIsVisible(true)
            ;
    }
}
