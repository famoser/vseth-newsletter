<?php

/*
 * This file is part of the vseth-musikzimmer-pay project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\DataFixtures\Base\BaseFixture;
use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Serializer\SerializerInterface;

class LoadReservation extends BaseFixture
{
    const ORDER = LoadUsers::ORDER + 1;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * LoadEvent constructor.
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        /** @var User[] $users */
        $users = $manager->getRepository(User::class)->findAll();

        $this->fillWithPremadeReservations($manager, $users[0]);

        $faker = $this->getFaker();
        $organisationCount = \count($users);
        for ($i = 1; $i < $organisationCount; ++$i) {
            /** @var Reservation[] $randomEvents */
            $randomEvents = $this->loadSomeRandoms($manager, $faker->randomFloat(0, 0, 10));
            foreach ($randomEvents as $randomEvent) {
                $randomEvent->setUser($users[$i]);
                $manager->persist($randomEvent);
            }
        }

        $manager->flush();
    }

    /**
     * @throws \Exception
     *
     * @return \DateTime
     */
    private function roundToHour(\DateTime $dateTime)
    {
        $time = $dateTime->format('c');
        $hourPart = mb_substr($time, 0, mb_strlen(' 	2004-02-12T15:19'));

        return new \DateTime($hourPart . ':00+00:00');
    }

    /**
     * @throws \Exception
     *
     * @return Reservation|mixed
     */
    protected function getRandomInstance()
    {
        $faker = $this->getFaker();

        $reservation = new Reservation();

        $reservation->setCreatedAt($faker->dateTimeInInterval('01.01.2019', '+365 days'));
        $reservation->setModifiedAt($faker->dateTimeInInterval($reservation->getCreatedAt(), '+2 days'));

        $reservation->setStart($this->roundToHour($faker->dateTimeInInterval($reservation->getModifiedAt(), '+5 hours')));
        $reservation->setEnd($this->roundToHour($faker->dateTimeInInterval($reservation->getStart(), '+3 hours')));

        $reservation->setRoom($faker->numberBetween(1, 12));

        return $reservation;
    }

    private function fillWithPremadeReservations(ObjectManager $manager, User $user)
    {
        //prepare resources
        $json = file_get_contents(__DIR__ . '/Resources/reservations.json');
        /** @var Reservation[] $reservations */
        $reservations = $this->serializer->deserialize($json, Reservation::class . '[]', 'json');

        foreach ($reservations as $reservation) {
            $reservation->setUser($user);
            $manager->persist($reservation);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return static::ORDER;
    }
}
