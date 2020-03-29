<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * an event determines how the questionnaire looks like.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class Entry extends BaseEntity
{
    use IdTrait;
    use TimeTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $organizer;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $titleDe;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $titleEn;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $descriptionDe;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $descriptionEn;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $linkDe;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $linkEn;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endAt;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $location;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $priority;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $approvedAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sentAt;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $rejectReason;

    /**
     * @var Newsletter
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Newsletter", inversedBy="entries")
     */
    private $newsletter;

    /**
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Organisation", inversedBy="entries")
     */
    private $organisation;

    public function getOrganizer(): ?string
    {
        return $this->organizer;
    }

    public function setOrganizer(string $organizer): void
    {
        $this->organizer = $organizer;
    }

    public function getTitleDe(): ?string
    {
        return $this->titleDe;
    }

    public function setTitleDe(string $titleDe): void
    {
        $this->titleDe = $titleDe;
    }

    public function getTitleEn(): ?string
    {
        return $this->titleEn;
    }

    public function setTitleEn(string $titleEn): void
    {
        $this->titleEn = $titleEn;
    }

    public function getDescriptionDe(): ?string
    {
        return $this->descriptionDe;
    }

    public function setDescriptionDe(string $descriptionDe): void
    {
        $this->descriptionDe = $descriptionDe;
    }

    public function getDescriptionEn(): ?string
    {
        return $this->descriptionEn;
    }

    public function setDescriptionEn(string $descriptionEn): void
    {
        $this->descriptionEn = $descriptionEn;
    }

    public function getLinkDe(): ?string
    {
        return $this->linkDe;
    }

    public function setLinkDe(?string $linkDe): void
    {
        $this->linkDe = $linkDe;
    }

    public function getLinkEn(): ?string
    {
        return $this->linkEn;
    }

    public function setLinkEn(?string $linkEn): void
    {
        $this->linkEn = $linkEn;
    }

    public function getStartAt(): ?\DateTime
    {
        return $this->startAt;
    }

    public function setStartAt(?\DateTime $startAt): void
    {
        $this->startAt = $startAt;
    }

    public function getEndAt(): ?\DateTime
    {
        return $this->endAt;
    }

    public function setEndAt(?\DateTime $endAt): void
    {
        $this->endAt = $endAt;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): void
    {
        $this->location = $location;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getApprovedAt(): ?\DateTime
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(?\DateTime $approvedAt): void
    {
        $this->approvedAt = $approvedAt;
    }

    public function getSentAt(): ?\DateTime
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTime $sentAt): void
    {
        $this->sentAt = $sentAt;
    }

    public function getRejectReason(): ?string
    {
        return $this->rejectReason;
    }

    public function setRejectReason(?string $rejectReason): void
    {
        $this->rejectReason = $rejectReason;
    }

    public function getNewsletter(): ?Newsletter
    {
        return $this->newsletter;
    }

    public function setNewsletter(Newsletter $newsletter): void
    {
        $this->newsletter = $newsletter;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation): void
    {
        $this->organisation = $organisation;
    }
}
