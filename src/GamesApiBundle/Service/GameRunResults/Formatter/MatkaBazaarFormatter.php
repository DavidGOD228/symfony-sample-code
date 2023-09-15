<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\GameRunResults\Formatter;

use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Type\BazaarRunType;
use GamesApiBundle\Service\BazaarRun\BazaarNameMapper;
use GamesApiBundle\Service\CardFormatter;
use GamesApiBundle\Service\GameRunResults\Calculator\MatkaCalculator;

/**
 * Class MatkaBazaarFormatter
 */
final class MatkaBazaarFormatter implements FormatterInterface
{
    private MatkaCalculator $matkaCalculator;
    private BazaarNameMapper $bazaarNameMapper;

    /**
     * MatkaBazaarFormatter constructor.
     *
     * @param MatkaCalculator $matkaCalculator
     * @param BazaarNameMapper $bazaarNameMapper
     */
    public function __construct(
        MatkaCalculator $matkaCalculator,
        BazaarNameMapper $bazaarNameMapper
    )
    {
        $this->matkaCalculator = $matkaCalculator;
        $this->bazaarNameMapper = $bazaarNameMapper;
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
            'bazaar' => null,
            'cards' => $cardsFormatted,
            'outcome' => $outcome
        ];

        $bazaarRun = $gameRun->getBazaarRun();

        if ($bazaarRun) {
            $results['bazaar'] = [
                'name' => $this->bazaarNameMapper->mapName($bazaarRun->getTitle()),
                'isReturned' => $bazaarRun->isReturned(),
            ];

            if ($gameRun->getId() === $bazaarRun->getOpeningRun()->getId()) {
                $results['bazaar']['type'] = BazaarRunType::OPENING_RUN;
            } elseif ($gameRun->getId() === $bazaarRun->getClosingRun()->getId()) {
                $results['bazaar']['type'] = BazaarRunType::CLOSING_RUN;
            }
        }

        return $results;
    }
}
