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

use App\Entity\Entry;
use Doctrine\ORM\EntityRepository;

class EntryRepository extends EntityRepository
{
    /**
     * @return Entry[]
     */
    public function findApprovedByNewsletter($newsletterId)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->where('u.approvedAt IS NOT NULL')
            ->andWhere('u.newsletter = :newsletter_id')
            ->setParameter(':newsletter_id', $newsletterId)
            ->orderBy('u.priority', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
