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
                    $newsletterDate = $newsletter->getPlannedSendAt();
                    $start = $newsletterDate->add(new \DateInterval('P10D'));
                    $end = $newsletterDate->add(new \DateInterval('P10DT2H'));
                    $entry->setStartAt($start);
                    $entry->setEndAt($end);
                }

                $manager->persist($entry);
            }
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
