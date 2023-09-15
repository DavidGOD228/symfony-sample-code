<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\GameRunResults\Formatter;

use Acme\SymfonyDb\Entity\GameRun;
use GamesApiBundle\Service\CardFormatter;
use GamesApiBundle\Service\GameRunResults\Calculator\MatkaCalculator;

/**
 * Class MatkaRegularFormatter
 */
final class MatkaRegularFormatter implements FormatterInterface
{
    private MatkaCalculator $matkaCalculator;

    /**
     * MatkaRegularFormatter constructor.
     *
     * @param MatkaCalculator $matkaCalculator
     */
    public function __construct(MatkaCalculator $matkaCalculator)
    {
        $this->matkaCalculator = $matkaCalculator;
    }

    /**
     * @param GameRun $gameRun
     *
     * @return array
     */
    public function format(GameRun $gameRun): array
    {
        $cards = $gameRun->getMatkaRunCards();
        $outcome = $this->matkaCalculator->calculate($cards)->getWinners();

        $cardsFormatted = [];

        foreach ($cards as $card) {
            $cardsFormatted[] = CardFormatter::formatCard($card->getCard());
        }

        $results = [
            'cards' => $cardsFormatted,
            'outcome' => $outcome
        ];

        return $results;
    }
}
