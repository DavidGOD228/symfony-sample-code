<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification;

use Acme\SymfonyDb\Entity\Player;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;

/**
 * Class PlayerFixture
 */
class PlayerFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $partner1 = $this->getReference('partner1:gamification');
        $partner2 = $this->getReference('partner2:gamification');
        $currency = $this->getReference('currency:eur');

        $player1 = (new Player())
            ->setPartner($partner1)
            ->setCurrency($currency)
            ->setExternalCode('player1')
            ->setIsShop(false)
            ->setIsTest(false)
            ->setIsFreePlay(false)
            ->setCreatedAt(new DateTime())
            ->setTag('existing')
            ->setTaggedAt(new DateTimeImmutable())
        ;
        $manager->persist($player1);

        $player2 = (new Player())
            ->setPartner($partner2)
            ->setCurrency($currency)
            ->setExternalCode('player2')
            ->setIsShop(false)
            ->setIsTest(false)
            ->setIsFreePlay(false)
            ->setCreatedAt(new DateTime())
            ->setTag('existing')
            ->setTaggedAt(new DateTimeImmutable())
        ;
        $manager->persist($player2);

        $player3 = (new Player())
            ->setPartner($partner2)
            ->setCurrency($currency)
            ->setExternalCode('player3')
            ->setIsShop(false)
            ->setIsTest(false)
            ->setIsFreePlay(false)
            ->setCreatedAt(new DateTime())
            ->setTag('existing')
            ->setTaggedAt(new DateTimeImmutable())
        ;
        $manager->persist($player3);

        $player4 = (new Player())
            ->setPartner($partner2)
            ->setCurrency($currency)
            ->setExternalCode('player4')
            ->setIsShop(false)
            ->setIsTest(false)
            ->setIsFreePlay(false)
            ->setCreatedAt(new DateTime())
            ->setTag('existing')
            ->setTaggedAt(new DateTimeImmutable())
        ;
        $manager->persist($player4);

        $manager->flush();

        $this->addReference('player1:gamification', $player1);
        $this->addReference('player2:gamification', $player2);
        $this->addReference('player3:gamification', $player3);
        $this->addReference('player4:gamification', $player4);
    }
}
