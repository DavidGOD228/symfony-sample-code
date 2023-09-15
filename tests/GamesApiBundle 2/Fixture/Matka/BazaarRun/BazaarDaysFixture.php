<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Matka\BazaarRun;

use Acme\SymfonyDb\Entity\BazaarRun;
use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\MatkaCard;
use Acme\SymfonyDb\Entity\MatkaOdd;
use Acme\SymfonyDb\Entity\MatkaRunCard;
use Acme\SymfonyDb\Entity\MatkaRunRound;
use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyDb\Entity\OddPreset;
use Acme\SymfonyDb\Entity\TaxScheme;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class NewGameRunFixture
 */
final class BazaarDaysFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     */
    public function load(ObjectManager $manager)
    {
        /** @var Game $game */
        $game = $this->getReference('game:18');
        $oddsByClass = $this->getOddsByClass();
        /** @var OddPreset $preset */
        $preset = $this->getReference('oddsPreset:18:1');
        /** @var TaxScheme[] $taxSchemes */
        $taxSchemes = [$this->getReference('tax-scheme:1'), null];

        foreach ($this->generate() as $fixture) {
            /** @var MatkaRunRound $openingRound */
            $openingRound = (new MatkaRunRound())
                ->setTime($fixture['open']['time'])
                ->setResultsEntered($fixture['open']['resultsEntered'])
                ->setIsActive($fixture['open']['active'])
                ->setRoundNumber(1);

            /** @var MatkaRunRound $closingRound */
            $closingRound = (new MatkaRunRound())
                ->setTime($fixture['close']['time'])
                ->setResultsEntered($fixture['close']['resultsEntered'])
                ->setIsActive($fixture['close']['active'])
                ->setRoundNumber(1);

            $card1 = $this->getRunCard($fixture['cards'][0]['suit'], $fixture['cards'][0]['value'], $openingRound);
            $card2 = $this->getRunCard($fixture['cards'][1]['suit'], $fixture['cards'][1]['value'], $openingRound);
            $card3 = $this->getRunCard($fixture['cards'][2]['suit'], $fixture['cards'][2]['value'], $openingRound);
            $card4 = $this->getRunCard($fixture['cards'][3]['suit'], $fixture['cards'][3]['value'], $openingRound);
            $card5 = $this->getRunCard($fixture['cards'][4]['suit'], $fixture['cards'][4]['value'], $openingRound);
            $card6 = $this->getRunCard($fixture['cards'][5]['suit'], $fixture['cards'][5]['value'], $openingRound);

            $openingRun = (new GameRun())
                ->setCode($fixture['open']['code'])
                ->setIsReturned($fixture['open']['returned'])
                ->setTime($fixture['open']['time'])
                ->setPublishedDate(new DateTimeImmutable())
                ->setResultsEntered($fixture['open']['resultsEntered'])
                ->setVideoConfirmationRequired(false)
                ->setIsImported(true)
                ->setGame($game)
                ->setMatkaRunRound($openingRound)
                ->addMatkaRunCard($card1)
                ->addMatkaRunCard($card2)
                ->addMatkaRunCard($card3);

            $closingRun = (new GameRun())
                ->setCode($fixture['close']['code'])
                ->setIsReturned($fixture['close']['returned'])
                ->setTime($fixture['close']['time'])
                ->setPublishedDate(new DateTimeImmutable())
                ->setResultsEntered($fixture['close']['resultsEntered'])
                ->setVideoConfirmationRequired(false)
                ->setIsImported(true)
                ->setGame($game)
                ->setMatkaRunRound($closingRound)
                ->addMatkaRunCard($card4)
                ->addMatkaRunCard($card5)
                ->addMatkaRunCard($card6);

            $bazaarRun = (new BazaarRun())
                ->setCode($fixture['open']['code'] . ':' . $fixture['close']['code'])
                ->setTitle($fixture['title'])
                ->setOpeningRun($openingRun)
                ->setClosingRun($closingRun)
                ->setIsReturned($fixture['returned']);
            $manager->persist($bazaarRun);

            $openingRun->setBazaarRun($bazaarRun);
            $manager->persist($openingRun);

            $closingRun->setBazaarRun($bazaarRun);
            $manager->persist($closingRun);

            foreach ($fixture['odds'] as $odd) {
                foreach ($taxSchemes as $taxScheme) {
                    foreach ([$openingRound, $closingRound] as $runRound) {
                        $matkaOdd = (new MatkaOdd())
                            ->setRunRound($runRound)
                            ->setOdd($oddsByClass[$odd['class']])
                            ->setPreset($preset)
                            ->setOddValue($odd['value'])
                            ->setIsEnabled(true)
                            ->setProbability($odd['probability'])
                            ->setStatus($odd['status'])
                            ->setTaxScheme($taxScheme)
                            ->setType('bazaar')
                        ;
                        $runRound->addOdd($matkaOdd);
                        $manager->persist($runRound);
                    }
                }
            }
        }

        $manager->flush();
    }

    /**
     * @param string $suit
     * @param string $value
     * @param MatkaRunRound $runRound
     *
     * @return MatkaRunCard
     */
    private function getRunCard(string $suit, string $value, MatkaRunRound $runRound): MatkaRunCard
    {
        /** @var MatkaCard $card */
        $card = $this->getReference("matka_card:$suit:$value");

        return (new MatkaRunCard())
            ->setCard($card)
            ->setIsConfirmed(true)
            ->setRunRound($runRound)
            ->setEnteredAt(CarbonImmutable::now())
            ;
    }

    /**
     * @return array<string, Odd>
     */
    private function getOddsByClass(): array
    {
        /** @var Odd[] $odds */
        $odds = [
            $this->getReference('bazaar-odd:2'),
            $this->getReference('bazaar-odd:3'),
            $this->getReference('bazaar-odd:4'),
            $this->getReference('bazaar-odd:5'),
            $this->getReference('bazaar-odd:6'),
            $this->getReference('bazaar-odd:7'),
            $this->getReference('bazaar-odd:8'),
            $this->getReference('bazaar-odd:9'),
            $this->getReference('bazaar-odd:10'),
            $this->getReference('bazaar-odd:11'),
            $this->getReference('bazaar-odd:12'),
        ];

        $map = [];
        foreach ($odds as $odd) {
            $map[$odd->getClass()] = $odd;
        }

        return $map;
    }

    /**
     * @return iterable
     */
    private function generate(): iterable
    {
        $cards = [
            ['suit' => 'hearts', 'value' => '3'],
            ['suit' => 'spades', 'value' => '3'],
            ['suit' => 'diamonds', 'value' => '3'],
            ['suit' => 'clubs', 'value' => '5'],
            ['suit' => 'hearts', 'value' => '5'],
            ['suit' => 'spades', 'value' => '7']
        ];

        $odds = [
            [
                'class' => 'JODI',
                'status' => 'active',
                'probability' => 0.010000,
                'value' => 95.00
            ],
            [
                'class' => 'HALF_SANGAM_A',
                'status' => 'active',
                'probability' => 0.000648,
                'value' => 1400.00
            ],
            [
                'class' => 'HALF_SANGAM_B',
                'status' => 'active',
                'probability' => 0.000648,
                'value' => 1400.00
            ],
            [
                'class' => 'SANGAM',
                'status' => 'active',
                'probability' => 0.000042,
                'value' => 15000.00
            ],
        ];

        // yesterday
        yield from [
            [
                'title' => 'Milan Morning',
                'returned' => false,
                'open' => [
                    'code' => 'opening-1',
                    'time' => new DateTimeImmutable('2021-10-26 5:00:00'),
                    'returned' => false,
                    'resultsEntered' => true,
                    'active' => false,
                ],
                'close' => [
                    'code' => 'closing-1',
                    'time' => new DateTimeImmutable('2021-10-26 6:30:00'),
                    'returned' => false,
                    'resultsEntered' => true,
                    'active' => false,
                ],
                'cards' => $cards,
                'odds' => $odds,
            ],
            [
                'title' => 'Madhur Matinee',
                'returned' => false,
                'open' => [
                    'code' => 'opening-2',
                    'time' => new DateTimeImmutable('2021-10-26 7:00:00'),
                    'returned' => false,
                    'resultsEntered' => true,
                    'active' => false,
                ],
                'close' => [
                    'code' => 'closing-2',
                    'time' => new DateTimeImmutable('2021-10-26 8:00:00'),
                    'returned' => false,
                    'resultsEntered' => true,
                    'active' => false,
                ],
                'cards' => $cards,
                'odds' => $odds,
            ],
            [
                'title' => 'Starline Sunset',
                'returned' => false,
                'open' => [
                    'code' => 'opening-3',
                    'time' => new DateTimeImmutable('2021-10-26 10:30:00'),
                    'returned' => false,
                    'resultsEntered' => true,
                    'active' => false,
                ],
                'close' => [
                    'code' => 'closing-3',
                    'time' => new DateTimeImmutable('2021-10-26 11:30:00'),
                    'returned' => false,
                    'resultsEntered' => true,
                    'active' => false,
                ],
                'cards' => $cards,
                'odds' => $odds,
            ],
            [
                'title' => 'Tara Twilight',
                'returned' => false,
                'open' => [
                    'code' => 'opening-4',
                    'time' => new DateTimeImmutable('2021-10-26 12:30:00'),
                    'returned' => false,
                    'resultsEntered' => true,
                    'active' => false,
                ],
                'close' => [
                    'code' => 'closing-4',
                    'time' => new DateTimeImmutable('2021-10-26 13:30:00'),
                    'returned' => false,
                    'resultsEntered' => true,
                    'active' => false,
                ],
                'cards' => $cards,
                'odds' => $odds,
            ],
            [
                'title' => 'Navratna Night',
                'returned' => false,
                'open' => [
                    'code' => 'opening-5',
                    'time' => new DateTimeImmutable('2021-10-26 14:30:00'),
                    'returned' => false,
                    'resultsEntered' => true,
                    'active' => false,
                ],
                'close' => [
                    'code' => 'closing-5',
                    'time' => new DateTimeImmutable('2021-10-26 15:30:00'),
                    'returned' => false,
                    'resultsEntered' => true,
                    'active' => false,
                ],
                'cards' => $cards,
                'odds' => $odds,
            ],
            [
                'title' => 'Ruby Midnight',
                'returned' => false,
                'open' => [
                    'code' => 'opening-6',
                    'time' => new DateTimeImmutable('2021-10-26 16:30:00'),
                    'returned' => false,
                    'resultsEntered' => true,
                    'active' => false,
                ],
                'close' => [
                    'code' => 'closing-6',
                    'time' => new DateTimeImmutable('2021-10-26 18:00:00'),
                    'returned' => false,
                    'resultsEntered' => true,
                    'active' => false,
                ],
                'cards' => $cards,
                'odds' => $odds,
            ]
        ];

        // today
        yield from [
            [
                'title' => 'Milan Morning',
                'returned' => false,
                'open' => [
                    'code' => 'opening-7',
                    'time' => new DateTimeImmutable('2021-10-27 5:00:00'),
                    'returned' => false,
                    'resultsEntered' => true,
                    'active' => false,
                ],
                'close' => [
                    'code' => 'closing-7',
                    'time' => new DateTimeImmutable('2021-10-27 6:30:00'),
                    'returned' => false,
                    'resultsEntered' => true,
                    'active' => false,
                ],
                'cards' => $cards,
                'odds' => $odds,
            ],
            [
                'title' => 'Madhur Matinee',
                'returned' => false,
                'open' => [
                    'code' => 'opening-8',
                    'time' => new DateTimeImmutable('2021-10-27 7:00:00'),
                    'returned' => false,
                    'resultsEntered' => true,
                    'active' => false,
                ],
                'close' => [
                    'code' => 'closing-8',
                    'time' => new DateTimeImmutable('2021-10-27 8:00:00'),
                    'returned' => false,
                    'resultsEntered' => false,
                    'active' => true, // closing round is still active
                ],
                'cards' => $cards,
                'odds' => $odds,
            ],
            [
                'title' => 'Starline Sunset',
                'returned' => false,
                'open' => [
                    'code' => 'opening-9',
                    'time' => new DateTimeImmutable('2021-10-27 10:30:00'),
                    'returned' => false,
                    'resultsEntered' => false,
                    'active' => false,
                ],
                'close' => [
                    'code' => 'closing-9',
                    'time' => new DateTimeImmutable('2021-10-27 11:30:00'),
                    'returned' => false,
                    'resultsEntered' => false,
                    'active' => false,
                ],
                'cards' => $cards,
                'odds' => $odds,
            ],
            [
                'title' => 'Tara Twilight',
                'returned' => false,
                'open' => [
                    'code' => 'opening-10',
                    'time' => new DateTimeImmutable('2021-10-27 12:30:00'),
                    'returned' => false,
                    'resultsEntered' => false,
                    'active' => false,
                ],
                'close' => [
                    'code' => 'closing-10',
                    'time' => new DateTimeImmutable('2021-10-27 13:30:00'),
                    'returned' => false,
                    'resultsEntered' => false,
                    'active' => false,
                ],
                'cards' => $cards,
                'odds' => $odds,
            ],
            [
                'title' => 'Navratna Night',
                'returned' => true,
                'open' => [
                    'code' => 'opening-11',
                    'time' => new DateTimeImmutable('2021-10-27 14:30:00'),
                    'returned' => true,
                    'resultsEntered' => false,
                    'active' => false,
                ],
                'close' => [
                    'code' => 'closing-11',
                    'time' => new DateTimeImmutable('2021-10-27 15:30:00'),
                    'returned' => false,
                    'resultsEntered' => false,
                    'active' => false,
                ],
                'cards' => $cards,
                'odds' => $odds,
            ],
            [
                'title' => 'Ruby Midnight',
                'returned' => false,
                'open' => [
                    'code' => 'opening-12',
                    'time' => new DateTimeImmutable('2021-10-27 16:30:00'),
                    'returned' => false,
                    'resultsEntered' => false,
                    'active' => false,
                ],
                'close' => [
                    'code' => 'closing-12',
                    'time' => new DateTimeImmutable('2021-10-27 18:00:00'),
                    'returned' => false,
                    'resultsEntered' => false,
                    'active' => false,
                ],
                'cards' => $cards,
                'odds' => $odds
            ]
        ];
    }
}
