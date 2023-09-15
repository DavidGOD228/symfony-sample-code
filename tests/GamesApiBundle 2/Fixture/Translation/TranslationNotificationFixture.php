<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Translation;

use Acme\SymfonyDb\Entity\TranslationNotification;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class TranslationNotificationFixture
 */
class TranslationNotificationFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $notification1 = (new TranslationNotification())
            ->setName('some1');
        $notification2 = (new TranslationNotification())
            ->setName('some2');

        $manager->persist($notification1);
        $manager->persist($notification2);

        $manager->flush();
    }
}
