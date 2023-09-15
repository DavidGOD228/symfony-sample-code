<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture;

use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\CurrencyButtonAmount;
use Doctrine\Persistence\ObjectManager;
use SymfonyTests\Unit\CoreBundle\Fixture\AbstractCustomizableFixture;

/**
 * Class CurrencyButtonAmount
 */
class CurrencyButtonAmountFixture extends AbstractCustomizableFixture
{
    private const DEFAULT_EUR_VALUES = [
        1,
        3,
        5,
        10,
        50,
        100
    ];

    /**
     * @var array
     */
    protected array $tables = [
        CurrencyButtonAmount::class
    ];

    /**
     * @var string
     */
    private $currencyCode;

    /**
     * CurrencyButtonAmountFixture constructor.
     *
     * @param string $currencyCode
     */
    public function __construct(string $currencyCode = 'eur')
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function doLoad(ObjectManager $manager): void
    {
        $currency = $this->getReference('currency:' . $this->currencyCode);
        foreach (self::DEFAULT_EUR_VALUES as $value) {
            $this->entities[] = $this->getEntity($currency, $value);
        }

        foreach ($this->entities as $entity) {
            /* @var CurrencyButtonAmount $entity */
            $manager->persist($entity);
            $this->addReference('currencyButtonAmount:' . $this->currencyCode . ':' . $entity->getValue(), $entity);
        }
        $manager->flush();
    }

    /**
     * @param Currency $currency
     * @param float $value
     *
     * @return CurrencyButtonAmount
     * @throws \Exception
     */
    private function getEntity(Currency $currency, float $value): CurrencyButtonAmount
    {
        $currencyButtonAmount = (new CurrencyButtonAmount())
            ->setCurrency($currency)
            ->setValue($value);

        return $currencyButtonAmount;
    }
}
