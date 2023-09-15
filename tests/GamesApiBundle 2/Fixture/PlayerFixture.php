<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture;

use Acme\SymfonyDb\Entity\Player;
use Doctrine\Persistence\ObjectManager;
use SymfonyTests\Unit\CoreBundle\Fixture\AbstractCustomizableFixture;

/**
 * Class PlayerFixture
 */
class PlayerFixture extends AbstractCustomizableFixture
{
    /**
     * @var array
     */
    protected array $tables = [
        Player::class,
    ];

    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $partner = $this->getReference('partner:1');

        $player = (new Player())->setPartner($partner)
            ->setCurrency($partner->getCurrency())
            ->setExternalCode('externalCode1')
            ->setExternalToken('externalToken1')
            ->setIsShop(false)
            ->setIsTest(false)
            ->setIsFreePlay(false)
            ->setCreatedAt(new \DateTime())
            ->setTag('existing')
            ->setTaggedAt(new \DateTimeImmutable())
        ;

        $manager->persist($player);
        $manager->flush();

        $this->addReference('player:1', $player);
    }
}
