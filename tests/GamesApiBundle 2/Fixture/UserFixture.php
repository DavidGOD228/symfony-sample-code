<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture;

use Acme\SymfonyDb\Entity\User;
use Acme\SymfonyDb\Entity\UserRole;
use Doctrine\Persistence\ObjectManager;
use SymfonyTests\Unit\CoreBundle\Fixture\AbstractCustomizableFixture;

/**
 * Class UserFixture
 */
class UserFixture extends AbstractCustomizableFixture
{
    /**
     * @var array
     */
    protected array $tables = [
        UserRole::class,
        User::class
    ];

    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function doLoad(ObjectManager $manager): void
    {
        $role = (new UserRole())
            ->setName('Test role')
            ->setDescription('');
        $manager->persist($role);

        $this->entities[] = $this->getEntity($role);

        foreach ($this->entities as $entity) {
            $manager->persist($entity);
            $this->addReference('user', $entity);
        }
        $manager->flush();
    }

    /**
     * @param UserRole $role
     *
     * @return User
     */
    private function getEntity(UserRole $role): User
    {
        $user = (new User())
            ->setUsername('demo')
            ->setRole($role)
            ->setEmail('demo@Acme.tv')
            ->setPassword('881b9d8f016ae74af3e747c2ce7252335ece8bc6')
            ->setIpAddress('127.0.0.1')
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime())
            ->setTimezone('UTC')
            ->setActive(1);

        return $user;
    }
}
