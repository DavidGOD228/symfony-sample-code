<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Matka\BazaarRun;

use Acme\SymfonyDb\Entity\MatkaOdd;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class MatkaOddFixture
 */
final class MatkaOddFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $odd2 = $this->getReference('bazaar-odd:2');
        $odd3 = $this->getReference('bazaar-odd:3');
        $odd4 = $this->getReference('bazaar-odd:4');
        $odd5 = $this->getReference('bazaar-odd:5');
        $odd6 = $this->getReference('bazaar-odd:6');
        $odd7 = $this->getReference('bazaar-odd:7');
        $odd8 = $this->getReference('bazaar-odd:8');
        $odd9 = $this->getReference('bazaar-odd:9');
        $odd10 = $this->getReference('bazaar-odd:10');
        $odd11 = $this->getReference('bazaar-odd:11');
        $odd12 = $this->getReference('bazaar-odd:12');
        $preset = $this->getReference('oddsPreset:18:1');
        $openingRunRound1 = $this->getReference('opening-run-round:1');
        $openingRunRound2 = $this->getReference('opening-run-round:2');
        $openingRunRound3 = $this->getReference('opening-run-round:3');
        $taxScheme = $this->getReference('tax-scheme:1');

        $openingRunRounds = [
            $openingRunRound1,
            $openingRunRound2,
            $openingRunRound3
        ];

        foreach ($openingRunRounds as $openingRunRound) {
            $odd = (new MatkaOdd())
                ->setRunRound($openingRunRound)
                ->setOdd($odd2)
                ->setPreset($preset)
                ->setOddValue(2.3)
                ->setIsEnabled(true)
                ->setProbability(0.123)
                ->setStatus('won')
                ->setType('bazaar')
            ;
            $manager->persist($odd);

            $odd = (new MatkaOdd())
                ->setRunRound($openingRunRound)
                ->setOdd($odd3)
                ->setPreset($preset)
                ->setOddValue(7.3)
                ->setIsEnabled(true)
                ->setProbability(0.234)
                ->setStatus('lost')
                ->setType('bazaar')
            ;
            $manager->persist($odd);

            $odd = (new MatkaOdd())
                ->setRunRound($openingRunRound)
                ->setOdd($odd4)
                ->setPreset($preset)
                ->setOddValue(1.3)
                ->setIsEnabled(true)
                ->setProbability(0.456)
                ->setStatus('active')
                ->setType('bazaar')
            ;
            $manager->persist($odd);

            $odd = (new MatkaOdd())
                ->setRunRound($openingRunRound)
                ->setOdd($odd5)
                ->setPreset($preset)
                ->setOddValue(4.3)
                ->setIsEnabled(true)
                ->setProbability(0.842)
                ->setStatus('active')
                ->setType('bazaar')
            ;
            $manager->persist($odd);

            $odd = (new MatkaOdd())
                ->setRunRound($openingRunRound)
                ->setOdd($odd6)
                ->setPreset($preset)
                ->setOddValue(2.3)
                ->setIsEnabled(true)
                ->setProbability(0.345)
                ->setStatus('active')
                ->setType('classic')
            ;
            $manager->persist($odd);

            $odd = (new MatkaOdd())
                ->setRunRound($openingRunRound)
                ->setOdd($odd6)
                ->setPreset($preset)
                ->setOddValue(2.3)
                ->setIsEnabled(true)
                ->setProbability(0.345)
                ->setStatus('active')
                ->setType('bazaar')
            ;
            $manager->persist($odd);

            $odd = (new MatkaOdd())
                ->setRunRound($openingRunRound)
                ->setOdd($odd7)
                ->setPreset($preset)
                ->setOddValue(3.0)
                ->setIsEnabled(true)
                ->setProbability(0.653)
                ->setStatus('active')
                ->setType('bazaar')
            ;
            $manager->persist($odd);

            $odd = (new MatkaOdd())
                ->setRunRound($openingRunRound)
                ->setOdd($odd8)
                ->setPreset($preset)
                ->setOddValue(2.8)
                ->setIsEnabled(true)
                ->setProbability(0.213)
                ->setStatus('active')
                ->setType('bazaar')
            ;
            $manager->persist($odd);

            $odd = (new MatkaOdd())
                ->setRunRound($openingRunRound)
                ->setOdd($odd9)
                ->setPreset($preset)
                ->setOddValue(5.8)
                ->setIsEnabled(true)
                ->setProbability(0.998)
                ->setStatus('active')
                ->setType('bazaar')
            ;
            $manager->persist($odd);

            $odd = (new MatkaOdd())
                ->setRunRound($openingRunRound)
                ->setOdd($odd10)
                ->setPreset($preset)
                ->setOddValue(1.8)
                ->setIsEnabled(true)
                ->setProbability(0.111)
                ->setStatus('active')
                ->setType('bazaar')
            ;
            $manager->persist($odd);

            $odd = (new MatkaOdd())
                ->setRunRound($openingRunRound)
                ->setOdd($odd11)
                ->setPreset($preset)
                ->setOddValue(17.3)
                ->setIsEnabled(true)
                ->setProbability(3.121)
                ->setStatus('active')
                ->setType('bazaar')
            ;
            $manager->persist($odd);

            $odd = (new MatkaOdd())
                ->setRunRound($openingRunRound)
                ->setOdd($odd12)
                ->setPreset($preset)
                ->setOddValue(23.3)
                ->setIsEnabled(true)
                ->setProbability(23.23)
                ->setStatus('active')
                ->setType('bazaar')
            ;
            $manager->persist($odd);
        }

        $odd = (new MatkaOdd())
            ->setRunRound($openingRunRound2)
            ->setOdd($odd2)
            ->setPreset($preset)
            ->setOddValue(2.3)
            ->setIsEnabled(true)
            ->setProbability(0.123)
            ->setStatus('won')
            ->setTaxScheme($taxScheme)
            ->setType('bazaar')
        ;
        $manager->persist($odd);

        $odd = (new MatkaOdd())
            ->setRunRound($openingRunRound2)
            ->setOdd($odd3)
            ->setPreset($preset)
            ->setOddValue(7.3)
            ->setIsEnabled(true)
            ->setProbability(0.234)
            ->setStatus('lost')
            ->setTaxScheme($taxScheme)
            ->setType('bazaar')
        ;
        $manager->persist($odd);

        $odd = (new MatkaOdd())
            ->setRunRound($openingRunRound2)
            ->setOdd($odd4)
            ->setPreset($preset)
            ->setOddValue(1.3)
            ->setIsEnabled(true)
            ->setProbability(0.456)
            ->setStatus('active')
            ->setTaxScheme($taxScheme)
            ->setType('bazaar')
        ;
        $manager->persist($odd);

        $odd = (new MatkaOdd())
            ->setRunRound($openingRunRound2)
            ->setOdd($odd5)
            ->setPreset($preset)
            ->setOddValue(4.3)
            ->setIsEnabled(true)
            ->setProbability(0.842)
            ->setStatus('active')
            ->setTaxScheme($taxScheme)
            ->setType('bazaar')
        ;
        $manager->persist($odd);

        $manager->flush();
    }
}
