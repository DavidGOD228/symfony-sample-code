<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\HowToPlay;

use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\HowToPlayBlock;
use Acme\SymfonyDb\Entity\HowToPlayPreset;
use Acme\SymfonyDb\Entity\HowToPlayPresetBlock;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class HowToPlayPresetFixture
 */
class HowToPlayPresetFixture extends Fixture
{
    private const PRESETS = [
        [
            'id' => 201, 'reference' => 'default', 'name' => 'Default preset',
            'isDefault' => true, 'game' => '1', 'blocks' =>
            [
                ['order' => 201, 'reference' => 'general'],
                ['order' => 202, 'reference' => 'rules'],
                ['order' => 203, 'reference' => 'video'],
            ]
        ],
        [
            'id' => 202, 'reference' => 'custom', 'name' => 'Custom preset',
            'isDefault' => false, 'game' => '1', 'blocks' =>
            [
                ['order' => 204, 'reference' => 'general'],
                ['order' => 205, 'reference' => 'rules'],
                ['order' => 206, 'reference' => 'video'],
            ]
        ],
        [
            'id' => 203, 'reference' => 'default:rtp', 'name' => 'Default RTP preset',
            'isDefault' => true, 'game' => '1', 'blocks' =>
            [
                ['order' => 207, 'reference' => 'rules'],
                ['order' => 208, 'reference' => 'rtp'],
            ]
        ],
        [
            'id' => 204, 'reference' => 'default:subs-and-combo', 'name' => 'Default Subs and Combo preset',
            'isDefault' => true, 'game' => '1', 'blocks' =>
            [
                ['order' => 209, 'reference' => 'subscriptions'],
                ['order' => 210, 'reference' => 'combinations'],
            ]
        ],
        [
            'id' => 205, 'reference' => 'default:no-content', 'name' => 'Default preset without blocks',
            'isDefault' => true, 'game' => '1', 'blocks' => []
        ],
        [
            'id' => 206, 'reference' => 'default:cashback', 'name' => 'Default Cashback preset',
            'isDefault' => true, 'game' => '1', 'blocks' =>
            [
                ['order' => 211, 'reference' => 'cashback'],
            ]
        ],
        [
            'id' => 207, 'reference' => 'default:jackpot-speedy7', 'name' => 'Default Jackpot S7 preset',
            'isDefault' => true, 'game' => '11', 'blocks' =>
            [
                ['order' => 212, 'reference' => 'jackpot-speedy7'],
            ]
        ],
        [
            'id' => 208, 'reference' => 'default:jackpot-headsup', 'name' => 'Default Jackpot 6+ preset',
            'isDefault' => true, 'game' => '12', 'blocks' =>
            [
                ['order' => 212, 'reference' => 'jackpot-headsup'],
            ]
        ],
        [
            'id' => 209, 'reference' => 'gamification', 'name' => 'Default Lucky 7 preset',
            'isDefault' => true, 'game' => '1', 'blocks' =>
            [
                ['order' => 213, 'reference' => 'gamification'],
            ]
        ],
        // How to play on screen fixtures
        [
            'id' => 210, 'reference' => 'preset-with-video-url', 'name' => 'Preset with video url',
            'isDefault' => false, 'game' => '13', 'blocks' =>
            [
                ['order' => 214, 'reference' => 'video'],
            ]
        ],
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach (self::PRESETS as $preset) {
            /** @var Game $game */
            $game = $this->getReference('game:' . $preset['game']);

            $howToPlayPreset = HowToPlayPreset::createFromId($preset['id'])
                ->setGame($game)
                ->setName($preset['name'])
                ->setIsDefault($preset['isDefault']);

            foreach ($preset['blocks'] as $block) {
                /** @var HowToPlayBlock $howToPlayBlock */
                $howToPlayBlock = $this->getReference('htp-block:' . $block['reference']);

                $presetBlock = (new HowToPlayPresetBlock())
                    ->setOrder($block['order'])
                    ->setBlock($howToPlayBlock)
                    ->setPreset($howToPlayPreset);
                $howToPlayPreset->addPresetBlock($presetBlock);
            }

            $manager->persist($howToPlayPreset);
            $manager->flush();

            $this->addReference('preset:' . $preset['reference'], $howToPlayPreset);
        }
    }
}
