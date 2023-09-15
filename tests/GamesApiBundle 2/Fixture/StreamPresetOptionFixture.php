<?php


namespace SymfonyTests\Unit\GamesApiBundle\Fixture;

use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\StreamPreset;
use Acme\SymfonyDb\Entity\StreamPresetOption;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class StreamPresetOptionFixture
 */
class StreamPresetOptionFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var StreamPreset $streamPreset */
        $streamPreset = $this->getReference('stream_preset:default');
        /** @var Game $game1 */
        $game1 = $this->getReference('game:1');
        $source1 = (string) $game1->getId();
        /** @var Game $game3 */
        $game3 = $this->getReference('game:3');
        $source3 = (string) $game3->getId();
        /** @var Game $game7 */
        $game7 = $this->getReference('game:7');
        $source7 = (string) $game7->getId();
        /** @var Game $gameMatka */
        $gameMatka = $this->getReference('game:18');
        $sourceMatka = (string) $gameMatka->getId();

        $option = (new StreamPresetOption())
            ->setProviderId(8)
            ->setPlatform('iframe')
            ->setSource($source1)
            ->setStreamPreset($streamPreset);
        $manager->persist($option);

        $option = (new StreamPresetOption())
            ->setProviderId(8)
            ->setPlatform('iframe')
            ->setSource($source3)
            ->setStreamPreset($streamPreset);
        $manager->persist($option);

        $option = (new StreamPresetOption())
            ->setProviderId(8)
            ->setPlatform('iframe')
            ->setSource($source7)
            ->setStreamPreset($streamPreset);
        $manager->persist($option);

        $option = (new StreamPresetOption())
            ->setProviderId(8)
            ->setPlatform('iframe')
            ->setSource($sourceMatka)
            ->setStreamPreset($streamPreset);
        $manager->persist($option);

        $manager->flush();
    }
}
