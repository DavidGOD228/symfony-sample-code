<?php

declare(strict_types = 1);

namespace GamesApiBundle\Service\GameRunResults;

use Acme\Contract\GameDefinition;
use GamesApiBundle\Service\GameRunResults\Formatter\AndarBaharFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\BaccaratFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\FormatterInterface;
use GamesApiBundle\Service\GameRunResults\Formatter\HeadsUpFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\LotteryFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\MatkaBazaarFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\NullFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\PokerFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\RpsFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\Speedy7Formatter;
use GamesApiBundle\Service\GameRunResults\Formatter\StsPokerFormatter;
use GamesApiBundle\Service\GameRunResults\Formatter\WarOfBetsFormatter;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Class FormatterFactory
 */
final class FormatterFactory implements ServiceSubscriberInterface
{
    private ContainerInterface $serviceContainer;

    /**
     * FormatterFactory constructor.
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
            Speedy7Formatter::class => Speedy7Formatter::class,
            RpsFormatter::class => RpsFormatter::class,
            PokerFormatter::class => PokerFormatter::class,
            StsPokerFormatter::class => StsPokerFormatter::class,
            BaccaratFormatter::class => BaccaratFormatter::class,
            AndarBaharFormatter::class => AndarBaharFormatter::class,
            HeadsUpFormatter::class => HeadsUpFormatter::class,
            WarOfBetsFormatter::class => WarOfBetsFormatter::class,
            MatkaBazaarFormatter::class => MatkaBazaarFormatter::class,
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
            case GameDefinition::STS_POKER:
                return $this->serviceContainer->get(StsPokerFormatter::class);
            case GameDefinition::POKER:
                return $this->serviceContainer->get(PokerFormatter::class);
            case GameDefinition::BACCARAT:
                return $this->serviceContainer->get(BaccaratFormatter::class);
            case GameDefinition::HEADSUP:
                return $this->serviceContainer->get(HeadsUpFormatter::class);
            case GameDefinition::ANDAR_BAHAR:
                return $this->serviceContainer->get(AndarBaharFormatter::class);
            case GameDefinition::WAR:
                return $this->serviceContainer->get(WarOfBetsFormatter::class);
            case GameDefinition::SPEEDY7:
                return $this->serviceContainer->get(Speedy7Formatter::class);
            case GameDefinition::RPS:
                return $this->serviceContainer->get(RpsFormatter::class);
            case GameDefinition::MATKA:
                return $this->serviceContainer->get(MatkaBazaarFormatter::class);
        }

        // Using NullFormatter instead of exception to not break other games processing.
        return $this->serviceContainer->get(NullFormatter::class);
    }
}
