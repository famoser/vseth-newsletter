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
use App\Entity\Newsletter;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Serializer\SerializerInterface;

class LoadNewsletters extends BaseFixture
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
        $newsletter = new Newsletter();
        $introductionDe = 'Willkommen im Semester! Der VSETH hat wieder viele spannende Events für euch.';
        $newsletter->setIntroductionDe($introductionDe);
        $introductionEn = 'Welcome to the new semester! VSETH has again a lot to offer.';
        $newsletter->setIntroductionEn($introductionEn);

        //prepare resources
        $newsletter->setPlannedSendAt(new \DateTime('+1 week'));
        $manager->persist($newsletter);

        $newsletter = clone $newsletter;
        $newsletter->setPlannedSendAt(new \DateTime('+4 week'));
        $manager->persist($newsletter);

        $newsletter = clone $newsletter;
        $newsletter->setPlannedSendAt(new \DateTime('-2 weeks'));
        $newsletter->setSentAt(new \DateTime('-2 weeks'));
        $manager->persist($newsletter);

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
