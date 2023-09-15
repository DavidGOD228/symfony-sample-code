<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Interfaces\RunRoundInterface;
use CoreBundle\Service\RunRoundRepositoryProvider;

/**
 * Class RoundNumberResolver
 */
final class RoundNumberResolver
{
    private RunRoundRepositoryProvider $runRoundRepositoryProvider;

    /**
     * RoundNumberResolver constructor.
     *
     * @param RunRoundRepositoryProvider $classResolver
     */
    public function __construct(RunRoundRepositoryProvider $classResolver)
    {
        $this->runRoundRepositoryProvider = $classResolver;
    }

    /**
     * @param Bet $bet
     *
     * @return int|null
     */
    public function getRoundNumber(Bet $bet): ?int
    {
        $gameId = $bet->getOdd()->getGame()->getId();

        if (GameDefinition::isLottery($gameId)) {
            return null;
        }

        /** @var RunRoundInterface $runRound */
        $runRound = $this->runRoundRepositoryProvider->getMasterRepository($gameId)->find($bet->getRunRoundId());

        return $runRound->getRoundNumber();
    }
}
