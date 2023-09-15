<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Initial\Component;

use Acme\SymfonyDb\Entity\Player;
use GamesApiBundle\DataObject\InitialInfo\Component\PartnerInitialInfoPlayer;
use GamesApiBundle\Service\PlayerBalanceService;

/**
 * Class InitialPlayerBuilder
 * @deprecated frontend is migrating to initial API v3 - avoid modifying any initial API v2 code if possible
 */
final class InitialPlayerBuilder
{
    private PlayerBalanceService $balanceService;
    private InitialGamificationBuilder $gamificationBuilder;

    /**
     * @param PlayerBalanceService $balanceService
     * @param InitialGamificationBuilder $gamificationBuilder
     */
    public function __construct(
        PlayerBalanceService $balanceService,
        InitialGamificationBuilder $gamificationBuilder
    )
    {
        $this->balanceService = $balanceService;
        $this->gamificationBuilder = $gamificationBuilder;
    }

    /**
     * @param Player $player
     * @param string|null $iosAppVersion
     *
     * @return PartnerInitialInfoPlayer
     */
    public function build(
        Player $player,
        ?string $iosAppVersion
    ): PartnerInitialInfoPlayer
    {
        $balance = $this->balanceService->getPlayerBalance(
            $player->getPartner(),
            $player
        );

        $gamification = $this->gamificationBuilder->build($player, $iosAppVersion);

        $initialPlayer = new PartnerInitialInfoPlayer(
            $player->getId(),
            $balance,
            $player->getExternalToken(),
            $gamification
        );

        return $initialPlayer;
    }
}
