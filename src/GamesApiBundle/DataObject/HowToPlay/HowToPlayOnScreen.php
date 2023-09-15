<?php

namespace GamesApiBundle\DataObject\HowToPlay;

/**
 * Class HowToPlayOnScreen
 */
class HowToPlayOnScreen
{
    public string $videoSrc;

    /**
     * @param string $videoSrc
     */
    public function __construct(string $videoSrc)
    {
        $this->videoSrc = $videoSrc;
    }
}