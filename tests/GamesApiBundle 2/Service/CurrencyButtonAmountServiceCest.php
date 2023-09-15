<?php
namespace SymfonyTests\Unit\GamesApiBundle\Service;

use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\CurrencyButtonAmount;
use Codeception\Stub;
use CoreBundle\Service\CacheService;
use Doctrine\ORM\EntityRepository;
use GamesApiBundle\Service\Initial\Component\CurrencyButtonAmountService;
use SymfonyTests\_support\Doctrine\EntityHelper;
use SymfonyTests\Unit\AbstractUnitTest;
use SymfonyTests\Unit\GamesApiBundle\Fixture\CurrencyButtonAmountFixture;
use SymfonyTests\UnitTester;

/**
 * Class CurrencyButtonAmountServiceCest
 */
class CurrencyButtonAmountServiceCest extends AbstractUnitTest
{
    /**
     * @var array
     */
    protected array $tables = [
        CurrencyButtonAmount::class
    ];

    /**
     * @var array
     */
    protected array $fixtures = [
        CurrencyButtonAmountFixture::class
    ];

    /** @inheritDoc */
    protected function setUpFixtures(): void
    {
        $this->fixtureBoostrapper->addPartners(1, true);
    }


    /**
     * @param UnitTester $I
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \ReflectionException
     */
    public function testGetBetAmounts(UnitTester $I): void
    {
        /* @var CacheService $cache */
        $cache = $I->getContainer()->get(CacheService::class);

        $repositoryProvider = $this->getRepositoryProvider();
        $repository = $repositoryProvider->getSlaveRepository(CurrencyButtonAmount::class);
        $mockedRepository = Stub::make(
            EntityRepository::class,
            [
                'findBy' => Stub\Expected::exactly(
                    2, // One for each currency.
                    function (array $criteria, array $order) use ($repository) {
                        return $repository->findBy($criteria, $order);
                    }
                ),
            ]
        );
        $this->stubsToVerify[] = $mockedRepository;

        $repositoryProvider->setRepository(CurrencyButtonAmount::class, $mockedRepository);

        $service = new CurrencyButtonAmountService($cache, $repositoryProvider);

        /* @var Currency $currency */
        $currency = $this->getEntityByReference('currency:eur');

        $expectedResponse = [
            $this->getEntityByReference('currencyButtonAmount:eur:1'),
            $this->getEntityByReference('currencyButtonAmount:eur:3'),
            $this->getEntityByReference('currencyButtonAmount:eur:5'),
            $this->getEntityByReference('currencyButtonAmount:eur:10'),
            $this->getEntityByReference('currencyButtonAmount:eur:50'),
            $this->getEntityByReference('currencyButtonAmount:eur:100'),
        ];

        $response = $service->getBetAmounts($currency);

        foreach ($expectedResponse as $key => $value) {
            $I->assertEquals($expectedResponse[$key]->getId(), $response[$key]->getId());
            $I->assertEquals($expectedResponse[$key]->getValue(), $response[$key]->getValue());
            $I->assertEquals(
                $expectedResponse[$key]->getCurrency()->getId(),
                $response[$key]->getCurrency()->getId()
            );
        }

        $response = $service->getBetAmounts($currency);

        foreach ($expectedResponse as $key => $value) {
            $I->assertEquals($expectedResponse[$key]->getId(), $response[$key]->getId());
            $I->assertEquals($expectedResponse[$key]->getValue(), $response[$key]->getValue());
            $I->assertEquals(
                $expectedResponse[$key]->getCurrency()->getId(),
                $response[$key]->getCurrency()->getId()
            );
        }

        $newCurrency = new Currency();
        $notExistingCurrencyId = 999999;
        EntityHelper::setId($newCurrency, $notExistingCurrencyId);

        $response = $service->getBetAmounts($newCurrency);
        $I->assertEmpty($response);

        $cache = $cache->get('currency:amount_buttons:v2:999999');
        $I->assertNotEmpty($cache);

        // Executing get twice to verify cache.
        $response = $service->getBetAmounts($newCurrency);
        $I->assertEmpty($response);
    }
}
