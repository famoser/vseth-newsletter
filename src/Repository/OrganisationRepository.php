<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * This file is part of the vseth-semesterly-reports project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\Organisation;
use Doctrine\ORM\EntityRepository;

class OrganisationRepository extends EntityRepository
{
    /**
     * @return Organisation[]
     */
    public function findHidden()
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where('u.hiddenAt IS NOT NULL')
            ->orderBy('u.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Organisation[]
     */
    public function findActive()
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where('u.hiddenAt IS NULL')
            ->orderBy('u.name', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
