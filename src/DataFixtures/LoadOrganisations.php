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
use App\Entity\Organisation;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Serializer\SerializerInterface;

class LoadOrganisations extends BaseFixture
{
    const ORDER = 1;

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
        //prepare resources
        $json = file_get_contents(__DIR__ . '/Resources/organisations.json');
        $organisations = $this->serializer->deserialize($json, Organisation::class . '[]', 'json');

        foreach ($organisations as $organisation) {
            $manager->persist($organisation);
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
