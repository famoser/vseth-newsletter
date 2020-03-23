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
use App\Entity\PaymentRemainder;
use App\Entity\User;
use App\Enum\PaymentRemainderStatusType;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Serializer\SerializerInterface;

class LoadUsers extends BaseFixture
{
    const ORDER = LoadPaymentRemainder::ORDER + 1;

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
        $paymentRemainder = $manager->getRepository(PaymentRemainder::class)->findActive();

        //prepare resources
        $json = file_get_contents(__DIR__ . '/Resources/users.json');
        /** @var User[] $users */
        $users = $this->serializer->deserialize($json, User::class . '[]', 'json');

        foreach ($users as $user) {
            $user->generateAuthenticationCode();
            $user->setPaymentRemainderStatus(PaymentRemainderStatusType::SENT);
            $user->setPaymentRemainder($paymentRemainder);
            $manager->persist($user);
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
