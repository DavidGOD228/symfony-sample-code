<?php

namespace GamesApiBundle\Service\Translation;

use Acme\Contract\BaccaratDefinition;
use Acme\Contract\GameDefinition;
use Acme\Contract\HeadsUpDefinition;
use Acme\Contract\PokerDefinition;
use Acme\Contract\WarDefinition;
use Acme\MatkaOutcomeCalculator\MatkaOutcomeCalculator;
use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyTranslation\Service\Translator;
use CoreBundle\Utils\TranslationKey;
use GamesApiBundle\Service\Gamification\RequestValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class TranslationProvider
 */
class TranslationProvider
{
    // Only some of games requires short odds.
    // Not adding all games to avoid extra data sent to FE which is not used.
    private const SHORT_ODDS_GAMES = [
        GameDefinition::DICE_DUEL,
        GameDefinition::WHEEL,
        GameDefinition::MATKA,
    ];

    private const WEBAPI_TRANSLATABLE_ERRORS = [
        '701',
        '702',
        '703',
        '704',
        '705',
        '711',
        '714',
        '715',
        '750',
        '751',
        '752',
        '780',
        '790',
        '791',
        '792',
        '793',
    ];

    private Translator $translator;
    private RulesProvider $rulesProvider;
    private OddProvider $oddProvider;
    private NotificationProvider $notificationProvider;

    /**
     * TranslationProvider constructor.
     *
     * @param TranslatorInterface $translator
     * @param RulesProvider $rulesProvider
     * @param OddProvider $oddProvider
     * @param NotificationProvider $notificationProvider
     */
    public function __construct(
        TranslatorInterface $translator,
        RulesProvider $rulesProvider,
        OddProvider $oddProvider,
        NotificationProvider $notificationProvider
    )
    {
        $this->rulesProvider = $rulesProvider;
        $this->translator = $translator;
        $this->oddProvider = $oddProvider;
        $this->notificationProvider = $notificationProvider;
    }

    /**
     * @param string $languageCode
     *
     * @return array
     */
    public function getIframeTranslations(string $languageCode): array
    {
        $this->translator->setClient('iframe');
        $this->translator->setLocale($languageCode);
        $odds = $this->oddProvider->getOdds();

        $translations = [
            'strings' => $this->getGeneral(),
            'colors' => [
                'red' => $this->t('red'),
                'black' => $this->t('black'),
                'grey' => $this->t('grey'),
            ],
            'statuses' => [
                'lost' => $this->t('lost'),
                'active' => $this->t('active'),
                'push' => $this->t('push'),
                'returned' => $this->t('returned'),
                'won' => $this->t('won'),
            ],
            'betStatuses' => [
                Bet::STATUS_ACTIVE => $this->t('active'),
                Bet::STATUS_WON => $this->t('won'),
                Bet::STATUS_LOST => $this->t('lost'),
                Bet::STATUS_RETURNED => $this->t('returned'),
                Bet::STATUS_TIE => $this->t('push'),
            ],
            'favorites' => [
                'add' => $this->t('add to favorites'),
                'remove' => $this->t('remove from favorites'),
            ],
            'resultsAbbr' => [
                'player' => $this->t('p'),
                'dealer' => $this->t('d'),
                'banker' => $this->t('b'),
            ],
            'baccaratWinners' => [
                'player' => $this->t('player'),
                'banker' => $this->t('banker'),
                'tie' => $this->t('tie'),
            ],
            'matka' => [
                'possible_odds' => $this->t('matka.possible_odds'),
                'bazaar' => $this->t('matka.bazaar'),
                'bazaar_canceled' => $this->t('matka.bazaar_canceled'),
                'bazaar_results' => $this->t('matka.bazaar_results'),
                'opening' => $this->t('matka.bazaar_opening_run'),
                'closing' => $this->t('matka.bazaar_closing_run'),
                'open_pana' => $this->t('matka.open_pana'),
                'close_pana' => $this->t('matka.close_pana'),
                'pana' => $this->t('matka.pana'),
                'upcoming' => $this->t('matka.upcoming'),
                'closed' => $this->t('matka.closed'),
                'opens_in' => $this->t('matka.opens_in'),
                'no_bazaars' => $this->t('matka.no_bazaars'),
            ],
            'matkaResults' => [
                'single' => $this->t('matka.' . MatkaOutcomeCalculator::PANA_TYPE_SINGLE),
                'double' => $this->t('matka.' . MatkaOutcomeCalculator::PANA_TYPE_DOUBLE),
                'triple' => $this->t('matka.' . MatkaOutcomeCalculator::PANA_TYPE_TRIPLE),
            ],
            'bazaarNames' => [
                'milan_morning' => $this->t('matka.bazaar_milan_morning'),
                'madhur_matinee' => $this->t('matka.bazaar_madhur_matinee'),
                'starline_sunset' => $this->t('matka.bazaar_starline_sunset'),
                'tara_twilight' => $this->t('matka.bazaar_tara_twilight'),
                'navratna_night' => $this->t('matka.bazaar_navratna_night'),
                'ruby_midnight' => $this->t('matka.bazaar_ruby_midnight'),
            ],
            'warWinners' => [
                'dealer' => $this->t('dealer'),
                'player' => $this->t('player'),
                'war' => $this->t('war'),
            ],
            'headsupWinners' => [
                'dealer' => $this->t('dealer'),
                'player' => $this->t('player'),
                'split' => $this->t('split'),
            ],
            'wheelStatistics' => [
                'last_results' => $this->t('wheel.last_results'),
                'frequency' => $this->t('wheel.frequency'),
                'numbers' => $this->t('wheel.numbers'),
                'colors' => $this->t('wheel.colors'),
            ],
            'errors' => $this->getErrors(),
            'videoQualities' => [
                'Auto' => $this->t('auto'),
                'Off' => $this->t('off'),
                'On' => $this->t('on'),
            ],
            'rounds' => [
                GameDefinition::POKER => [
                    PokerDefinition::ROUND_BET => $this->tPokerRound(PokerDefinition::ROUND_BET),
                    PokerDefinition::ROUND_PREFLOP => $this->tPokerRound(PokerDefinition::ROUND_PREFLOP),
                    PokerDefinition::ROUND_FLOP => $this->tPokerRound(PokerDefinition::ROUND_FLOP),
                    PokerDefinition::ROUND_TURN => $this->tPokerRound(PokerDefinition::ROUND_TURN),
                    PokerDefinition::ROUND_RIVER => $this->tPokerRound(PokerDefinition::ROUND_RIVER),
                ],
                GameDefinition::BACCARAT => [
                    BaccaratDefinition::ROUND_BET => $this->tBaccaratRound(BaccaratDefinition::ROUND_BET),
                    BaccaratDefinition::ROUND_PLAYER => $this->tBaccaratRound(BaccaratDefinition::ROUND_PLAYER),
                    BaccaratDefinition::ROUND_BANKER => $this->tBaccaratRound(BaccaratDefinition::ROUND_BANKER),
                ],
                GameDefinition::WAR => [
                    WarDefinition::ROUND_BET => $this->tWarRound(WarDefinition::ROUND_BET),
                    WarDefinition::ROUND_PLAYER => $this->tWarRound(WarDefinition::ROUND_PLAYER),
                ],
                GameDefinition::HEADSUP => [
                    HeadsUpDefinition::ROUND_BET => $this->tHeadsUpRound(HeadsUpDefinition::ROUND_BET),
                    HeadsUpDefinition::ROUND_PREFLOP => $this->tHeadsUpRound(HeadsUpDefinition::ROUND_PREFLOP),
                    HeadsUpDefinition::ROUND_FLOP => $this->tHeadsUpRound(HeadsUpDefinition::ROUND_FLOP),
                    HeadsUpDefinition::ROUND_TURN => $this->tHeadsUpRound(HeadsUpDefinition::ROUND_TURN),
                    HeadsUpDefinition::ROUND_RIVER => $this->tHeadsUpRound(HeadsUpDefinition::ROUND_RIVER),
                ],
            ],
            'rules' => $this->getRules(),
            'promotions' => [
                'speedy7_jackpot.title' => $this->t('speedy7 jackpot'),
                'poker_headsup_jackpot.title' => $this->t('6+ jackpot'),
                'cashback.title' => $this->t('cashback'),
            ],
            'headsupCombinations' => $this->getHeadsUpCombinations(),
            'pokerCombinations' => $this->getPokerCombinations(),
            'odds' => $this->getOdds($odds),
            'shortOdds' => $this->getShortOdds($odds),
            'oddGroups' => $this->getOddGroups(),
            'notifications' => $this->getNotifications(),
            'gameNames' => $this->getGameNames(),
            'dates' => [
                'years' => $this->t('years'),
                'months' => $this->t('months'),
                'weeks' => $this->t('weeks'),
                'days' => $this->t('days'),
                'hours' => $this->t('hours'),
                'minutes' => $this->t('minutes'),
                'seconds' => $this->t('seconds'),
            ],
            'gamification' => [
                'lvl' => $this->t('gamification.lvl'),
                'welcome_slide1_title' => $this->t('gamification.welcome_slide1_title'),
                'welcome_slide1_text' => $this->t('gamification.welcome_slide1_text'),
                'welcome_slide2_title' => $this->t('gamification.welcome_slide2_title'),
                'welcome_slide2_text' => $this->t('gamification.welcome_slide2_text'),
                'welcome_slide3_title' => $this->t('gamification.welcome_slide3_title'),
                'welcome_slide3_text' => $this->t('gamification.welcome_slide3_text'),
                'welcome_slide4_title' => $this->t('gamification.welcome_slide4_title'),
                'welcome_slide4_text' => $this->t('gamification.welcome_slide4_text'),
                'your_nickname' => $this->t('gamification.your_nickname'),
                'start_journey' => $this->t('gamification.start_journey'),
                'should_not_be_blank' => $this->t('gamification.should_not_be_blank'),
                'wrong_length' => $this->t('gamification.wrong_length'),
                'invalid_characters' => $this->t('gamification.invalid_characters'),
                'not_unique' => $this->t('gamification.not_unique'),
                'achievements' => $this->t('gamification.achievements'),
                'challenges' => $this->t('gamification.challenges'),
                'empty_achievements' => $this->t('gamification.empty_achievements'),
                'empty_challenges' => $this->t('gamification.empty_challenges'),
                'go_to_challenges' => $this->t('gamification.go_to_challenges'),
                'empty_inbox' => $this->t('gamification.empty_inbox'),
                'level_up' => $this->t('gamification.level_up'),
                'new_message' => $this->t('gamification.new_message'),
                'challenge_complete' => $this->t('gamification.challenge_complete'),
                'profile_on' => $this->t('gamification.profile_on'),
                'profile_off' => $this->t('gamification.profile_off'),
                'delete_profile' => $this->t('gamification.delete_profile'),
                'deactivate_profile' => $this->t('gamification.deactivate_profile'),
                'keep_profile' => $this->t('gamification.keep_profile'),
                'activate' => $this->t('gamification.activate'),
                'activate.step1.title' => $this->t('gamification.activate.step1.title'),
                'activate.step1.description' => $this->t('gamification.activate.step1.description'),
                'deactivate.step1.title' => $this->t('gamification.deactivate.step1.title'),
                'deactivate.step1.description' => $this->t('gamification.deactivate.step1.description'),
                'deactivate.step2.title' => $this->t('gamification.deactivate.step2.title'),
                'deactivate.step2.description' => $this->t('gamification.deactivate.step2.description'),
                'deactivate.step2.note.title' => $this->t('gamification.deactivate.step2.note.title'),
                'deactivate.step2.note.description' => $this->t('gamification.deactivate.step2.note.description'),
                'deactivate.step3.title' => $this->t('gamification.deactivate.step3.title'),
                'deactivate.step3.description' => $this->t('gamification.deactivate.step3.description'),
                'deactivate.step3.warning' => $this->t('gamification.deactivate.step3.warning'),
                'deactivate.step3.note.title' => $this->t('gamification.deactivate.step3.note.title'),
                'deactivate.step3.note.description' => $this->t('gamification.deactivate.step3.note.description'),
                'broken' => $this->t('gamification.broken'),
                'PROFILE_ALREADY_UNBLOCKED' => $this->t('gamification.profile_already_unblocked'),
                'PROFILE_ALREADY_BLOCKED' => $this->t('gamification.profile_already_blocked'),
                'PROFILE_ALREADY_EXISTS' => $this->t('gamification.profile_already_exists'),
                'choose_your_avatar' => $this->t('gamification.choose_your_avatar'),
                'terms_and_conditions_agree' => $this->t('gamification.terms_and_conditions_agree'),
                'terms_and_conditions' => $this->t('gamification.terms_and_conditions'),
            ],
            'chat' => [
                'chat_host' => $this->t('chat.chat_host'),
                'chat_help' => $this->t('chat.chat_help'),
                'game_assistant' => $this->t('chat.game_assistant'),
                'you' => $this->t('chat.you'),
                'type_your_question' => $this->t('chat.type_your_question'),
                'send' => $this->t('chat.send'),
                'message_sent' => $this->t('chat.message_sent'),
                'check_for_new_answers' => $this->t('chat.check_for_new_answers'),
            ],
        ];

        foreach (RequestValidator::BLOCK_REASONS_KEYS as $key) {
            $translations['gamification']['disable_reason.' . $key] = $this->t(
                'gamification.disable.reason.' . $key
            );
        }

        return $translations;
    }

    /**
     * @param string $languageCode
     *
     * @return array
     */
    public function getWidgetTranslations(string $languageCode): array
    {
        $this->translator->setClient('widget');
        $this->translator->setLocale($languageCode);

        $translations = [
            'strings' => [
                'Amount' => $this->t('amount'),
                'live' => $this->t('live'),
                'place_bet' => $this->t('place bet'),
                'lottery_is_returned' => $this->t('lottery_is_returned'),
                'dealing_cards' => $this->t('please wait, dealing cards'),
                'waiting_for_results' => $this->t('waiting for results...'),
                'place_bets' => $this->t('bets are accepted'),
                'bet_history' => $this->t('bets'),
                'maintenance' => $this->t('game is under maintenance'),
                'how_to_play' => $this->t('how to play?'),
                'amount_should_be_positive' => $this->e('amount_should_be_positive'),
                'make_a_choice' => $this->t('make_a_choice'),
                'no_recent_bets' => $this->t('you have no bets'),
                'not_available' => $this->t('not available'),
                'bet_success' => $this->t('Bet successfull.'),
                'try_again' => $this->t('try again'),
                'recent_bets' => $this->t('bought tickets'),
                'bet_slip_empty_choose' => $this->t('please choose 1 bet from the list'),
                'please_login' => $this->t('in order to place bets, please login.'),
                'choose_bet_amount' => $this->t('choose_bet_amount'),
                'bet_amount' => $this->t('bet_amount'),
                'draw' => $this->t('draw'),
                'bet' => $this->t('bet'),
                'tie' => $this->t('tie'),
                'possible_winning' => $this->t('possible win'),
            ],
            'war_choices' => [
                'dealer' => $this->t('dealer'),
                'player' => $this->t('player'),
                'war' => $this->t('war'),
            ],
            'statuses' => [
                'lost' => $this->t('lost'),
                'active' => $this->t('active'),
                'push' => $this->t('push'),
                'returned' => $this->t('returned'),
                'won' => $this->t('won'),
            ],
            'gameNames' => $this->getGameNames(),
            'errors' => $this->getErrors(),
        ];

        return $translations;
    }

    /**
     * @return array
     */
    private function getGeneral(): array
    {
        $translations = [
            'Date' => $this->t('date'),
            'Game' => $this->t('game'),
            'Draw' => $this->t('draw'),
            'Bet' => $this->t('bet'),
            'Odd' => $this->t('odd'),
            'Result' => $this->t('result'),
            'Amount' => $this->t('amount'),
            'Won' => $this->t('won'),
            'Time' => $this->t('time'),
            'War' => $this->t('war'),
            'Tie' => $this->t('tie'),
            'Video' => $this->t('video'),
            'Watch' => $this->t('watch'),
            'bet_history' => $this->t('bets'),
            'cancelled' => $this->t('draw was canceled.'),
            'bet_returned' => $this->t('bet was canceled and returned due to technical problems.'),
            'active' => $this->t('active'),
            'no_bets' => $this->t('you have no bets for this date.'),
            'no_results' => $this->t('no results.'),
            'choose_another' => $this->t('choose another date or start playing now.'),
            'single' => $this->t('single'),
            'subscriptions' => $this->t('subscriptions'),
            'combinations' => $this->t('combinations'),
            'select_stake' => $this->t('select stake to play'),
            'not_found' => $this->t('page not found'),
            'bets_open' => $this->t('bets open'),
            'something_wrong' => $this->t('something went wrong'),
            'try_again' => $this->t('try again'),
            'draws' => $this->t('draws'),
            'back' => $this->t('back'),
            'back_to_list' => $this->t('back to the list'),
            'subscription_no' => $this->t('subscription no'),
            'all_games' => $this->t('all games'),
            'draw_number' => $this->t('draw number'),
            'filter' => $this->t('filter'),
            'video_does_not_exist' => $this->t('video does not exist.'),
            'video_inactivity' => $this->t('video_inactivity'),
            'bonus' => $this->t('bonus'),
            'remaining' => $this->t('remaining'),
            'promotions' => $this->t('promotions'),
            'no_promotions' => $this->t('no new promotions'),
            'lobby' => $this->t('lobby'),
            'results' => $this->t('results'),
            'how_to_play' => $this->t('how to play?'),
            'dealers' => $this->t('our dealers'),
            'balance' => $this->t('balance'),
            'favorites' => $this->t('favorites'),
            'recent_bets' => $this->t('bought tickets'),
            'place_bet' => $this->t('place bet'),
            'confirm' => $this->t('confirm'),
            'cancel' => $this->t('cancel'),
            'save' => $this->t('save'),
            'bet_slip' => $this->t('betting cart'),
            'bet_slip_empty' => $this->t('bet slip is empty'),
            'bet_slip_empty_choose' => $this->t('please choose 1 bet from the list'),
            'possible_winning' => $this->t('possible win'),
            'add_one_more_bet' => $this->t('+add one more bet'),
            'games_for_combo' => $this->t('games available for combination'),
            'total_odd' => $this->t('total odd'),
            'amount' => $this->t('amount'),
            'total_amount' => $this->t('total amount'),
            'number_of_draws' => $this->t('number of draws'),
            'combination' => $this->t('combination'),
            'bet_inprocess' => $this->t('please wait, we process your bet'),
            'please_login' => $this->t('in order to place bets, please login.'),
            'ok' => $this->t('ok'),
            'accepted_upcoming_draw' => $this->t('bet will be accepted for the upcoming draw'),
            'will_be_accepted_for' => $this->t('will_be_accepted_for'),
            'place_bets' => $this->t('bets are accepted'),
            'dealing_cards' => $this->t('please wait, dealing cards'),
            'dealing' => $this->t('dealing'),
            'draw_returned' => $this->t('lottery_is_returned'),
            'banker_pair' => $this->t('banker pair'),
            'player_pair' => $this->t('player pair'),
            'natural' => $this->t('natural'),
            'history' => $this->t('history'),
            'lottery_is_returned' => $this->t('lottery_is_returned'),
            'won_message' => $this->t('won'),
            'last_results_title' => $this->t('last 5 draws'),
            'maintenance' => $this->t('game is under maintenance'),
            'live_starts_in' => $this->t('video starts in'),
            'live' => $this->t('live'),
            'win' => $this->t('win'),
            'tax' => $this->t('tax'),
            'payout_tax' => $this->t('payout tax'),
            'run_started' => $this->t('run started'),
            'waiting_for_results' => $this->t('waiting for results...'),
            'rerun_spin' => $this->t('repeating spin'),
            'rerun_spin_rules' => $this->t('rules section 4.2'),
            'rerun_roll' => $this->t('repeating roll'),
            'rerun_roll_rules' => $this->t('rules section 5.2'),
            'shuffle_deck' => $this->t('after this draw the shoe will be shuffled'),
            'top_won_title' => $this->t('top 5 won amounts'),
            'odds_have_changed' => $this->t('odds have been changed'),
            'pick_your_guess' => $this->t('pick your guess'),
            'stake' => $this->t('stake'),
            'oops_bad_luck' => $this->t('oops, bad luck'),
            'oops' => $this->t('oops'),
            'empty_content' => $this->t('how_to_play_no_content'),
            'missed_round' => $this->t('you missed your time to guess. cashed out'),
            'game_started' => $this->t('game started'),
            'game_stopped' => $this->t('game stopped. cashed out'),
            'dealing_the_card' => $this->t('please wait, dealing the card'),
            'clear' => $this->t('clear'),
            'cashout' => $this->t('cashout'),
            'correct_guess' => $this->t('your guess is correct'),
            'not_logged_in' => $this->t('you are not logged in'),
            'sound' => $this->t('sound'),
            'quality' => $this->t('quality'),
            'hand_rankings' => $this->t('hand rankings'),
            'jackpot' => $this->t('jackpot'),
            'royal_flush' => $this->t('royal flush'),
            'straight_flush' => $this->t('straight flush'),
            'jackpot_won' => $this->t('jackpot won'),
            'min_amount' => $this->t('min amount is'),
            'full_game_rules' => $this->t('full game description & rules'),
            'close_tutorial' => $this->t('close tutorial'),
            'PROFIT_LESS_THAN_BET' => $this->t('profit_less_than_bet'),
            'rotate_your_screen' => $this->t('rotate_your_screen'),
            'add_random' => $this->t('add_random'),
            'tip' => $this->t('tip'),
            'try' => $this->t('try'),
            'try_switch_to_gui' => $this->t('try_switch_to_gui'),
            'not_available_in_progress' => $this->t('not_available_in_progress'),
            'bet_success' => $this->t('Bet successfull.'),
            'video_starts_in' => $this->t('video_starts_in'),
            'betting_open' => $this->t('betting_open'),
            'close' => $this->t('close'),
            'rng_introduction' => $this->t('rng_introduction'),
            'last' => $this->t('last'),
            'choose_bet_amount' => $this->t('choose_bet_amount'),
            'bet_amount' => $this->t('bet_amount'),
            'silver' => $this->t('silver'),
            'gold' => $this->t('gold'),
            'make_a_choice' => $this->t('make_a_choice'),
            'coming_soon' => $this->t('coming_soon'),
            'non_stop_action' => $this->t('non_stop_action'),
            'last_x_draws' => $this->t('last_x_draws'),
            'math.odd' => $this->t('math.odd'),
            'math.even' => $this->t('math.even'),
            'cup' => $this->t('cup'),
            'starts_in' => $this->t('starts_in'),
            'lotto_reload' => $this->t('lotto_reload'),
            'cctv' => $this->t('cctv'),
            'select_date' => $this->t('select_date'),
            'choose_another_date' => $this->t('choose_another_date'),
            // TODO Duplicated 'possible_odds'. Remove after https://jira.Acme.tv/browse/FTL-5774
            'possible_odds' => $this->t('matka.possible_odds'),
            'show_cards' => $this->t('show_cards'),
        ];

        return $translations;
    }

    /**
     * @return array
     */
    private function getErrors(): array
    {
        $translations = [
            'amount_should_be_positive' => $this->e('amount_should_be_positive'),
            'TOO_MANY_REQUESTS' => $this->e('TOO MANY REQUESTS'),
            'ROUND_IS_NOT_OPEN_FOR_BETTING' => $this->e('ROUND IS NOT OPEN FOR BETTING'),
            'ODDS_NOT_AVAILABLE' => $this->e('ODDS NOT AVAILABLE'),
            'ODDS_VALUE_IS_INCORRECT' => $this->e('ODDS VALUE IS INCORRECT'),
            'PLAYER_ALREADY_HAS_ACTIVE_STREAK' => $this->e('PLAYER ALREADY HAS ACTIVE STREAK'),
            'BET_AMOUNT_DIFFERS_AFTER_ROUNDING' => $this->e('BET AMOUNT DIFFERS AFTER ROUNDING'),
            'MIN_BET_AMOUNT_NOT_REACHED' => $this->e('MIN BET AMOUNT NOT REACHED'),
            'BET_GREATER_THAN_MAX_BET_AMOUNT' => $this->e('BET GREATER THAN MAX BET AMOUNT'),
            'POTENTIAL_WINNING_GREATER_THAN_LIMIT' => $this->e('POTENTIAL WINNING GREATER THAN LIMIT'),
            'PAY_IN_PERSISTING_FAILED' => $this->e('PAY IN PERSISTING FAILED'),
            'transaction_failed_bet_timeout' => $this->e('TRANSACTION FAILED BET TIMEOUT'),
            'transaction_failed_bet_not_accepted' => $this->e('TRANSACTION FAILED BET NOT ACCEPTED'),
            'STREAK_IS_CALCULATING' => $this->e('STREAK IS CALCULATING'),
            'CHANGING_ODDS_IS_FORBIDDEN' => $this->e('CHANGING ODDS IS FORBIDDEN'),
            'ROUND_IS_SKIPPED' => $this->e('ROUND IS SKIPPED'),
            'PLACE_BET_PERSISTING_FAILED' => $this->e('PLACE BET PERSISTING FAILED'),
            'NO_ACTIVE_STREAK' => $this->e('NO ACTIVE STREAK'),
            'PAYOUT_RESTRICTED_DURING_CARD_DEALING' => $this->e('PAYOUT RESTRICTED DURING CARD DEALING'),
            'STREAK_IS_PROCESSING' => $this->e('STREAK IS PROCESSING'),
            'PAYOUT_OF_NEW_STREAK_IS_NOT_ALLOWED' => $this->e('PAYOUT OF NEW STREAK IS NOT ALLOWED'),
            'This field is missing.' => $this->e('value is missing'),
            'This value should not be null.' => $this->e('value should not be null'),
            'This value should be of type int.' => $this->e('value should be of type int'),
            'This value should be of type string.' => $this->e('value should be of type string'),
            '[runId]' => $this->t('run id'),
            '[roundId]' => $this->t('round id'),
            '[amount]' => $this->t('amount'),
            '[oddId]' => $this->t('odd id'),
            '[oddValue]' => $this->t('odd value'),
            'GEO_BLOCKING' => $this->e('The game is not available in your country'),
            'INSUFFICIENT_FUNDS' => $this->e('INSUFFICIENT_FUNDS'),
            'INCORRECT_CURRENCY' => $this->e('INCORRECT_CURRENCY'),
            'OTHER' => $this->e('OTHER'),
            'wrong_run' => $this->e('wrong_run'),
            'wrong_round' => $this->e('wrong_round'),
            'run_already_started' => $this->e('run_already_started'),
            'cant_bet_more_than' => $this->e('cant_bet_more_than'),
            'less_than_min_amount' => $this->e('less_than_min_amount'),
            'odd_items_count_is_different' => $this->e('odd_items_count_is_different'),
            'items_from_other_lottery' => $this->e('items_from_other_lottery'),
            'RPS_BET_EXISTS_FOR_ZONE' => $this->e('RPS_BET_EXISTS_FOR_ZONE'),
        ];

        foreach (self::WEBAPI_TRANSLATABLE_ERRORS as $errorCode) {
            $translations[$errorCode] = $this->e($errorCode);
        }

        return $translations;
    }

    /**
     * @param Odd[] $odds
     *
     * @return array
     */
    private function getOdds(iterable $odds): array
    {
        $translations = [];
        foreach ($odds as $odd) {
            $oddId = $odd->getId();
            $translationKey = TranslationKey::getOdds($odd);
            $translations[$oddId] = $this->translator->trans($translationKey, [], 'odds');
        }

        return $translations;
    }

    /**
     * @param Odd[] $odds
     *
     * @return array
     */
    private function getShortOdds(iterable $odds): array
    {
        $translations = [];
        foreach ($odds as $odd) {
            if (!in_array($odd->getGame()->getId(), self::SHORT_ODDS_GAMES, true)) {
                continue;
            }
            $oddId = $odd->getId();
            $translationKey = TranslationKey::getShortOdds($odd);
            $translations[$oddId] = $this->translator->trans($translationKey, [], 'odds');
        }

        return $translations;
    }

    /**
     * @return array
     */
    private function getOddGroups(): array
    {
        $translations = [];

        $oddGroups = $this->oddProvider->getGroups();
        foreach ($oddGroups as $oddGroup) {
            $oddGroupId = $oddGroup->getId();
            $translations[$oddGroupId] = $this->translator->trans($oddGroupId, [], 'odd_groups');
        }

        return $translations;
    }

    /**
     * @return array
     */
    private function getHeadsUpCombinations(): array
    {
        $translations = [];
        $headsUpCombinations = $this->oddProvider->getHeadsUpCombinations();
        foreach ($headsUpCombinations as $headsUpCombination) {
            $combinationId = $headsUpCombination->getId();
            $translations[$combinationId] = $this->translator->trans($combinationId, [], 'headsup_combinations');
        }

        return $translations;
    }

    /**
     * @return array
     */
    private function getPokerCombinations(): array
    {
        $translations = [];
        $pokerCombinations = $this->oddProvider->getPokerCombinations();
        foreach ($pokerCombinations as $pokerCombination) {
            $combinationId = $pokerCombination->getId();
            $translations[$combinationId] = $this->translator->trans($combinationId, [], 'poker_combinations');
        }

        return $translations;
    }

    /**
     * @return array
     */
    private function getGameNames(): array
    {
        $translations = [];
        $gameIds = GameDefinition::getKnown();
        foreach ($gameIds as $gameId) {
            $translations[$gameId] = $this->translator->trans($gameId, [], 'games');
        }
        return $translations;
    }

    /**
     * @return array
     */
    private function getNotifications(): array
    {
        $translations = [];

        $notifications = $this->notificationProvider->get();
        foreach ($notifications as $notification) {
            $translations[$notification->getName()] = $this->translator->trans(
                $notification->getName(),
                [],
                'notifications'
            );
        }

        return $translations;
    }

    /**
     * @return array
     */
    private function getRules(): array
    {
        $translations = [];
        $rulesTexts = $this->rulesProvider->getRulesTexts();
        foreach ($rulesTexts as $rulesText) {
            $translation = $this->translator->trans($rulesText->getCode(), [], 'promotions');
            $formatted = $this->rulesProvider->format($rulesText->getCode(), $translation);
            if (!$formatted) {
                continue;
            }

            $promotion = $formatted['promotion'];
            $gameId = $formatted['gameId'];
            $index = $formatted['index'];
            $translations[$promotion][$gameId][$index] = [
                'title' => $formatted['title'],
                'description' => $formatted['description']
            ];
        }

        return $translations;
    }

    /**
     * Helper for messages translation.
     *
     * @param string $translationKey
     *
     * @return string
     */
    private function t(string $translationKey): string
    {
        return $this->translator->trans($translationKey, [], 'iframe');
    }

    /**
     * Helper for error translation.
     *
     * @param string $translationKey
     *
     * @return string
     */
    private function e(string $translationKey): string
    {
        return $this->translator->trans($translationKey, [], 'betting_error');
    }

    /**
     * @param int $roundId
     *
     * @return string
     */
    private function tPokerRound(int $roundId): string
    {
        return $this->translator->trans($roundId, [], 'poker_rounds');
    }

    /**
     * @param int $roundId
     *
     * @return string
     */
    private function tBaccaratRound(int $roundId): string
    {
        return $this->translator->trans($roundId, [], 'baccarat_rounds');
    }

    /**
     * @param int $roundId
     *
     * @return string
     */
    private function tWarRound(int $roundId): string
    {
        return $this->translator->trans($roundId, [], 'war_rounds');
    }

    /**
     * @param int $roundId
     *
     * @return string
     */
    private function tHeadsUpRound(int $roundId): string
    {
        return $this->translator->trans($roundId, [], 'headsup_rounds');
    }
}
