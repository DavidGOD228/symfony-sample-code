<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification;

use Acme\Contract\GameDefinition;
use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Combination;
use Acme\SymfonyDb\Interfaces\PlayerBetInterface;
use CoreBundle\Exception\ValidationException;
use GamesApiBundle\DataObject\Gamification\PayOutBetRequest;
use GamesApiBundle\Service\BetStatusResolver;
use GamesApiBundle\Service\GameRunResults\ResultsBuilder;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\FormatterInterface;
use PartnerApiBundle\Service\RpsBetService;

/**
 * Class PayOutRequestBuilder
 */
final class PayOutRequestBuilder
{
    private const SEPARATOR = '-';

    private CaptainUpCurrencyConverter $currencyCoverter;
    private BetStatusResolver $statusResolver;
    private RpsBetService $rpsBetService;
    private ResultsBuilder $resultsBuilder;
    private GameResultsFormatterFactory $gameResultsFormatterFactory;
    private BetItemsBuilder $betItemsBuilder;
    private RoundNumberResolver $roundNumberResolver;

    /**
     * PayOutRequestBuilder constructor.
     *
     * @param CaptainUpCurrencyConverter $currencyCoverter
     * @param BetStatusResolver $statusResolver
     * @param RpsBetService $rpsBetService
     * @param ResultsBuilder $resultsBuilder
     * @param GameResultsFormatterFactory $gameResultsFormatterFactory
     * @param BetItemsBuilder $betItemsBuilder
     * @param RoundNumberResolver $roundNumberResolver
     */
    public function __construct(
        CaptainUpCurrencyConverter $currencyCoverter,
        BetStatusResolver $statusResolver,
        RpsBetService $rpsBetService,
        ResultsBuilder $resultsBuilder,
        GameResultsFormatterFactory $gameResultsFormatterFactory,
        BetItemsBuilder $betItemsBuilder,
        RoundNumberResolver $roundNumberResolver
    )
    {
        $this->currencyCoverter = $currencyCoverter;
        $this->statusResolver = $statusResolver;
        $this->rpsBetService = $rpsBetService;
        $this->resultsBuilder = $resultsBuilder;
        $this->gameResultsFormatterFactory = $gameResultsFormatterFactory;
        $this->betItemsBuilder = $betItemsBuilder;
        $this->roundNumberResolver = $roundNumberResolver;
    }

    /**
     * @param Bet $bet
     * @param string $betType
     *
     * @return PayOutBetRequest
     *
     * @throws ValidationException
     */
    public function buildSingle(Bet $bet, string $betType): PayOutBetRequest
    {
        $params = $this->buildBaseRequestParams($bet);

        $oddClass = OddClassFormatter::format($bet->getOdd());
        // Push value for RPS game
        $tieOddValue = $this->rpsBetService->getTieOddValue($bet);
        $betStatus = $this->statusResolver->resolveSingleBet($bet);
        $roundNumber = $this->roundNumberResolver->getRoundNumber($bet);

        $results = $this->buildGameRunResults($bet);
        $betItems = $this->betItemsBuilder->build([$bet]);

        $params[PayOutBetRequest::BET_STATUS_FIELD] = $betStatus;
        $params[PayOutBetRequest::BET_TYPE_FIELD] = $betType;
        $params[PayOutBetRequest::GAME_IDS_FIELD] = [$bet->getOdd()->getGame()->getId()];
        $params[PayOutBetRequest::RUN_CODES_FIELD] = [$bet->getGameRun()->getCode()];
        $params[PayOutBetRequest::ODD_CLASSES_FIELD] = [$oddClass];
        $params[PayOutBetRequest::RESULTS_FIELD] = $results;
        $params[PayOutBetRequest::BET_ITEMS_FIELD] = $betItems;
        $params[PayOutBetRequest::ODD_VALUE_FIELD] = $bet->getOddValue();
        $params[PayOutBetRequest::TIE_ODD_VALUE_FIELD] = $tieOddValue;
        $params[PayOutBetRequest::ROUND_NUMBER_FIELD] = $roundNumber;

        $request = new PayOutBetRequest($params);

        return $request;
    }

    /**
     * @param Combination $combination
     * @param string $betType
     *
     * @return PayOutBetRequest
     *
     * @throws ValidationException
     */
    public function buildCombination(Combination $combination, string $betType): PayOutBetRequest
    {
        $params = $this->buildBaseRequestParams($combination);

        $bets = $combination->getBets();

        $gameIds = [];
        $runCodes = [];
        $oddClasses = [];
        $runResults = [];

        foreach ($bets as $bet) {
            $runCodes[] = $bet->getGameRun()->getCode();
            $gameId = $bet->getOdd()->getGame()->getId();
            $gameIds[] = $gameId;
            $oddClasses[] = OddClassFormatter::format($bet->getOdd());
            $runResults[] = $this->buildGameRunResults($bet);
        }

        $results = array_merge(...$runResults);

        $betStatus = $this->statusResolver->resolveCombinationBet($combination);
        $betItems = $this->betItemsBuilder->build($bets);

        $params[PayOutBetRequest::BET_STATUS_FIELD] = $betStatus;
        $params[PayOutBetRequest::BET_TYPE_FIELD] = $betType;
        $params[PayOutBetRequest::GAME_IDS_FIELD] = $gameIds;
        $params[PayOutBetRequest::RUN_CODES_FIELD] = $runCodes;
        $params[PayOutBetRequest::ODD_CLASSES_FIELD] = $oddClasses;
        $params[PayOutBetRequest::RESULTS_FIELD] = $results;
        $params[PayOutBetRequest::BET_ITEMS_FIELD] = $betItems;
        $params[PayOutBetRequest::ODD_VALUE_FIELD] = $combination->getOddValue();
        // Tie odd value is null, because RPS game is not a part of combination feature
        $params[PayOutBetRequest::TIE_ODD_VALUE_FIELD] = null;
        // Round number is null, because Card games are not a part of combination feature
        $params[PayOutBetRequest::ROUND_NUMBER_FIELD] = null;

        $request = new PayOutBetRequest($params);

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

        $amountWon = $bet->getAmountWon();
        $amountWonInEur = $this->currencyCoverter->covertToDefault($bet->getCurrency(), $amountWon);

        $params = [
            PayOutBetRequest::PARTNER_CODE_FIELD => $partnerCode,
            PayOutBetRequest::CURRENCY_CODE_FIELD => $bet->getCurrency()->getCode(),
            PayOutBetRequest::BET_TIME_FIELD => $bet->getCreatedAt()->format('Y-m-d H:i:s'),
            PayOutBetRequest::AMOUNT_WON_FIELD => $amountWon,
            PayOutBetRequest::AMOUNT_WON_EUR_FIELD => $amountWonInEur,
        ];

        return $params;
    }

    /**
     * @param Bet $bet
     *
     * @return array
     */
    private function buildGameRunResults(Bet $bet): array
    {
        $gameRun = $bet->getGameRun();
        $gameId = $gameRun->getGame()->getId();

        // Speedy7 pay outs happens before game run ended - after any round can be.
        // So we can't just format results because there could be not all cards on table.
        // Can't use NullFormatter from GameResults or Gamification because error occurs in Speedy7Formatter.php:32.
        // TODO: https://jira.Acme.tv/browse/CORE-2684
        if ($gameId === GameDefinition::SPEEDY7) {
            return [];
        }

        // In case of returned game run, we will not have any results
        if ($gameRun->getIsReturned()) {
            return [$gameId . self::SEPARATOR . FormatterInterface::RETURNED_GAME_RUN];
        }

        $results = $this->resultsBuilder->getResultsCached($gameRun);

        $formatter = $this->gameResultsFormatterFactory->getFormatter($gameId);

        $formattedResults = $formatter->format($results);

        $results = [];
        foreach ($formattedResults as $formattedResult) {
            $results[] = $gameId . self::SEPARATOR . $formattedResult;
        }

        return $results;
    }
}
