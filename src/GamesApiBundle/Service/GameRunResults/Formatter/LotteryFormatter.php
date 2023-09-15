<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults\Formatter;

use Acme\SymfonyDb\Entity\GameRun;
use GamesApiBundle\DataObject\RecentBet\Component\RecentBetDataItem;
use GamesApiBundle\Service\GameItemService;

/**
 * Class LotteryFormatter
 */
final class LotteryFormatter implements FormatterInterface
{
    private GameItemService $gameItemService;

    /**
     * LotteryFormatter constructor.
     *
     * @param GameItemService $gameItemService
     */
    public function __construct(GameItemService $gameItemService)
    {
        $this->gameItemService = $gameItemService;
    }

    /**
     * @param GameRun $gameRun
     *
     * @return array
     */
    public function format(GameRun $gameRun): array
    {
        $results = [];
        foreach ($gameRun->getGameRunResult() as $result) {
            foreach ($result->getGameRunResultItems() as $item) {
                $type = $this->gameItemService->getType($item->getGameItem());
                $color = $this->gameItemService->getColorByResult($item);
                $results[] = new RecentBetDataItem(
                    $type,
                    $item->getGameItem()->getNumber(),
                    $color
                );
            }
        }

        return $results;
    }
}
