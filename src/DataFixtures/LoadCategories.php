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
use App\Entity\Category;
use App\Entity\Newsletter;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Serializer\SerializerInterface;

class LoadCategories extends BaseFixture
{
    const ORDER = LoadNewsletters::ORDER + 1;

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
        /** @var Newsletter[] $newsletters */
        $newsletters = $manager->getRepository(Newsletter::class)->findAll();
        foreach ($newsletters as $newsletter) {
            //prepare resources
            $json = file_get_contents(__DIR__ . '/Resources/categories.json');
            /** @var Category[] $categories */
            $categories = $this->serializer->deserialize($json, Category::class . '[]', 'json');

            foreach ($categories as $entry) {
                $entry->setNewsletter($newsletter);
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
