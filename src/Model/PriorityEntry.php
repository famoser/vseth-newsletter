<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

use App\Entity\Entry;

class PriorityEntry
{
    /**
     * @var Entry
     */
    private $entry;
    private $originalPriority;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getOriginalPriority()
    {
        return $this->originalPriority;
    }

    /**
     * @param mixed $originalPriority
     */
    public function setOriginalPriority($originalPriority): void
    {
        $this->originalPriority = $originalPriority;
    }
}
