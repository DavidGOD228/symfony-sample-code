<?php

declare(strict_types=1);

namespace GamesApiBundle\Exception;

use Acme\SymfonyDb\Entity\BazaarRun;
use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\BetItem;
use Acme\SymfonyDb\Entity\Combination;
use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyDb\Entity\Partner;
use Acme\SymfonyDb\Entity\Subscription;
use Acme\SymfonyDb\Interfaces\PlayerBetInterface;
use Acme\SymfonyDb\Interfaces\RunRoundInterface;
use CoreBundle\Exception\ValidationException;

/**
 * Class PayInValidationException
 */
final class PayInValidationException extends ValidationException
{
    protected string $details;

    /**
     * PayInException constructor.
     *
     * Should be created by named static constructors.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    protected function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @param GameRun $gameRun
     *
     * @return static
     */
    public static function createCardGameWithoutRunRoundId(GameRun $gameRun): self
    {
        $exception = new self('wrong_run');
        $exception->details = sprintf(
            'Requested card gameId=%s but not provided run round id.',
            $gameRun->getGame()->getId()
        );

        return $exception;
    }

    /**
     * @param GameRun $gameRun
     *
     * @return static
     */
    public static function createRunNotFound(GameRun $gameRun): self
    {
        $exception = new self('wrong_run');
        $exception->details = sprintf(
            'Requested not existing gameRunId=%s.',
            $gameRun->getId()
        );

        return $exception;
    }

    /**
     * @param GameRun $gameRun
     *
     * @return static
     */
    public static function createRunResultsEntered(GameRun $gameRun): self
    {
        $exception = new self('wrong_run');
        $exception->details = sprintf(
            'Requested already has results gameRunId=%s.',
            $gameRun->getId()
        );

        return $exception;
    }

    /**
     * @param GameRun $gameRun
     *
     * @return static
     */
    public static function createRunNotActive(GameRun $gameRun): self
    {
        $exception = new self('wrong_run');
        $exception->details = sprintf(
            'Requested is not imported yet gameRunId=%s.',
            $gameRun->getId()
        );

        return $exception;
    }

    /**
     * @param GameRun $gameRun
     *
     * @return static
     */
    public static function createRunStarted(GameRun $gameRun): self
    {
        $exception = new self('run_already_started');
        $exception->details = sprintf(
            'Requested is already started gameRunId=%s.',
            $gameRun->getId()
        );

        return $exception;
    }

    /**
     * @param GameRun $gameRun
     *
     * @return static
     */
    public static function createBetOnNextRun(GameRun $gameRun): self
    {
        $exception = new self('wrong_run');
        $exception->details = sprintf(
            'Requested next run gameRunId=%s.',
            $gameRun->getId()
        );

        return $exception;
    }

    /**
     * @param Game $game
     *
     * @return static
     */
    public static function createNextRunNotFound(Game $game): self
    {
        $exception = new self('wrong_run');
        $exception->details = sprintf(
            'Requested not found nextRun gameId=%s.',
            $game->getId()
        );

        return $exception;
    }

    /**
     * @param GameRun $requestedGameRun
     * @param GameRun $nextGameRun
     *
     * @return static
     */
    public static function createBetOnNotNextRun(GameRun $requestedGameRun, GameRun $nextGameRun): self
    {
        $exception = new self('wrong_run');
        $exception->details = sprintf(
            'Requested not nextRunId=%s gameRunId=%s.',
            $nextGameRun->getId(),
            $requestedGameRun->getId()
        );

        return $exception;
    }

    /**
     * @param GameRun $requestedGameRun
     * @param GameRun $currentGameRun
     *
     * @return static
     */
    public static function createBetOnNextRunWhenCurrentIsActive(
        GameRun $requestedGameRun,
        GameRun $currentGameRun
    ): self
    {
        $exception = new self('wrong_run');
        $exception->details = sprintf(
            'Requested nextRunId=%s while gameRunId=%s is active.',
            $requestedGameRun->getId(),
            $currentGameRun->getId()
        );

        return $exception;
    }

    /**
     * @param int $gameId
     * @param int $runRoundId
     *
     * @return static
     */
    public static function createRoundNotFound(int $gameId, int $runRoundId): self
    {
        $exception = new self('wrong_round');
        $exception->details = sprintf(
            'Requested runRoundId=%s not found for gameId=%s.',
            $runRoundId,
            $gameId
        );

        return $exception;
    }

    /**
     * @param RunRoundInterface $runRound
     *
     * @return static
     */
    public static function createRoundResultsEntered(RunRoundInterface $runRound): self
    {
        $exception = new self('wrong_round');
        $exception->details = sprintf(
            'Requested already has results runRoundId=%s.',
            $runRound->getId()
        );

        return $exception;
    }

    /**
     * @param RunRoundInterface $runRound
     *
     * @return static
     */
    public static function createRoundNotActive(RunRoundInterface $runRound): self
    {
        $exception = new self('wrong_round');
        $exception->details = sprintf(
            'Requested is not active yet runRoundId=%s.',
            $runRound->getId()
        );

        return $exception;
    }

    /**
     * @param RunRoundInterface $runRound
     *
     * @return static
     */
    public static function createRoundStarted(RunRoundInterface $runRound): self
    {
        $exception = new self('wrong_round');
        $exception->details = sprintf(
            'Requested already started runRoundId=%s.',
            $runRound->getId()
        );

        return $exception;
    }

    /**
     * @param BazaarRun $bazaarRun
     *
     * @return static
     */
    public static function createBazaarRunNotFound(BazaarRun $bazaarRun): self
    {
        $exception = new self('wrong_run');
        $exception->details = sprintf(
            'Requested not existing bazaarRunId=%s.',
            $bazaarRun->getId()
        );

        return $exception;
    }

    /**
     * @param BazaarRun $bazaarRun
     * @param GameRun $gameRun
     *
     * @return static
     */
    public static function createBazaarOpeningRunNotFound(BazaarRun $bazaarRun, GameRun $gameRun): self
    {
        $exception = new self('wrong_run');
        $exception->details = sprintf(
            'Requested not existing bazaarRunId=%s openingRunId=%s.',
            $bazaarRun->getId(),
            $gameRun->getId()
        );

        return $exception;
    }

    /**
     * @param BazaarRun $bazaarRun
     * @param GameRun $gameRun
     *
     * @return static
     */
    public static function createBazaarClosingRunNotFound(BazaarRun $bazaarRun, GameRun $gameRun): self
    {
        $exception = new self('wrong_run');
        $exception->details = sprintf(
            'Requested not existing bazaarRunId=%s closingRunId=%s.',
            $bazaarRun->getId(),
            $gameRun->getId()
        );

        return $exception;
    }

    /**
     * @param BazaarRun $bazaarRun
     *
     * @return static
     */
    public static function createBazaarRunResultsEntered(BazaarRun $bazaarRun): self
    {
        $exception = new self('wrong_run');
        $exception->details = sprintf(
            'Requested already has results bazaarRunId=%s.',
            $bazaarRun->getId()
        );

        return $exception;
    }

    /**
     * @param BazaarRun $bazaarRun
     *
     * @return static
     */
    public static function createBazaarRunNotWithinValidBettingTime(BazaarRun $bazaarRun): self
    {
        $exception = new self('wrong_run');
        $exception->details = sprintf(
            'Requested is not within valid betting interval bazaarRunId=%s.',
            $bazaarRun->getId()
        );

        return $exception;
    }

    /**
     * @param PlayerBetInterface $bet
     *
     * @return static
     */
    public static function createRateLimit(PlayerBetInterface $bet): self
    {
        $exception = new self('TOO_MANY_REQUESTS');
        $exception->details = sprintf(
            'Place bet too often playerId=%s.',
            $bet->getPlayer()->getId()
        );

        return $exception;
    }

    /**
     * @param float $max
     * @param PlayerBetInterface $bet
     *
     * @return static
     */
    public static function createMoreThenMax(float $max, PlayerBetInterface $bet): self
    {
        $exception = new self('cant_bet_more_than|' . $max);
        $exception->details = sprintf(
            'Requested bet amount %s, but max limit is %s.',
            $bet->getAmount(),
            $max
        );

        return $exception;
    }

    /**
     * @param float $min
     * @param PlayerBetInterface $bet
     *
     * @return static
     */
    public static function createLessThenMin(float $min, PlayerBetInterface $bet): self
    {
        $exception = new self('less_than_min_amount|' . $min);
        $exception->details = sprintf(
            'Requested bet amount %s, but min limit is %s.',
            $bet->getAmount(),
            $min
        );

        return $exception;
    }

    /**
     * @param float $min
     * @param float $max
     *
     * @return static
     */
    public static function createLimitReached(float $min, float $max): self
    {
        $exception = new self('cant_bet_during_this_run');
        $exception->details = sprintf(
            'Betting limit reached min=%s, max=%s.',
            $min,
            $max
        );

        return $exception;
    }

    /**
     * @param Odd $odd
     *
     * @return static
     */
    public static function createOddNotFound(Odd $odd): self
    {
        $exception = new self('ODDS_NOT_AVAILABLE');
        $exception->details = sprintf(
            'Requested not existing oddId=%s.',
            $odd->getId()
        );

        return $exception;
    }

    /**
     * @param Odd $odd
     * @param Game $game
     *
     * @return static
     */
    public static function createOddFromAnotherGame(Odd $odd, Game $game): self
    {
        $exception = new self('ODDS_NOT_AVAILABLE');
        $exception->details = sprintf(
            'Requested oddId=%s for run on gameId=%s, but actually it belongs to gameId=%s.',
            $odd->getId(),
            $game->getId(),
            $odd->getGame()->getId()
        );

        return $exception;
    }

    /**
     * @param Odd $odd
     * @param Bet $bet
     *
     * @return static
     */
    public static function createOddItemsCountMismatch(Odd $odd, Bet $bet): self
    {
        $exception = new self('odd_items_count_is_different');
        $exception->details = sprintf(
            'Requested %s items, but odd requires %s, oddId=%s.',
            count($bet->getItems()),
            $odd->getItemsCount(),
            $odd->getId()
        );

        return $exception;
    }

    /**
     * @param BetItem $item
     * @param Game $game
     *
     * @return static
     */
    public static function createOddItemFromAnotherGame(BetItem $item, Game $game): self
    {
        $exception = new self('items_from_other_lottery');
        $exception->details = sprintf(
            'Requested item from %s game, betting on %s game, itemId=%s.',
            $item->getGameItem()->getGame()->getId(),
            $game->getId(),
            $item->getGameItem()->getId()
        );

        return $exception;
    }

    /**
     * @param Partner $partner
     * @param Odd $odd
     *
     * @return static
     */
    public static function createOddDisabled(Partner $partner, Odd $odd): self
    {
        $exception = new self('ODDS_NOT_AVAILABLE');
        $exception->details = sprintf(
            'Requested oddId=%s is disabled or game is not enabled for partnerId=%s.',
            $odd->getId(),
            $partner->getId()
        );

        return $exception;
    }

    /**
     * @param Partner $partner
     * @param Odd $odd
     *
     * @return static
     */
    public static function createBetRepeatDisabled(Partner $partner, Odd $odd): self
    {
        $exception = new self('BET_REPEAT_NOT_AVAILABLE');
        $exception->details = sprintf(
            'Bet repeat is disabled for partnerId=%s or oddId=%s is not available.',
            $partner->getId(),
            $odd->getId(),
        );

        return $exception;
    }

    /**
     * @param Odd $odd
     *
     * @return static
     */
    public static function createBetRepeatGameNotSupported(Odd $odd): self
    {
        $exception = new self('BET_REPEAT_NOT_SUPPORTED');
        $exception->details = sprintf(
            'Game %s is not supported by bet repeat feature.',
            $odd->getGame()->getName()
        );

        return $exception;
    }

    /**
     * @param int $betId
     *
     * @return static
     */
    public static function createBetNotFound(int $betId): self
    {
        $exception = new self('BET_NOT_FOUND');
        $exception->details = sprintf(
            'Bet betId=%s is not found.',
            $betId,
        );

        return $exception;
    }

    /**
     * @param float $actualOddValue
     * @param Bet $bet
     *
     * @return static
     */
    public static function createOddValueMismatch(float $actualOddValue, Bet $bet): self
    {
        $exception = new self('ODDS_VALUE_IS_INCORRECT');
        $exception->details = sprintf(
            'Requested odd value=%s, but actual=%s for oddId=%s.',
            $bet->getOddValue(),
            $actualOddValue,
            $bet->getOdd()->getId()
        );

        return $exception;
    }

    /**
     * @param PlayerBetInterface $bet
     * @param float $winningAfterTaxes
     *
     * @return static
     */
    public static function createTaxEatenProfit(PlayerBetInterface $bet, float $winningAfterTaxes): self
    {
        $exception = new self('ODDS_NOT_AVAILABLE');
        $exception->details = sprintf(
            'Requested bet amount=%s, oddValue=%s, but after taxes winning=%s.',
            $bet->getAmount(),
            $bet->getOddsValue(),
            $winningAfterTaxes
        );

        return $exception;
    }

    /**
     * @param Subscription $bet
     *
     * @return static
     */
    public static function createSubscriptionBetWhenDisabled(Subscription $bet): self
    {
        $exception = new self('SUBSCRIPTION_DISABLED');
        $exception->details = sprintf(
            'Requested subscription but partnerId=%s disabled subscriptions.',
            $bet->getPlayer()->getPartner()->getId()
        );

        return $exception;
    }

    /**
     * @param Combination $bet
     *
     * @return static
     */
    public static function createCombinationBetWhenDisabled(Combination $bet): self
    {
        $exception = new self('COMBINATION_DISABLED');
        $exception->details = sprintf(
            'Requested combination but partnerId=%s disabled combinations.',
            $bet->getPlayer()->getPartner()->getId()
        );

        return $exception;
    }

    /**
     * @param Combination $combination
     * @param float $calculated
     *
     * @return static
     */
    public static function createCombinationOddValueMismatch(Combination $combination, float $calculated): self
    {
        $exception = new self('ODDS_VALUE_IS_INCORRECT');

        $errors = [];

        foreach ($combination->getBets() as $bet) {
            $errors[] = sprintf(
                '[id=%s, value=%s]',
                $bet->getOdd()->getId(),
                $bet->getOddValue()
            );
        }

        $exception->details = sprintf(
            'Requested odd value=%s, but actual=%s for odds=%s.',
            $combination->getOddValue(),
            $calculated,
            implode(
                ',',
                $errors
            )
        );

        return $exception;
    }

    /**
     * @param Combination $combination
     *
     * @return static
     */
    public static function createCombinationNotUniqGame(Combination $combination): self
    {
        $exception = new self('INVALID_COMBINATION');

        $exception->details = sprintf(
            'Requested combination with bets for same game odds=%s.',
            implode(
                ',',
                array_map(
                    function (Bet $bet): string {
                        return sprintf(
                            '[id=%s]',
                            $bet->getOdd()->getId()
                        );
                    },
                    $combination->getBets()->toArray()
                )
            )
        );

        return $exception;
    }

    /**
     * @param string $message
     *
     * @return static
     */
    public static function createSpeedy7Exception(string $message): self
    {
        $exception = new self($message);
        $exception->details = 'Error while processing Speedy7 bet';
        return $exception;
    }

    /**
     * @return static
     */
    public static function createRpsBetDisabled(): self
    {
        $exception = new self('RPS_BET_DISABLED');
        $exception->details = 'Betting on RPS is not available.';
        return $exception;
    }

    /**
     * @return string
     */
    public function getDetails(): string
    {
        return $this->details;
    }
}
