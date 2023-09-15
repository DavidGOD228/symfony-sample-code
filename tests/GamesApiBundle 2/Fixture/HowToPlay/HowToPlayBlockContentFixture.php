<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\HowToPlay;

use Acme\SymfonyDb\Entity\HowToPlayBlock;
use Acme\SymfonyDb\Entity\HowToPlayBlockContent;
use Acme\SymfonyDb\Entity\Language;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class HowToPlayBlockContent
 */
class HowToPlayBlockContentFixture extends Fixture
{
    private const CONTENT_BLOCKS = [
        [
            'id' => 201, 'language' => 'en', 'block' => 'general', 'title' => 'General',
            'content' => 'Some general info about the game in English', 'reference' => 'general-en',
        ],
        [
            'id' => 202, 'language' => 'en', 'block' => 'rules', 'title' => 'Rules',
            'content' => 'Game rules list in English', 'reference' => 'rules-en',
        ],
        [
            'id' => 203, 'language' => 'en', 'block' => 'video', 'title' => 'Game overview',
            'content' => 'http//test/video/url.mp4', 'reference' => 'video-en',
        ],
        [
            'id' => 204, 'language' => 'ru', 'block' => 'general', 'title' => 'Генеральный',
            'content' => 'Some general info about the game in Russian', 'reference' => 'general-ru',
        ],
        [
            'id' => 205, 'language' => 'ru', 'block' => 'video', 'title' => 'Обзор игры',
            'content' => 'http//test/video/url-russian.mp4', 'reference' => 'video-ru',
        ],
        [
            'id' => 206, 'language' => 'en', 'block' => 'rtp', 'title' => 'Return-to-player',
            'content' => 'Random RTP content: ', 'reference' => 'rtp-en',
        ],
        [
            'id' => 207, 'language' => 'en', 'block' => 'subscriptions', 'title' => 'Subscriptions',
            'content' => 'Some content from block with subscriptions', 'reference' => 'subscriptions-en',
        ],
        [
            'id' => 208, 'language' => 'en', 'block' => 'combinations', 'title' => 'Combinations',
            'content' => 'Some content from block with combinations', 'reference' => 'combinations-en',
        ],
        [
            'id' => 209, 'language' => 'en', 'block' => 'cashback', 'title' => 'Cashback',
            'content' => 'Some content from block with cashback', 'reference' => 'cashback-en',
        ],
        [
            'id' => 210, 'language' => 'en', 'block' => 'jackpot-speedy7', 'title' => 'Jackpot',
            'content' => 'Some content from block with jackpot for speedy7', 'reference' => 'jackpot-speedy7-en',
        ],
        [
            'id' => 211, 'language' => 'en', 'block' => 'jackpot-headsup', 'title' => 'Jackpot',
            'content' => 'Some content from block with jackpot for headsup',  'reference' => 'jackpot-headsup-en',
        ],
        [
            'id' => 212, 'language' => 'en', 'block' => 'gamification', 'title' => 'Hero',
            'content' => 'Some content from block with gamification', 'reference' => 'gamification-en',
        ],
    ];

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach (self::CONTENT_BLOCKS as $contentBlock) {
            /** @var Language $language */
            $language = $this->getReference('language:' . $contentBlock['language']);
            /** @var HowToPlayBlock $howToPlayBlock */
            $howToPlayBlock = $this->getReference('htp-block:' . $contentBlock['block']);
            $howToPlayBlockContent = HowToPlayBlockContent::createFromId($contentBlock['id'])
                ->setLanguage($language)
                ->setTitle($contentBlock['title'])
                ->setContent($contentBlock['content'])
                ->setBlock($howToPlayBlock);
            $manager->persist($howToPlayBlockContent);

            $this->addReference('htp-block-content:' . $contentBlock['reference'], $howToPlayBlockContent);
        }

        $manager->flush();
    }
}
