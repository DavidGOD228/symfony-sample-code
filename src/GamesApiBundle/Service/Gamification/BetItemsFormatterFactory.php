<?php

declare(strict_types=1);

namespace GamesApiBundle\Service\Gamification;

use Acme\Contract\GameDefinition;
use GamesApiBundle\Service\Gamification\BetItemsFormatter\DefaultLotteryFormatter;
use GamesApiBundle\Service\Gamification\BetItemsFormatter\DiceDuelFormatter;
use GamesApiBundle\Service\Gamification\BetItemsFormatter\FormatterInterface;
use GamesApiBundle\Service\Gamification\BetItemsFormatter\Lucky6Formatter;
use GamesApiBundle\Service\Gamification\BetItemsFormatter\NullFormatter;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Class BetItemsFormatterFactory
 */
final class BetItemsFormatterFactory implements ServiceSubscriberInterface
{
    private ContainerInterface $serviceContainer;

    /**
     * GameResultsFormatterFactory constructor.
     *
     * @param ContainerInterface $serviceContainer
     */
    public function __construct(ContainerInterface $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedServices(): array
    {
        return [
            DefaultLotteryFormatter::class => DefaultLotteryFormatter::class,
            DiceDuelFormatter::class => DiceDuelFormatter::class,
            Lucky6Formatter::class => Lucky6Formatter::class,
            NullFormatter::class => NullFormatter::class,
        ];
    }

    /**
     * @param int $gameId
     *
     * @return FormatterInterface
     */
    public function getFormatter(int $gameId): FormatterInterface
    {
        switch ($gameId) {
            case GameDefinition::LUCKY_7:
            case GameDefinition::LUCKY_7_BETWAY:
            case GameDefinition::LUCKY_5:
            case GameDefinition::WHEEL:
                return $this->serviceContainer->get(DefaultLotteryFormatter::class);
            case GameDefinition::LUCKY_6:
                return $this->serviceContainer->get(Lucky6Formatter::class);
            case GameDefinition::DICE_DUEL:
                return $this->serviceContainer->get(DiceDuelFormatter::class);
        }

        return $this->serviceContainer->get(NullFormatter::class);
    }
}
