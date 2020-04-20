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

    public function __construct(Entry $entry)
    {
        $this->entry = $entry;
    }

    public function realId()
    {
        return $this->entry->getId();
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
