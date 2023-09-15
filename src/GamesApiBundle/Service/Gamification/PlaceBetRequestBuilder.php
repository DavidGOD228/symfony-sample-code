<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification;

use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Combination;
use Acme\SymfonyDb\Interfaces\PlayerBetInterface;
use CoreBundle\Exception\ValidationException;
use GamesApiBundle\DataObject\Gamification\PlaceBetRequest;
use PartnerApiBundle\Service\RpsBetService;

/**
 * Class PlaceBetRequestBuilder
 */
final class PlaceBetRequestBuilder
{
    private CaptainUpCurrencyConverter $currencyCoverter;
    private RpsBetService $rpsBetService;
    private RoundNumberResolver $roundNumberResolver;
    private BetItemsBuilder $betItemsBuilder;

    /**
     * PlaceBetRequestBuilder constructor.
     *
     * @param CaptainUpCurrencyConverter $currencyCoverter
     * @param RpsBetService $rpsBetService
     * @param RoundNumberResolver $roundNumberResolver
     * @param BetItemsBuilder $betItemsBuilder
     */
    public function __construct(
        CaptainUpCurrencyConverter $currencyCoverter,
        RpsBetService $rpsBetService,
        RoundNumberResolver $roundNumberResolver,
        BetItemsBuilder $betItemsBuilder
    )
    {
        $this->currencyCoverter = $currencyCoverter;
        $this->rpsBetService = $rpsBetService;
        $this->roundNumberResolver = $roundNumberResolver;
        $this->betItemsBuilder = $betItemsBuilder;
    }

    /**
     * @param Bet $bet
     * @param string $betType
     *
     * @return PlaceBetRequest
     *
     * @throws ValidationException
     */
    public function buildSingle(Bet $bet, string $betType): PlaceBetRequest
    {
        $params = $this->buildBaseRequestParams($bet);

        $oddClass = OddClassFormatter::format($bet->getOdd());
        // Push value for RPS game
        $tieOddValue = $this->rpsBetService->getTieOddValue($bet);
        $roundNumber = $this->roundNumberResolver->getRoundNumber($bet);
        $betItems = $this->betItemsBuilder->build([$bet]);

        $params[PlaceBetRequest::BET_TYPE_FIELD] = $betType;
        $params[PlaceBetRequest::GAME_IDS_FIELD] = [$bet->getOdd()->getGame()->getId()];
        $params[PlaceBetRequest::RUN_CODES_FIELD] = [$bet->getGameRun()->getCode()];
        $params[PlaceBetRequest::BET_ITEMS_FIELD] = $betItems;
        $params[PlaceBetRequest::ODD_CLASSES_FIELD] = [$oddClass];
        $params[PlaceBetRequest::ODD_VALUE_FIELD] = $bet->getOddValue();
        $params[PlaceBetRequest::TIE_ODD_VALUE_FIELD] = $tieOddValue;
        $params[PlaceBetRequest::ROUND_NUMBER_FIELD] = $roundNumber;

        $request = new PlaceBetRequest($params);

        return $request;
    }

    /**
     * @param Combination $combination
     * @param string $betType
     *
     * @return PlaceBetRequest
     *
     * @throws ValidationException
     */
    public function buildCombination(Combination $combination, string $betType): PlaceBetRequest
    {
        $params = $this->buildBaseRequestParams($combination);

        $bets = $combination->getBets();

        $gameIds = [];
        $runCodes = [];
        $oddClasses = [];

        foreach ($bets as $bet) {
            $runCodes[] = $bet->getGameRun()->getCode();
            $gameIds[] = $bet->getOdd()->getGame()->getId();
            $oddClasses[] = OddClassFormatter::format($bet->getOdd());
        }

        $betItems = $this->betItemsBuilder->build($bets);

        $params[PlaceBetRequest::BET_TYPE_FIELD] = $betType;
        $params[PlaceBetRequest::GAME_IDS_FIELD] = $gameIds;
        $params[PlaceBetRequest::RUN_CODES_FIELD] = $runCodes;
        $params[PlaceBetRequest::BET_ITEMS_FIELD] = $betItems;
        $params[PlaceBetRequest::ODD_CLASSES_FIELD] = $oddClasses;
        $params[PlaceBetRequest::ODD_VALUE_FIELD] = $combination->getOddValue();
        // Tie odd value is null, because RPS game is not a part of combination feature
        $params[PlaceBetRequest::TIE_ODD_VALUE_FIELD] = null;
        // Round number is null, because Card games are not a part of combination feature
        $params[PlaceBetRequest::ROUND_NUMBER_FIELD] = null;

        $request = new PlaceBetRequest($params);

        return $request;
    }

    /**
     * @param PlayerBetInterface $bet
     *
     * @return array
     *
     * @throws ValidationException
     */
    private function buildBaseRequestParams(PlayerBetInterface $bet): array
    {
        $partnerCode = $bet->getPlayer()->getPartner()->getApiCode();
        // Technically this should not happen because gamification is used by partners which already launched an iframe
        if (!$partnerCode) {
            $partnerCode = '';
        }

        $betAmount = $bet->getAmount();
        $betAmountInEur = $this->currencyCoverter->covertToDefault($bet->getCurrency(), $betAmount);

        $params = [
            PlaceBetRequest::PARTNER_CODE_FIELD => $partnerCode,
            PlaceBetRequest::CURRENCY_CODE_FIELD => $bet->getCurrency()->getCode(),
            PlaceBetRequest::BET_TIME_FIELD => $bet->getCreatedAt()->format('Y-m-d H:i:s'),
            PlaceBetRequest::BET_AMOUNT_FIELD => $betAmount,
            PlaceBetRequest::BET_AMOUNT_EUR_FIELD => $betAmountInEur,
        ];

        return $params;
    }
}
