<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture;

use Acme\SymfonyDb\Entity\Logo;
use Carbon\Carbon;
use Doctrine\Persistence\ObjectManager;
use SymfonyTests\Unit\CoreBundle\Fixture\AbstractCustomizableFixture;

/**
 * Class LogoFixture
 */
class LogoFixture extends AbstractCustomizableFixture
{
    /**
     * @var array
     */
    protected array $tables = [
        Logo::class
    ];

    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function doLoad(ObjectManager $manager): void
    {
        $this->entities[] = $this->getEntity();

        foreach ($this->entities as $entity) {
            $manager->persist($entity);
            $this->addReference('logo', $entity);
        }
        $manager->flush();
    }

    /**
     * @return Logo
     * @throws \Exception
     */
    private function getEntity(): Logo
    {
        /* @var \Acme\SymfonyDb\Entity\User $user */
        $user = $this->getReference('user');

        $logo = (new Logo())
            ->setName('logo')
            ->setLogoTypeId(6)
            ->setUrl('/design/images/company_logos/Acme.jpg')
            ->setPublishedDate(Carbon::now())
            ->setPublishedBy($user);

        return $logo;
    }
}
