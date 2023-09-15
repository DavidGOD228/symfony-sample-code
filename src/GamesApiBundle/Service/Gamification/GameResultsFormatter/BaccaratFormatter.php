<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\Gamification\GameResultsFormatter;

use Acme\SymfonyDb\Type\BaccaratDealtToType;
use GamesApiBundle\Service\GameRunResults\BaccaratSideOddsProvider;
use GamesApiBundle\Service\GameRunResults\Formatter\BaccaratFormatter as BaseResultFormatter;

/**
 * Class BaccaratFormatter
 */
class BaccaratFormatter implements FormatterInterface
{
    private const FIELD_PLAYER = BaccaratDealtToType::PLAYER;
    private const FIELD_DEALER = BaccaratDealtToType::BANKER;

    private const FIELD_WINNER = BaseResultFormatter::FIELD_WINNER;
    private const FIELD_CARD = BaseResultFormatter::FIELD_CARD;
    private const FIELD_WON_SIDE_ODDS = BaseResultFormatter::FIELD_SIDE_ODDS;

    private BaccaratSideOddsProvider $baccaratSideOddsProvider;

    /**
     * BaccaratFormatter constructor.
     *
     * @param BaccaratSideOddsProvider $baccaratSideOddsProvider
     */
    public function __construct(BaccaratSideOddsProvider $baccaratSideOddsProvider)
    {
        $this->baccaratSideOddsProvider = $baccaratSideOddsProvider;
    }

    /**
     * @param array $results
     *
     * @return array
     */
    public function format(array $results): array
    {
        foreach ($results[self::FIELD_PLAYER] as $result) {
            $formattedResults[] = self::FIELD_PLAYER . self::SEPARATOR . $result[self::FIELD_CARD];
        }

        foreach ($results[self::FIELD_DEALER] as $result) {
            $formattedResults[] = self::FIELD_DEALER . self::SEPARATOR . $result[self::FIELD_CARD];
        }

        $formattedResults[] = self::FIELD_WINNER . self::SEPARATOR . $results[self::FIELD_WINNER];


        $sideOddsIdsMap = $this->baccaratSideOddsProvider->getSideOddsIdsMap();

        foreach ($results[self::FIELD_WON_SIDE_ODDS] as $result) {
            $formattedResults[] = sprintf(
                '%s%s%s',
                self::FIELD_WON_SIDE_ODDS,
                self::SEPARATOR,
                array_search($result, $sideOddsIdsMap, true),
            );
        }

        return $formattedResults;
    }
}
