<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture;

use Acme\SymfonyDb\Entity\PartnerLogoLink;
use Doctrine\Persistence\ObjectManager;
use SymfonyTests\Unit\CoreBundle\Fixture\AbstractCustomizableFixture;

/**
 * Class PartnerLogoLinkFixture
 */
class PartnerLogoLinkFixture extends AbstractCustomizableFixture
{
    /**
     * @var array
     */
    protected array $tables = [
        PartnerLogoLink::class
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
            $this->addReference('partnerLogoLink', $entity);
        }
        $manager->flush();
    }

    /**
     * @return PartnerLogoLink
     */
    private function getEntity(): PartnerLogoLink
    {
        /* @var \Acme\SymfonyDb\Entity\Logo $logo */
        $logo = $this->getReference('logo');
        /* @var \Acme\SymfonyDb\Entity\Partner $partner */
        $partner = $this->getReference('partner:1');

        $partnerLogoLink = (new PartnerLogoLink())
            ->setPartner($partner)
            ->setIsOverridingDefault(0)
            ->setLogo($logo)
            ->setLogoTypeId(6);

        return $partnerLogoLink;
    }
}
