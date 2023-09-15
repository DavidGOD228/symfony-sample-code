<?php

namespace SymfonyTests\Unit\GamesApiBundle\Fixture\Matka;

use Acme\SymfonyDb\Entity\GroupingOdd;
use Acme\SymfonyDb\Entity\Odd;
use Acme\SymfonyDb\Entity\OddGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Class GroupingOddFixture
 */
class GroupingOddFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        /** @var Odd $odd1 */
        $odd1 = $this->getReference('matka-odd:1');
        /** @var Odd $odd2 */
        $odd2 = $this->getReference('matka-odd:2');
        /** @var Odd $odd3 */
        $odd3 = $this->getReference('matka-odd:3');
        /** @var Odd $odd4 */
        $odd4 = $this->getReference('matka-odd:4');
        /** @var Odd $odd5 */
        $odd5 = $this->getReference('matka-odd:5');
        /** @var Odd $odd6 */
        $odd6 = $this->getReference('matka-odd:6');
        /** @var OddGroup $oddGroup1 */
        $oddGroup1 = $this->getReference('matka-odd-group:1');
        /** @var OddGroup $oddGroup2 */
        $oddGroup2 = $this->getReference('matka-odd-group:2');
        /** @var OddGroup $oddGroup3 */
        $oddGroup3 = $this->getReference('matka-odd-group:3');
        /** @var OddGroup $oddGroup4 */
        $oddGroup4 = $this->getReference('matka-odd-group:4');
        /** @var OddGroup $oddGroup51 */
        $oddGroup51 = $this->getReference('matka-odd-group:51');
        /** @var OddGroup $oddGroup52 */
        $oddGroup52 = $this->getReference('matka-odd-group:52');
        /** @var OddGroup $oddGroup61 */
        $oddGroup61 = $this->getReference('matka-odd-group:61');
        /** @var OddGroup $oddGroup71 */
        $oddGroup71 = $this->getReference('matka-odd-group:71');
        /** @var OddGroup $oddGroup62 */
        $oddGroup62 = $this->getReference('matka-odd-group:62');
        /** @var OddGroup $oddGroup72 */
        $oddGroup72 = $this->getReference('matka-odd-group:72');

        $groupingOdd1 = (new GroupingOdd())
            ->setOdd($odd1)
            ->setGroup($oddGroup1)
            ->setOrder(1);
        $manager->persist($groupingOdd1);

        $groupingOdd2 = (new GroupingOdd())
            ->setOdd($odd2)
            ->setGroup($oddGroup2)
            ->setOrder(2);
        $manager->persist($groupingOdd2);

        $groupingOdd3 = (new GroupingOdd())
            ->setOdd($odd3)
            ->setGroup($oddGroup3)
            ->setOrder(3);
        $manager->persist($groupingOdd3);

        $groupingOdd4 = (new GroupingOdd())
            ->setOdd($odd4)
            ->setGroup($oddGroup4)
            ->setOrder(4);
        $manager->persist($groupingOdd4);

        $groupingOdd51 = (new GroupingOdd())
            ->setOdd($odd5)
            ->setGroup($oddGroup51)
            ->setOrder(5);
        $manager->persist($groupingOdd51);

        $groupingOdd52 = (new GroupingOdd())
            ->setOdd($odd6)
            ->setGroup($oddGroup52)
            ->setOrder(5);
        $manager->persist($groupingOdd52);

        $groupingOdd61 = (new GroupingOdd())
            ->setOdd($odd1)
            ->setGroup($oddGroup61)
            ->setOrder(6);
        $manager->persist($groupingOdd61);

        $groupingOdd62 = (new GroupingOdd())
            ->setOdd($odd2)
            ->setGroup($oddGroup62)
            ->setOrder(6);
        $manager->persist($groupingOdd62);

        $groupingOdd71 = (new GroupingOdd())
            ->setOdd($odd1)
            ->setGroup($oddGroup71)
            ->setOrder(7);
        $manager->persist($groupingOdd71);

        $groupingOdd72 = (new GroupingOdd())
            ->setOdd($odd2)
            ->setGroup($oddGroup72)
            ->setOrder(7);
        $manager->persist($groupingOdd72);

        $manager->flush();
    }
}
