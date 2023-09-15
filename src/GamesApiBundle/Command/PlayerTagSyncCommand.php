<?php

declare(strict_types=1);

namespace GamesApiBundle\Command;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use CoreBundle\Command\AbstractCommand;
use Acme\Semaphore\SemaphoreInterface;
use CoreBundle\Repository\PlayerRepository;
use MyBuilder\Bundle\CronosBundle\Annotation\Cron;
use CoreBundle\Service\RepositoryProviderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GamesApiBundle\Repository\PlayerTagSourceRepository;

/**
 * Class PlayerTagSyncCommand
 *
 * @Cron(minute="0", hour="6", server="cli")
 */
final class PlayerTagSyncCommand extends AbstractCommand
{
    protected const LOCK_TTL = 1;

    /**
     * @var string
     */
    protected static $defaultName = 'app:players:sync-tags';
    protected const BATCH_SIZE = 1000;

    private PlayerRepository $playerRepository;
    private PlayerTagSourceRepository $playerTagSourceRepository;
    private EntityManagerInterface $entityManager;

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Updating players tag from source table');
    }

    /**
     * PlayerTagSyncCommand constructor.
     *
     * @param LoggerInterface $logger
     * @param SemaphoreInterface $semaphore
     * @param RepositoryProviderInterface $repositoryProvider
     */
    public function __construct(
        LoggerInterface $logger,
        SemaphoreInterface $semaphore,
        RepositoryProviderInterface $repositoryProvider
    )
    {
        parent::__construct($logger, $semaphore);

        $this->playerTagSourceRepository = $repositoryProvider
            ->getMasterRepository(PlayerTagSourceRepository::class);
        $this->playerRepository = $repositoryProvider
            ->getMasterRepository(PlayerRepository::class);
        $this->entityManager = $repositoryProvider->getMasterEntityManager();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * Not using output because don't have ways how to get it.
     * phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundInExtendedClassAfterLastUsed
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->info('Player tag sync: started.');
        $lastTagTimestamp = $this->playerRepository
            ->getMaxTaggedAtDateTime();


        $this->logger->info(
            sprintf(
                'Player tag sync: last update date: %s.',
                $lastTagTimestamp->format(DateTimeInterface::ATOM)
            )
        );


        $batchOffsetId = 0;
        $syncedTagCount = 0;

        $newTagTimestamp = CarbonImmutable::now();

        while (true) {
            $tagsToUpdate = $this->playerTagSourceRepository
                ->findAllNewTags($newTagTimestamp, self::BATCH_SIZE, $batchOffsetId);

            if (!count($tagsToUpdate)) {
                break;
            }

            foreach ($tagsToUpdate as $tag) {
                $this->playerRepository->updateTag($tag, $lastTagTimestamp);
                $syncedTagCount++;
                $batchOffsetId = $tag->getId();
            }

            $this->entityManager->clear();
            gc_collect_cycles();
        }

        $this->logger->info(sprintf('Player tag sync: finished. Synced %d tags.', $syncedTagCount));

        return self::CODE_OK;
    }
}
