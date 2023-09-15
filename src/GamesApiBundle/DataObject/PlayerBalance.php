<?php

namespace GamesApiBundle\DataObject;

/**
 * Class Balance
 */
class PlayerBalance
{
    /**
     * @var bool
     */
    private $show;

    /**
     * @var string
     */
    private $value;

    /**
     * Balance constructor.
     *
     * @param bool $show
     * @param string $value
     */
    public function __construct(
        bool $show,
        string $value = '0'
    )
    {
        $this->show = $show;
        $this->value = $value;
    }

    /**
     * @return bool
     */
    public function getShow(): bool
    {
        return $this->show;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}