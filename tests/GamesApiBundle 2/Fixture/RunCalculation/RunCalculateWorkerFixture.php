<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\RunCalculation;

use Acme\SymfonyDb\Entity\BazaarBet;
use Acme\SymfonyDb\Entity\BazaarRun;
use Acme\SymfonyDb\Entity\Bet;
use Acme\SymfonyDb\Entity\Combination;
use Acme\SymfonyDb\Entity\Currency;
use Acme\SymfonyDb\Entity\Game;
use Acme\SymfonyDb\Entity\GameItem;
use Acme\SymfonyDb\Entity\GameRun;
use Acme\SymfonyDb\Entity\GameRunResult;
use Acme\SymfonyDb\Entity\GameRunResultItem;
use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyDb\Entity\Player;
use Acme\SymfonyDb\Entity\Ticket;
use Acme\SymfonyDb\Entity\Transaction;
use Acme\SymfonyDb\Type\BazaarBetType;
use Acme\SymfonyDb\Type\BetStatusType;
use Acme\SymfonyDb\Type\TicketStatusType;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class RunCalculateWorkerFixture
 */
final class RunCalculateWorkerFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $this->addRunResultItemsToLucky7Run($manager);
        $this->createTicketForLucky7($manager);
        $this->addCombination($manager);
        $this->addBazaarBets($manager);

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    private function addRunResultItemsToLucky7Run(ObjectManager $manager): void
    {
        /** @var GameRunResult $gameRunResult */
        $gameRunResult = $this->getReference('game:1:result');
        /** @var GameItem $lotteryItem1 */
        $lotteryItem1 = $this->getReference('lottery-item:1:1');

        $item1 = (new GameRunResultItem())
            ->setGameItem($lotteryItem1)
            ->setOrder(1);
        $gameRunResult->addGameRunResultItem($item1);
        $manager->persist($gameRunResult);
    }

    /**
     * @param ObjectManager $manager
     */
    private function createTicketForLucky7(ObjectManager $manager): void
    {
        /** @var Bet $bet */
        $bet = $this->getReference('bet:1:valid');
        /** @var Player $player */
        $player = $this->getReference('player:1');

        $ticket = (new Ticket())
            ->setAmount(5)
            ->setStatus(TicketStatusType::CALCULATED)
            ->setPayoutStatus(false)
            ->setBarcode('123')
            ->setPlayer($player)
            ->setCurrency($player->getCurrency())
            ->setTimeCreated(new DateTimeImmutable())
            ->addBet($bet)
        ;
        $manager->persist($ticket);
        $manager->persist($bet);
    }

    /**
     * @param ObjectManager $manager
     */
    private function addCombination(ObjectManager $manager): void
    {
        /** @var Bet $bet1 */
        $bet1 = $this->getReference('bet:1:1');
        /** @var Bet $bet2 */
        $bet2 = $this->getReference('bet:9:1');

        $combination = (new Combination())
            ->setAmount($bet1->getAmount() + $bet2->getAmount())
            ->setOddValue($bet1->getOddValue() * $bet2->getOddValue())
            ->setPlayer($bet1->getPlayer())
            ->setCurrency($bet1->getCurrency())
            ->setDateCreated(new DateTimeImmutable())
            ->setInvalid(false)
            ->addBet($bet1)
            ->addBet($bet2)
        ;

        $manager->persist($bet1);
        $manager->persist($bet2);
        $manager->persist($combination);
    }

    /**
     * @param ObjectManager $manager
     */
    private function addBazaarBets(ObjectManager $manager): void
    {
        /** @var Player $player */
        $player = $this->getReference('player:1');
        /** @var Currency $currency */
        $currency = $this->getReference('currency:eur');
        /** @var Game $game */
        $game = $this->getReference('game:18');
        /** @var Odd $odd */
        $odd = $this->getReference("odd:18:1");

        $run = (new GameRun())
            ->setCode('BazaarDraw')
            ->setIsReturned(false)
            ->setResultsEntered(true)
            ->setIsImported(true)
            ->setVideoConfirmationRequired(false)
            ->setTime(new DateTimeImmutable())
            ->setPublishedDate(new DateTimeImmutable())
            ->setGame($game)
            ->setVideoUrl(null)
        ;
        $manager->persist($run);

        $bazaarRun = (new BazaarRun())
            ->setCode('opening:closing')
            ->setTitle('Madhur evening')
            ->setOpeningRun($run)
            ->setClosingRun($run)
            ->setIsReturned(false)
        ;
        $manager->persist($bazaarRun);

        $run->setBazaarRun($bazaarRun);

        $examples = [
            [
                'isReturned' => true,
                'isPaidOut' => false,
            ],
            [
                'isReturned' => false,
                'isPaidOut' => true,
            ],
            [
                'isReturned' => false,
                'isPaidOut' => false,
            ],
        ];

        foreach ($examples as $example) {
            $transaction = new Transaction();
            $manager->persist($transaction);

            $bet = (new Bet())
                ->setPlayer($player)
                ->setCurrency($currency)
                ->setTransactionAmountBet($transaction)
                ->setGameRun($run)
                ->setOdd($odd)
                ->setOddValue(1.10)
                ->setFinalOddValue(1.10)
                ->setAmount(400)
                ->setAmountWon(453)
                ->setTimeWon(null)
                ->setStatus(BetStatusType::ACTIVE)
                ->setInvalid(false)
                ->setIsReturned($example['isReturned'])
                ->setIsPaidOut($example['isPaidOut'])
                ->setIsWidget(false)
                ->setIsMobile(false)
                ->setTime(new DateTimeImmutable())
                ->setIp('ip')
            ;

            $bazaarBet = (new BazaarBet())
                ->setBet($bet)
                ->setBazaarRun($bazaarRun)
                ->setBazaarBetType(BazaarBetType::MAIN)
                ->setValid(true)
            ;
            $manager->persist($bazaarBet);
        }

        $this->setReference('bazaar-run', $run);
    }
}
