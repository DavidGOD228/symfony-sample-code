<?php

declare(strict_types=1);

namespace GamesApiBundle\DataObject\HowToPlay;

/**
 * Class HowToPlayBlockDTO
 */
class HowToPlayBlockDTO
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $content;

    /**
     * @var bool
     */
    private $isExpandable;

    /**
     * HowToPlayBlock constructor.
     *
     * @param string $title
     * @param string $content
     * @param bool $isExpandable
     */
    public function __construct(
        string $title,
        string $content,
        bool $isExpandable
    )
    {
        $this->title = $title;
        $this->content = $content;
        $this->isExpandable = $isExpandable;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return bool
     */
    public function isExpandable(): bool
    {
        return $this->isExpandable;
    }
}