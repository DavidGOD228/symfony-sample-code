<?php

namespace GamesApiBundle\Service\Translation;

use Acme\SymfonyDb\Entity\TranslationNotification;
use CoreBundle\Service\RepositoryProviderInterface;

/**
 * Class NotificationProvider
 */
class NotificationProvider
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;

    /**
     * NotificationProvider constructor.
     *
     * @param RepositoryProviderInterface $repositoryProvider
     */
    public function __construct(RepositoryProviderInterface $repositoryProvider)
    {
        $this->repository = $repositoryProvider->getSlaveRepository(TranslationNotification::class);
    }

    /**
     * @return TranslationNotification[]
     */
    public function get(): iterable
    {
        return $this->repository->findAll();
    }
}
