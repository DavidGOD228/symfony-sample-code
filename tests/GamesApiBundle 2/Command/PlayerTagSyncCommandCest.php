<?php

declare(strict_types=1);

namespace SymfonyTests\Unit\GamesApiBundle\Command;

use Acme\SymfonyDb\Entity\PlayerTagSource;
use Carbon\CarbonImmutable;
use CoreBundle\Repository\PlayerRepository;
use SymfonyTests\UnitTester;
use Acme\SymfonyDb\Entity\Player;
use SymfonyTests\Unit\AbstractUnitTest;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use GamesApiBundle\Command\PlayerTagSyncCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use SymfonyTests\Unit\GamesApiBundle\Fixture\PlayerTagSourceFixture;

/**
 * Class PlayerTagSyncCommandCest
 */
final class PlayerTagSyncCommandCest extends AbstractUnitTest
{
    /**
     * @var array
     */
    protected array $tables
        = [
            PlayerTagSource::class,
        ];

    protected array $fixtures
        = [
            PlayerTagSourceFixture::class,
        ];

    /**
     * {@inheritDoc}
     */
    protected function setUpFixtures(): void
    {
        CarbonImmutable::setTestNow('2020-01-03 13:00:00');
        parent::setUpFixtures();

        $this->fixtureBoostrapper->addPartners(1, true);
        $this->fixtureBoostrapper->addPlayers(1);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Codeception\Exception\ModuleException
     */
    public function testAutowiringShouldSucceed(UnitTester $I): void
    {
        $kernel = $I->getTestingKernel();
        $app = new Application($kernel);

        $command = $app->find('app:players:sync-tags');

        $I->assertInstanceOf(PlayerTagSyncCommand::class, $command);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testRunShouldWriteLogs(UnitTester $I): void
    {
        $command = $I->getService(PlayerTagSyncCommand::class);

        $command->run(new ArrayInput([]), new NullOutput());

        $logger = $I->getTestLogger();
        $logs = $logger->getRecords();

        $I->assertCount(3, $logs);
        $I->assertEquals('Player tag sync: started.', $logs[0]['message']);
        $I->assertEquals('Player tag sync: last update date: 2020-01-03T13:00:00+00:00.', $logs[1]['message']);
        $I->assertEquals('Player tag sync: finished. Synced 1 tags.', $logs[2]['message']);
    }

    /**
     * @param UnitTester $I
     *
     * @throws \Exception
     */
    public function testRunShouldUpdatePlayerTag(UnitTester $I): void
    {
        $command = $I->getService(PlayerTagSyncCommand::class);

        $playerRepository = $this->getRepositoryProvider()
            ->getMasterRepository(PlayerRepository::class);

        /** @var Player $player */
        $player = $playerRepository->find('1');
        $I->assertEquals('existing', $player->getTag());

        $command->run(new ArrayInput([]), new NullOutput());
        $playerRepository->clear();

        /** @var Player $player */
        $player = $playerRepository->find('1');
        $I->assertEquals('ExampleTag', $player->getTag());
    }
}
