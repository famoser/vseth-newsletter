<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/*
 * automatically keeps track of creation time & last change time
 */

trait HideTrait
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $hiddenAt;

    /**
     * @throws Exception
     */
    public function hide()
    {
        $this->hiddenAt = new DateTime();
    }

    public function unhide()
    {
        $this->hiddenAt = null;
    }

    /**
     * @return DateTime|null
     */
    public function getHiddenAt()
    {
        return $this->hiddenAt;
    }
}
