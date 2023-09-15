<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\HowToPlay;

use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\HowToPlayPreset;
use Acme\SymfonyDb\Entity\OddPreset;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\PartnerEnabledGame;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class PartnerEnabledGameFixture
 */
class PartnerEnabledGameFixture extends Fixture
{
    private const SYSTEM_USER_ID = 1;
    private const ENABLED_GAMES = [
        // How to play fixtures data
        [
            'reference' => 'partner-with-no-parent-and-default-preset',
            'preset' => 'default', 'partner' => '1', 'game' => '1'
        ],
        [
            'reference' => 'partner-with-parent-and-no-preset',
            'preset' => null, 'partner' => '2', 'game' => '1'
        ],
        [
            'reference' => 'partner-with-no-parent-and-no-preset',
            'preset' => null, 'partner' => '3', 'game' => '1'
        ],
        [
            'reference' => 'partner-with-no-parent-and-custom-preset',
            'preset' => 'custom', 'partner' => '3', 'game' => '1'
        ],
        [
            'reference' => 'partner-with-rtp-block',
            'preset' => 'default:rtp', 'partner' => '1', 'game' => '1'
        ],
        [
            'reference' => 'partner-with-subs-and-combo-blocks',
            'preset' => 'default:subs-and-combo', 'partner' => '1', 'game' => '1'
        ],
        [
            'reference' => 'partner-with-no-blocks',
            'preset' => 'default:no-content', 'partner' => '1', 'game' => '1'
        ],
        [
            'reference' => 'partner-with-cashback-block',
            'preset' => 'default:cashback', 'partner' => '1', 'game' => '1'
        ],
        [
            'reference' => 'partner-with-jackpot-speedy7-block',
            'preset' => 'default:jackpot-speedy7', 'partner' => '1', 'game' => '11'
        ],
        [
            'reference' => 'partner-with-jackpot-headsup-block',
            'preset' => 'default:jackpot-headsup', 'partner' => '1', 'game' => '12'
        ],
        [
            'reference' => 'partner-with-gamification-block',
            'preset' => 'gamification', 'partner' => '1', 'game' => '1'
        ],
        // How to play on screen data
        [
            'reference' => '1',
            'preset' => 'default', 'partner' => '1', 'game' => '1'
        ],
        [
            'reference' => 'partner-with-video-url',
            'preset' => 'default', 'partner' => '1', 'game' => '13'
        ],
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach (self::ENABLED_GAMES as $enabledGame) {
            /** @var Game $game */
            $game = $this->getReference('game:' . $enabledGame['game']);
            $oddPreset = (new OddPreset())
                ->setGame($game)
                ->setName('preset 0.05')
                ->setRtpToShow(0.95)
                ->setPublishedBy(self::SYSTEM_USER_ID)
                ->setPublishedDate(new \DateTime('2001-10-11 00:00:00'));
            $manager->persist($oddPreset);

            /** @var HowToPlayPreset $preset */
            $preset = $enabledGame['preset'] ? $this->getReference('preset:' . $enabledGame['preset']) : null;
            /** @var Partner $partner */
            $partner = $this->getReference('partner:' . $enabledGame['partner']);
            $partnerEnabledGame = (new PartnerEnabledGame())
                ->setOddPreset($oddPreset)
                ->setHowToPlayPreset($preset)
                ->setPartner($partner)
                ->setPublishedDate(new \DateTime())
                ->setPublishedBy(self::SYSTEM_USER_ID)
                ->setGame($game);
            $manager->persist($partnerEnabledGame);
            $manager->flush();

            $this->addReference('partner-enabled-game:' . $enabledGame['reference'], $partnerEnabledGame);
        }
    }
}
