<?php

namespace GamesApiBundle\Service\Translation;

use Acme\SymfonyDb\Entity\HeadsUpCombination;
use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyDb\Entity\OddGroup;
use Acme\SymfonyDb\Entity\PokerCombination;
use CoreBundle\Service\RepositoryProviderInterface;

/**
 * Class OddProvider
 */
class OddProvider
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $oddRepository;
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $pokerRepository;
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $headsUpRepository;
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $groupsRepository;

    /**
     * OddProvider constructor.
     *
     * @param RepositoryProviderInterface $repositoryProvider
     */
    public function __construct(RepositoryProviderInterface $repositoryProvider)
    {
        $this->oddRepository = $repositoryProvider->getSlaveRepository(Odd::class);
        $this->pokerRepository = $repositoryProvider->getSlaveRepository(PokerCombination::class);
        $this->headsUpRepository = $repositoryProvider->getSlaveRepository(HeadsUpCombination::class);
        $this->groupsRepository = $repositoryProvider->getSlaveRepository(OddGroup::class);
    }

    /**
     * @return Odd[]
     */
    public function getOdds(): iterable
    {
        $odds = $this->oddRepository->findAll();
        return $odds;
    }

    /**
     * @return PokerCombination[]
     */
    public function getPokerCombinations(): iterable
    {
        $pokerCombinations = $this->pokerRepository->findAll();
        return $pokerCombinations;
    }

    /**
     * @return HeadsUpCombination[]
     */
    public function getHeadsUpCombinations(): iterable
    {
        $headsUpCombinations = $this->headsUpRepository->findAll();
        return $headsUpCombinations;
    }

    /**
     * @return OddGroup[]
     */
    public function getGroups(): iterable
    {
        $groups = $this->groupsRepository->findAll();
        return $groups;
    }
}
