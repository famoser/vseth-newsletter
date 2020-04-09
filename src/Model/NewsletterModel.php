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

use App\Entity\Newsletter;

class NewsletterModel
{
    /**
     * @var Newsletter
     */
    private $newsletter;

    /**
     * @var int
     */
    private $approvedEntries = 0;

    /**
     * @var int
     */
    private $moderateEntries = 0;

    public function __construct(Newsletter $newsletter)
    {
        $this->newsletter = $newsletter;

        foreach ($this->newsletter->getEntries() as $entry) {
            if ($entry->getApprovedAt() !== null) {
                ++$this->approvedEntries;
            } elseif ($entry->getRejectReason() === null) {
                ++$this->moderateEntries;
            }
        }
    }

    public function getNewsletter(): Newsletter
    {
        return $this->newsletter;
    }

    public function getApprovedEntries(): int
    {
        return $this->approvedEntries;
    }

    public function getModerateEntries(): int
    {
        return $this->moderateEntries;
    }
}
