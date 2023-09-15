<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Gamification;

use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Entity\PlayerProfile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;

/**
 * Class PlayerProfileFixture
 */
class PlayerProfileFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        /** @var Player $player1 */
        $player1 = $this->getReference('player1:gamification');
        /** @var Player $player2 */
        $player2 = $this->getReference('player2:gamification');
        /** @var Player $player4 */
        $player4 = $this->getReference('player4:gamification');

        $profile1 = (new PlayerProfile())
            ->setPlayer($player1)
            ->setExternalId('U3453D17021621Q3279052K003000871')
            ->setName('profile1')
            ->setAvatarUrl('https://some-image.jpg')
            ->setBlocked(false)
        ;
        $manager->persist($profile1);

        $profile2 = (new PlayerProfile())
            ->setPlayer($player2)
            ->setExternalId('U3453D17021621Q3279052K003000872')
            ->setName('profile2')
            ->setAvatarUrl('https://some-image.jpg')
            ->setBlocked(false)
        ;
        $manager->persist($profile2);

        $profile3 = (new PlayerProfile())
            ->setPlayer($player4)
            ->setExternalId('U3453D17021621Q3279052K003000873')
            ->setName('profile3')
            ->setAvatarUrl('https://some-image.jpg')
            ->setBlocked(false)
        ;
        $manager->persist($profile3);

        $manager->flush();

        $this->addReference('profile1:gamification', $profile1);
        $this->addReference('profile2:gamification', $profile2);
        $this->addReference('profile3:gamification', $profile3);
    }
}
