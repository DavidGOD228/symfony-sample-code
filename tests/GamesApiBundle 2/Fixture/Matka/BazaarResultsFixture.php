<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Matka;

use Acme\SymfonyDb\Entity\BazaarRun;
use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\MatkaCard;
use Acme\SymfonyDb\Entity\MatkaRunCard;
use Acme\SymfonyDb\Entity\MatkaRunRound;
use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class BazaarResultsFixture
 */
final class BazaarResultsFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->createMilanMorningToday($manager);
        $this->createTaraTwilightYesterday($manager);
        $this->createRubyMidnightYesterday($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    private function createMilanMorningToday(ObjectManager $manager): void
    {
        $openingTime = new CarbonImmutable('2021-09-06 05:00:00');

        $openingRunRound = $this->getDefaultRunRound()
            ->setTime($openingTime)
        ;

        $card1 = $this->getRunCard('hearts', '2', $openingRunRound);
        $card2 = $this->getRunCard('diamonds', 'a', $openingRunRound);
        $card3 = $this->getRunCard('spades', '8', $openingRunRound);

        $openingRun = $this->getDefaultGameRun()
            ->setCode('milan-morning-today-open')
            ->setTime($openingTime)
            ->setMatkaRunRound($openingRunRound)
            ->addMatkaRunCard($card1)
            ->addMatkaRunCard($card2)
            ->addMatkaRunCard($card3)
            ->setIsReturned(true)
        ;
        $this->addReference('matka:run:milan-morning-today-open', $openingRun);
        $manager->persist($openingRun);

        $closingTime = new CarbonImmutable('2021-09-06 06:30:00');

        $closingRunRound = $this->getDefaultRunRound()
            ->setTime($closingTime)
        ;

        $card4 = $this->getRunCard('clubs', '5', $closingRunRound);
        $card5 = $this->getRunCard('diamonds', '5', $closingRunRound);
        $card6 = $this->getRunCard('spades', '7', $closingRunRound);

        $closingRun = $this->getDefaultGameRun()
            ->setCode('milan-morning-today-close')
            ->setTime($closingTime)
            ->setMatkaRunRound($closingRunRound)
            ->addMatkaRunCard($card4)
            ->addMatkaRunCard($card5)
            ->addMatkaRunCard($card6)
        ;
        $manager->persist($closingRun);

        $bazaarRun = (new BazaarRun)
            ->setTitle('Milan Morning')
            ->setCode('milan-morning-today-open:milan-morning-today-close')
            ->setIsReturned(false)
            ->setOpeningRun($openingRun)
            ->setClosingRun($closingRun)
        ;
        $manager->persist($bazaarRun);

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    private function createTaraTwilightYesterday(ObjectManager $manager): void
    {
        $openingTime = new CarbonImmutable('2021-09-06 12:30:00');

        $openingRunRound = $this->getDefaultRunRound()
            ->setTime($openingTime)
        ;

        $card1 = $this->getRunCard('hearts', '7', $openingRunRound);
        $card2 = $this->getRunCard('spades', '9', $openingRunRound);
        $card3 = $this->getRunCard('clubs', '2', $openingRunRound);

        $openingRun = $this->getDefaultGameRun()
            ->setCode('tara-twilight-yesterday-open')
            ->setTime($openingTime)
            ->setMatkaRunRound($openingRunRound)
            ->addMatkaRunCard($card1)
            ->addMatkaRunCard($card2)
            ->addMatkaRunCard($card3)
        ;
        $manager->persist($openingRun);

        $closingTime = new CarbonImmutable('2021-09-06 13:30:00');

        $closingRunRound = $this->getDefaultRunRound()
            ->setTime($closingTime)
        ;

        $card4 = $this->getRunCard('clubs', '2', $closingRunRound);
        $card5 = $this->getRunCard('hearts', '5', $closingRunRound);
        $card6 = $this->getRunCard('spades', '6', $closingRunRound);

        $closingRun = $this->getDefaultGameRun()
            ->setCode('tara-twilight-yesterday-close')
            ->setTime($closingTime)
            ->setMatkaRunRound($closingRunRound)
            ->addMatkaRunCard($card4)
            ->addMatkaRunCard($card5)
            ->addMatkaRunCard($card6)
        ;
        $manager->persist($closingRun);

        $bazaarRun = (new BazaarRun)
            ->setTitle('Tara Twilight')
            ->setCode('tara-twilight-yesterday-open:tara-twilight-yesterday-close')
            ->setIsReturned(false)
            ->setOpeningRun($openingRun)
            ->setClosingRun($closingRun)
        ;
        $manager->persist($bazaarRun);

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    private function createRubyMidnightYesterday(ObjectManager $manager): void
    {
        $openingTime = new CarbonImmutable('2021-09-05 16:30:00');

        $openingRunRound = $this->getDefaultRunRound()
            ->setTime($openingTime)
        ;

        $card1 = $this->getRunCard('hearts', '2', $openingRunRound);
        $card2 = $this->getRunCard('spades', '2', $openingRunRound);
        $card3 = $this->getRunCard('diamonds', '2', $openingRunRound);

        $openingRun = $this->getDefaultGameRun()
            ->setCode('ruby-midnight-yesterday-open')
            ->setTime($openingTime)
            ->setMatkaRunRound($openingRunRound)
            ->addMatkaRunCard($card1)
            ->addMatkaRunCard($card2)
            ->addMatkaRunCard($card3)
        ;
        $manager->persist($openingRun);

        $closingTime = new CarbonImmutable('2021-09-05 18:00:00');

        $closingRunRound = $this->getDefaultRunRound()
            ->setTime($closingTime)
        ;

        $card4 = $this->getRunCard('clubs', 'a', $closingRunRound);
        $card5 = $this->getRunCard('diamonds', '3', $closingRunRound);
        $card6 = $this->getRunCard('spades', '10', $closingRunRound);

        $closingRun = $this->getDefaultGameRun()
            ->setCode('ruby-midnight-yesterday-close')
            ->setTime($closingTime)
            ->setMatkaRunRound($closingRunRound)
            ->addMatkaRunCard($card4)
            ->addMatkaRunCard($card5)
            ->addMatkaRunCard($card6)
        ;
        $manager->persist($closingRun);

        $bazaarRun = (new BazaarRun)
            ->setTitle('Ruby Midnight')
            ->setCode('ruby-midnight-yesterday-open:ruby-midnight-yesterday-close')
            ->setIsReturned(false)
            ->setOpeningRun($openingRun)
            ->setClosingRun($closingRun)
        ;
        $manager->persist($bazaarRun);

        $manager->flush();
    }

    /**
     * @return GameRun
     */
    private function getDefaultGameRun(): GameRun
    {
        /** @var Game $game */
        $game = $this->getReference('game:18');

        return (new GameRun)
            ->setGame($game)
            ->setIsImported(true)
            ->setIsReturned(false)
            ->setResultsEntered(true)
            ->setVideoConfirmationRequired(false)
            ->setPublishedDate(new CarbonImmutable('2021-08-30 03:00:00'))
        ;
    }

    /**
     * @return MatkaRunRound
     */
    private function getDefaultRunRound(): MatkaRunRound
    {
        $matkaRunRound = new MatkaRunRound();

        $matkaRunRound
            ->setTime(CarbonImmutable::now())
            ->setResultsEntered(true)
            ->setIsActive(false)
            ->setRoundNumber(1)
        ;

        return $matkaRunRound;
    }

    /**
     * @param string $suit
     * @param string $value
     * @param MatkaRunRound $matkaRunRound
     *
     * @return MatkaRunCard
     */
    private function getRunCard(string $suit, string $value, MatkaRunRound $matkaRunRound): MatkaRunCard
    {
        /** @var MatkaCard $card */
        $card = $this->getReference("matka_card:$suit:$value");

        return (new MatkaRunCard())
            ->setCard($card)
            ->setIsConfirmed(true)
            ->setRunRound($matkaRunRound)
            ->setEnteredAt(CarbonImmutable::now())
        ;
    }
}
