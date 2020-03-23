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

use App\Entity\PaymentRemainder;
use Doctrine\ORM\EntityRepository;

class PaymentRemainderRepository extends EntityRepository
{
    /**
     * @return PaymentRemainder|object|null
     */
    public function findActive()
    {
        return $this->findOneBy([], ['createdAt' => 'DESC']);
    }
}
