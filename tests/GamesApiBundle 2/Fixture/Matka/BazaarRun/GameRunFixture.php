<?php

declare(strict_types = 1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Matka\BazaarRun;

use Acme\SymfonyDb\Entity\BazaarRun;
use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\MatkaCard;
use Acme\SymfonyDb\Entity\MatkaRunCard;
use Acme\SymfonyDb\Entity\MatkaRunRound;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class LotteryRunFixture
 */
final class GameRunFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        /** @var Game $game */
        $game = $this->getReference('game:18');

        /* @var MatkaRunRound $openingRunRound2 */
        $openingRunRound2 = (new MatkaRunRound())
            ->setTime(new CarbonImmutable('2021-09-06 15:00:00'))
            ->setResultsEntered(true)
            ->setIsActive(false)
            ->setRoundNumber(1)
        ;
        $manager->persist($openingRunRound2);

        /* @var MatkaRunRound $closingRunRound2 */
        $closingRunRound2 = (new MatkaRunRound())
            ->setTime(new CarbonImmutable('2021-09-06 16:00:00'))
            ->setResultsEntered(true)
            ->setIsActive(false)
            ->setRoundNumber(1)
        ;
        $manager->persist($closingRunRound2);

        /* @var MatkaRunRound $openingRunRound3 */
        $openingRunRound3 = (new MatkaRunRound())
            ->setTime(new CarbonImmutable('2021-09-06 17:00:00'))
            ->setResultsEntered(true)
            ->setIsActive(false)
            ->setRoundNumber(1)
        ;
        $manager->persist($openingRunRound3);

        /* @var MatkaRunRound $closingRunRound3 */
        $closingRunRound3 = (new MatkaRunRound())
            ->setTime(new CarbonImmutable('2021-09-06 18:00:00'))
            ->setResultsEntered(true)
            ->setIsActive(false)
            ->setRoundNumber(1)
        ;
        $manager->persist($closingRunRound3);

        /* @var MatkaRunRound $openingRunRound4 */
        $openingRunRound4 = (new MatkaRunRound())
            ->setTime(new CarbonImmutable('2021-09-06 19:00:00'))
            ->setResultsEntered(true)
            ->setIsActive(false)
            ->setRoundNumber(1)
        ;
        $manager->persist($openingRunRound4);

        /* @var MatkaRunRound $closingRunRound4 */
        $closingRunRound4 = (new MatkaRunRound())
            ->setTime(new CarbonImmutable('2021-09-06 20:00:00'))
            ->setResultsEntered(true)
            ->setIsActive(false)
            ->setRoundNumber(1)
        ;
        $manager->persist($closingRunRound4);

        $card1 = $this->getRunCard('hearts', '3', $openingRunRound2);
        $card2 = $this->getRunCard('spades', '3', $openingRunRound2);
        $card3 = $this->getRunCard('diamonds', '3', $openingRunRound2);
        $card4 = $this->getRunCard('clubs', '5', $closingRunRound3);
        $card5 = $this->getRunCard('hearts', '5', $closingRunRound3);
        $card6 = $this->getRunCard('spades', '7', $closingRunRound3);

        $openingRun1 = (new GameRun())
            ->setCode('opening-1')
            ->setIsReturned(false)
            ->setTime(new DateTimeImmutable('2021-09-04 12:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setResultsEntered(true)
            ->setVideoConfirmationRequired(false)
            ->setIsImported(true)
            ->setGame($game)
        ;

        $closingRun1 = (new GameRun())
            ->setCode('closing-1')
            ->setIsReturned(true)
            ->setTime(new DateTimeImmutable('2021-09-04 13:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setResultsEntered(true)
            ->setVideoConfirmationRequired(false)
            ->setIsImported(true)
            ->setGame($game)
        ;

        $openingRun2 = (new GameRun())
            ->setCode('opening-2')
            ->setIsReturned(false)
            ->setTime(new DateTimeImmutable('2021-09-05 8:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setResultsEntered(true)
            ->setVideoConfirmationRequired(false)
            ->setIsImported(true)
            ->setGame($game)
            ->setMatkaRunRound($openingRunRound2)
            ->addMatkaRunCard($card1)
            ->addMatkaRunCard($card2)
            ->addMatkaRunCard($card3)
        ;

        $closingRun2 = (new GameRun())
            ->setCode('closing-2')
            ->setIsReturned(false)
            ->setTime(new DateTimeImmutable('2021-09-05 9:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setResultsEntered(true)
            ->setVideoConfirmationRequired(false)
            ->setIsImported(true)
            ->setGame($game)
            ->setMatkaRunRound($closingRunRound2)
            ->addMatkaRunCard($card4)
            ->addMatkaRunCard($card5)
            ->addMatkaRunCard($card6)
        ;

        $openingRun3 = (new GameRun())
            ->setCode('opening-3')
            ->setIsReturned(false)
            ->setTime(new DateTimeImmutable('2021-09-05 12:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setResultsEntered(false)
            ->setVideoConfirmationRequired(false)
            ->setIsImported(true)
            ->setGame($game)
            ->setIsReturned(true)
            ->setMatkaRunRound($openingRunRound3)
        ;

        $closingRun3 = (new GameRun())
            ->setCode('closing-3')
            ->setIsReturned(false)
            ->setTime(new DateTimeImmutable('2021-09-05 13:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setResultsEntered(false)
            ->setVideoConfirmationRequired(false)
            ->setIsImported(true)
            ->setGame($game)
            ->setMatkaRunRound($closingRunRound3)
        ;

        $openingRun4 = (new GameRun())
            ->setCode('opening-4')
            ->setIsReturned(false)
            ->setTime(new DateTimeImmutable('2021-09-06 09:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setResultsEntered(false)
            ->setVideoConfirmationRequired(false)
            ->setIsImported(true)
            ->setGame($game)
            ->setMatkaRunRound($openingRunRound4)
        ;

        $closingRun4 = (new GameRun())
            ->setCode('closing-4')
            ->setIsReturned(false)
            ->setTime(new DateTimeImmutable('2021-09-06 10:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setResultsEntered(false)
            ->setVideoConfirmationRequired(false)
            ->setIsImported(true)
            ->setGame($game)
            ->setMatkaRunRound($closingRunRound4)
        ;

        $openingRun5 = (new GameRun())
            ->setCode('opening-5')
            ->setIsReturned(false)
            ->setTime(new DateTimeImmutable('2021-09-06 12:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setResultsEntered(false)
            ->setVideoConfirmationRequired(false)
            ->setIsImported(true)
            ->setGame($game)
        ;

        $closingRun5 = (new GameRun())
            ->setCode('closing-5')
            ->setIsReturned(true)
            ->setTime(new DateTimeImmutable('2021-09-06 13:00:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setResultsEntered(false)
            ->setVideoConfirmationRequired(false)
            ->setIsImported(true)
            ->setGame($game)
        ;

        $openingRun6 = (new GameRun())
            ->setCode('opening-6')
            ->setIsReturned(false)
            ->setTime(new DateTimeImmutable('2021-09-06 11:30:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setResultsEntered(false)
            ->setVideoConfirmationRequired(false)
            ->setIsImported(true)
            ->setGame($game)
        ;

        $closingRun6 = (new GameRun())
            ->setCode('closing-6')
            ->setIsReturned(false)
            ->setTime(new DateTimeImmutable('2021-09-06 12:30:00'))
            ->setPublishedDate(new DateTimeImmutable())
            ->setResultsEntered(false)
            ->setVideoConfirmationRequired(false)
            ->setIsImported(true)
            ->setGame($game)
        ;

        $bazaarRun1 = (new BazaarRun())
            ->setCode('opening-1:closing-1')
            ->setTitle('Madhur Matinee')
            ->setOpeningRun($openingRun1)
            ->setClosingRun($closingRun1)
            ->setIsReturned(false)
        ;

        $bazaarRun2 = (new BazaarRun())
            ->setCode('opening-2:closing-2')
            ->setTitle('Tara Twilight')
            ->setOpeningRun($openingRun2)
            ->setClosingRun($closingRun2)
            ->setIsReturned(false)
        ;

        $bazaarRun3 = (new BazaarRun())
            ->setCode('opening-3:closing-3')
            ->setTitle('Navratna Night')
            ->setOpeningRun($openingRun3)
            ->setClosingRun($closingRun3)
            ->setIsReturned(true)
        ;

        $bazaarRun4 = (new BazaarRun())
            ->setCode('opening-4:closing-4')
            ->setTitle('Ruby Midnight')
            ->setOpeningRun($openingRun4)
            ->setClosingRun($closingRun4)
            ->setIsReturned(false)
        ;

        $bazaarRun5 = (new BazaarRun())
            ->setCode('opening-5:closing-5')
            ->setTitle('Starline Sunset')
            ->setOpeningRun($openingRun5)
            ->setClosingRun($closingRun5)
            ->setIsReturned(false)
        ;

        $bazaarRun6 = (new BazaarRun())
            ->setCode('opening-6:closing-6')
            ->setTitle('Madhur evening')
            ->setOpeningRun($openingRun6)
            ->setClosingRun($closingRun6)
            ->setIsReturned(false)
        ;
        $manager->persist($bazaarRun1);
        $manager->persist($bazaarRun2);
        $manager->persist($bazaarRun3);
        $manager->persist($bazaarRun4);
        $manager->persist($bazaarRun5);
        $manager->persist($bazaarRun6);

        $openingRun1->setBazaarRun($bazaarRun1);
        $closingRun1->setBazaarRun($bazaarRun1);
        $openingRun2->setBazaarRun($bazaarRun2);
        $closingRun2->setBazaarRun($bazaarRun2);
        $openingRun3->setBazaarRun($bazaarRun3);
        $closingRun3->setBazaarRun($bazaarRun3);
        $openingRun4->setBazaarRun($bazaarRun4);
        $closingRun4->setBazaarRun($bazaarRun4);
        $openingRun5->setBazaarRun($bazaarRun5);
        $closingRun5->setBazaarRun($bazaarRun5);
        $openingRun6->setBazaarRun($bazaarRun6);
        $closingRun6->setBazaarRun($bazaarRun6);

        $manager->persist($openingRun1);
        $manager->persist($closingRun1);
        $manager->persist($openingRun2);
        $manager->persist($closingRun2);
        $manager->persist($openingRun3);
        $manager->persist($closingRun3);
        $manager->persist($openingRun4);
        $manager->persist($closingRun4);
        $manager->persist($openingRun5);
        $manager->persist($closingRun5);
        $manager->persist($openingRun6);
        $manager->persist($closingRun6);

        $manager->flush();

        $this->addReference('opening-run-round:1', $openingRunRound2);
        $this->addReference('closing-run-round:1', $closingRunRound2);
        $this->addReference('opening-run-round:2', $openingRunRound3);
        $this->addReference('closing-run-round:2', $closingRunRound3);
        $this->addReference('opening-run-round:3', $openingRunRound4);
        $this->addReference('closing-run-round:3', $closingRunRound4);
        $this->addReference('opening-run:1', $openingRun1);
        $this->addReference('closing-run:1', $closingRun1);
        $this->addReference('opening-run:2', $openingRun2);
        $this->addReference('closing-run:2', $closingRun2);
        $this->addReference('opening-run:3', $openingRun3);
        $this->addReference('closing-run:3', $closingRun3);
        $this->addReference('opening-run:4', $openingRun4);
        $this->addReference('closing-run:4', $closingRun4);
        $this->addReference('opening-run:5', $openingRun5);
        $this->addReference('closing-run:5', $closingRun5);
        $this->addReference('opening-run:6', $openingRun6);
        $this->addReference('closing-run:6', $closingRun6);
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
