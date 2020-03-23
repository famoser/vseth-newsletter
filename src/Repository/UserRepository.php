<?php

/*
 * This file is part of the vseth-musikzimmer-pay project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\User;
use App\Enum\PaymentRemainderStatusType;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @return User[]
     */
    public function findByNotPayed()
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where('u.paymentRemainderStatus != :status')
            ->setParameter('status', PaymentRemainderStatusType::PAYMENT_SUCCESSFUL);

        return $qb->getQuery()->getResult();
    }
}
