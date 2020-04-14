<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\DataFixtures\Base\BaseFixture;
use App\Entity\Entry;
use App\Entity\Newsletter;
use App\Entity\Organisation;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Serializer\SerializerInterface;

class LoadEntries extends BaseFixture
{
    const ORDER = LoadNewsletters::ORDER + LoadOrganisations::ORDER + 1;
    const START_FREQUENCY = 3;

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
     */
    public function load(ObjectManager $manager)
    {
        $startIndex = 0;

        $organisations = $manager->getRepository(Organisation::class)->findAll();
        $organisationIndex = 0;

        $newsletters = $manager->getRepository(Newsletter::class)->findAll();
        foreach ($newsletters as $newsletter) {
            //prepare resources
            $json = file_get_contents(__DIR__ . '/Resources/entries.json');
            /** @var Entry[] $entries */
            $entries = $this->serializer->deserialize($json, Entry::class . '[]', 'json');

            foreach ($entries as $entry) {
                $organisation = $organisations[$organisationIndex];
                if (++$organisationIndex >= \count($organisations)) {
                    $organisationIndex = 0;
                }

                $entry->setOrganisation($organisation);
                $entry->setOrganizer($organisation->getName());
                $entry->setNewsletter($newsletter);

                if ($startIndex++ % self::START_FREQUENCY !== 0) {
                    $newsletterDate = clone $newsletter->getPlannedSendAt();
                    $start = clone $newsletterDate->add(new \DateInterval('P10D'));
                    $end = $newsletterDate->add(new \DateInterval('P10DT2H'));
                    $entry->setStartDate($start);
                    $entry->setEndDate($end);
                }

                $manager->persist($entry);
            }
        }

        /** @var Entry[] $randoms */
        $randoms = $this->loadSomeRandoms($manager, 30);
        foreach ($randoms as $random) {
            $randomOrganisation = $organisations[rand(0, \count($organisations) - 1)];
            $random->setOrganisation($randomOrganisation);
            $random->setOrganizer($randomOrganisation->getName());
            $random->setNewsletter($newsletters[0]);
        }

        $manager->flush();
    }

    protected function getRandomInstance()
    {
        $faker = $this->getFaker();

        $entry = new Entry();
        $entry->setPriority($faker->numberBetween(0, 10000));
        $entry->setTitleDe($faker->text(40));
        $entry->setTitleEn($faker->text(40));
        $entry->setDescriptionDe($faker->text(300));
        $entry->setDescriptionEn($faker->text(300));
        $entry->setLinkDe($faker->text(30));
        $entry->setLinkEn($faker->text(30));

        if ($faker->numberBetween(0, 100) > 20) {
            $entry->setStartDate($faker->dateTimeInInterval('now', '+30 days'));

            $withTime = $faker->numberBetween(0, 100) > 50;
            if ($withTime) {
                $entry->setStartTime($faker->time());
            }
            if ($faker->numberBetween(0, 100) > 10) {
                if ($faker->numberBetween(0, 100) > 10) {
                    $entry->setEndDate((clone $entry->getStartDate())->add(new \DateInterval('PT2H')));
                } else {
                    $entry->setEndDate((clone $entry->getStartDate())->add(new \DateInterval('P1DT2H')));
                }
                if ($withTime) {
                    $entry->setEndTime($faker->time());
                }
            }
        }

        if ($faker->numberBetween(0, 100) > 10) {
            $places = ['StuZ', 'HG E5', 'CAB E11', 'Höngg', 'Semperaula'];
            $chosenPlace = $faker->numberBetween(0, \count($places) - 1);
            $entry->setLocation($places[$chosenPlace]);
        }

        if ($faker->numberBetween(0, 100) > 20) {
            $entry->setApprovedAt(new  \DateTime());
        }

        return $entry;
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
