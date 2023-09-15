<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\Gamification;

use Acme\Contract\GameDefinition;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\AndarBaharFormatter;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\BaccaratFormatter;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\FormatterInterface;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\HeadsUpFormatter;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\LotteryFormatter;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\NullFormatter;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\PokerFormatter;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\RpsFormatter;
use GamesApiBundle\Service\Gamification\GameResultsFormatter\WarFormatter;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Class GameResultsFormatterFactory
 */
final class GameResultsFormatterFactory implements ServiceSubscriberInterface
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
            LotteryFormatter::class => LotteryFormatter::class,
            RpsFormatter::class => RpsFormatter::class,
            PokerFormatter::class => PokerFormatter::class,
            BaccaratFormatter::class => BaccaratFormatter::class,
            AndarBaharFormatter::class => AndarBaharFormatter::class,
            HeadsUpFormatter::class => HeadsUpFormatter::class,
            WarFormatter::class => WarFormatter::class,
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
        if (GameDefinition::isLottery($gameId)) {
            return $this->serviceContainer->get(LotteryFormatter::class);
        }

        switch ($gameId) {
            case GameDefinition::POKER:
            case GameDefinition::STS_POKER:
                return $this->serviceContainer->get(PokerFormatter::class);
            case GameDefinition::BACCARAT:
                return $this->serviceContainer->get(BaccaratFormatter::class);
            case GameDefinition::HEADSUP:
                return $this->serviceContainer->get(HeadsUpFormatter::class);
            case GameDefinition::ANDAR_BAHAR:
                return $this->serviceContainer->get(AndarBaharFormatter::class);
            case GameDefinition::WAR:
                return $this->serviceContainer->get(WarFormatter::class);
            case GameDefinition::RPS:
                return $this->serviceContainer->get(RpsFormatter::class);
        }

        return $this->serviceContainer->get(NullFormatter::class);
    }
}
