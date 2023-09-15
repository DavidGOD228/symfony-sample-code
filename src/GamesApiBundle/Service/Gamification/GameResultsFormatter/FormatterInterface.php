<?php

namespace GamesApiBundle\Service\Gamification\GameResultsFormatter;

/**
 * Interface FormatterInterface
 */
interface FormatterInterface
{
    public const SEPARATOR = '_';
    public const RETURNED_GAME_RUN = 'cancel';

    /**
     * @param array $results
     *
     * @return array
     */
    public function format(array $results): array;
}