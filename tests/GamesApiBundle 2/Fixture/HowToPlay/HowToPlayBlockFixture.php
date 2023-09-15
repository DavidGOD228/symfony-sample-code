<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\HowToPlay;

use Acme\SymfonyDb\Entity\HowToPlayBlock;
use Acme\SymfonyDb\Type\HowToPlayBlockType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class HowToPlayBlockFixture
 */
class HowToPlayBlockFixture extends Fixture
{
    private const BLOCKS = [
        [
            'id' => 201, 'reference' => 'general',
            'type' => HowToPlayBlockType::TYPE_GENERAL, 'isExpandable' => true
        ],
        [
            'id' => 202, 'reference' => 'rules',
            'type' => HowToPlayBlockType::TYPE_RULES, 'isExpandable' => true
        ],
        [
            'id' => 203, 'reference' => 'video',
            'type' => HowToPlayBlockType::TYPE_VIDEO, 'isExpandable' => false
        ],
        [
            'id' => 204, 'reference' => 'rtp',
            'type' => HowToPlayBlockType::TYPE_RTP, 'isExpandable' => true
        ],
        [
            'id' => 205, 'reference' => 'subscriptions',
            'type' => HowToPlayBlockType::TYPE_SUBSCRIPTIONS, 'isExpandable' => true
        ],
        [
            'id' => 206, 'reference' => 'combinations',
            'type' => HowToPlayBlockType::TYPE_COMBINATIONS, 'isExpandable' => true
        ],
        [
            'id' => 207, 'reference' => 'cashback',
            'type' => HowToPlayBlockType::TYPE_CASHBACK, 'isExpandable' => true
        ],
        [
            'id' => 208, 'reference' => 'jackpot-speedy7',
            'type' => HowToPlayBlockType::TYPE_JACKPOT, 'isExpandable' => true
        ],
        [
            'id' => 209, 'reference' => 'jackpot-headsup',
            'type' => HowToPlayBlockType::TYPE_JACKPOT, 'isExpandable' => true
        ],
        [
            'id' => 210, 'reference' => 'gamification',
            'type' => HowToPlayBlockType::TYPE_GAMIFICATION, 'isExpandable' => true
        ],
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach (self::BLOCKS as $block) {
            $howToPlayBlock = HowToPlayBlock::createFromId($block['id'])
                ->setType($block['type'])
                ->setIsExpandable($block['isExpandable']);
            $manager->persist($howToPlayBlock);
            $manager->flush();

            $this->addReference('htp-block:' . $block['reference'], $howToPlayBlock);
        }
    }
}
