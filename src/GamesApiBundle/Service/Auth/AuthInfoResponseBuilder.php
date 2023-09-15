<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Auth;

use Acme\SymfonyDb\Entity\Player;
use CoreBundle\Service\NodeService;
use GamesApiBundle\DataObject\Auth\PartnerAuthInfoPlayer;
use GamesApiBundle\DataObject\Auth\PartnerAuthInfoResponse;
use GamesApiBundle\DataObject\CurrencyDto;
use GamesApiBundle\Service\Initial\Component\CurrencyButtonAmountService;
use GamesApiBundle\Service\PlayerBalanceService;

/**
 * Class AuthInfoResponseBuilder
 */
final class AuthInfoResponseBuilder
{
    private NodeService $nodeService;
    private PlayerBalanceService $balanceService;
    private CurrencyButtonAmountService $currencyButtonAmountService;

    /**
     * @param NodeService $nodeService
     * @param PlayerBalanceService $balanceService
     * @param CurrencyButtonAmountService $currencyButtonAmountService
     */
    public function __construct(
        NodeService $nodeService,
        PlayerBalanceService $balanceService,
        CurrencyButtonAmountService $currencyButtonAmountService
    )
    {
        $this->nodeService = $nodeService;
        $this->balanceService = $balanceService;
        $this->currencyButtonAmountService = $currencyButtonAmountService;
    }

    /**
     * @param string $sessionId
     * @param Player $player
     *
     * @return PartnerAuthInfoResponse
     */
    public function build(
        string $sessionId,
        Player $player
    ): PartnerAuthInfoResponse
    {
        $partner = $player->getPartner();

        $playerInfo = $this->buildPlayer($player);
        $favoriteOddsIds = $this->getFavoriteOddsIds($player);

        $webSocketsToken = $this->nodeService->getNodeSocketToken($player->getId(), $partner);

        $currency = $player->getCurrency();
        $currencyButtonAmounts = $this->currencyButtonAmountService->getBetAmounts($currency);

        $authInfo = new PartnerAuthInfoResponse(
            $sessionId,
            $playerInfo,
            $currencyButtonAmounts,
            new CurrencyDto($currency),
            $favoriteOddsIds,
            $webSocketsToken
        );

        return $authInfo;
    }

    /**
     * @param Player $player
     *
     * @return array<int,array<int>>
     */
    private function getFavoriteOddsIds(Player $player): array
    {
        $favoriteOdds = $player->getFavoriteOdds();

        $favoriteOddsIds = [];
        foreach ($favoriteOdds as $favoriteOdd) {
            $odd = $favoriteOdd->getOdd();
            $favoriteOddsIds[$odd->getGame()->getId()][] = $favoriteOdd->getOdd()->getId();
        }

        return $favoriteOddsIds;
    }

    /**
     * @param Player $player
     *
     * @return PartnerAuthInfoPlayer
     */
    private function buildPlayer(
        Player $player
    ): PartnerAuthInfoPlayer
    {
        $balance = $this->balanceService->getPlayerBalance(
            $player->getPartner(),
            $player
        );

        $authPlayer = new PartnerAuthInfoPlayer(
            $player->getId(),
            $balance,
            $player->getTag(),
            $player->getExternalToken(),
        );

        return $authPlayer;
    }
}
