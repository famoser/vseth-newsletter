<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Publish;

use App\Entity\Entry;

class EntryModel
{
    /**
     * @var Entry
     */
    private $entry;

    /**
     * @var int
     */
    private $ref;

    public function __construct(Entry $entry, int $ref)
    {
        $this->entry = $entry;
        $this->ref = $ref;
    }

    public function getRef()
    {
        return $this->ref;
    }

    public function getRealId()
    {
        return $this->entry->getId();
    }

    public function hasEventInfo()
    {
        return $this->entry->getLocation() !== null || $this->entry->getStartTime() !== null;
    }

    public function getEventInfo()
    {
        $dateFormat = 'd.m.Y';

        $result = '';
        if ($this->entry->getStartDate() !== null) {
            $startDate = $this->entry->getStartDate()->format($dateFormat);
            $result .= $startDate;
            if ($this->entry->getStartTime() !== null) {
                $result .= ' ' . mb_substr($this->entry->getStartTime(), 0, 5);
            }
            if ($this->entry->getEndDate() !== null) {
                $endDate = $this->entry->getEndDate()->format($dateFormat);
                $showDate = $startDate !== $endDate;
                $showTime = $this->entry->getEndTime() !== null;

                if ($showDate || $showTime) {
                    $result .= ' -';

                    if ($showDate) {
                        $result .= ' ' . $endDate;
                    }

                    if ($showTime) {
                        $result .= ' ' . mb_substr($this->entry->getEndTime(), 0, 5);
                    }
                }
            }
        }

        if ($this->entry->getLocation() !== null) {
            if (mb_strlen($result) > 0) {
                $result .= ', ';
            }
            $result .= $this->entry->getLocation();
        }

        return $result;
    }

    public function getHeader(string $locale)
    {
        if ($this->entry->getOrganizer() !== null) {
            return $this->entry->getOrganizer() . ': ' . $this->getTitle($locale);
        }

        return $this->getTitle($locale);
    }

    public function getTitle(string $locale): ?string
    {
        if ($locale === 'de') {
            return $this->entry->getTitleDe();
        }

        return $this->entry->getLinkEn();
    }

    public function getDescription(string $locale): ?string
    {
        if ($locale === 'de') {
            return $this->entry->getDescriptionDe();
        }

        return $this->entry->getDescriptionEn();
    }

    public function getLink(string $locale): ?string
    {
        if ($locale === 'de') {
            return $this->entry->getLinkDe();
        }

        return $this->entry->getLinkEn();
    }
}
