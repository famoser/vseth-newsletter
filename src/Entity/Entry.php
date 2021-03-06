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
use App\Entity\Traits\PriorityTrait;
use App\Entity\Traits\TimeTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EntryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Entry extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use PriorityTrait;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
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
     * @Assert\Length(max = 300)
     */
    private $descriptionDe;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Assert\Length(max = 300)
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
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $startTime;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $endTime;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $location;

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
     * @var Category|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="entries")
     */
    private $category;

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

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    public function setStartTime(?string $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    public function setEndTime(?string $endTime): void
    {
        $this->endTime = $endTime;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): void
    {
        $this->location = $location;
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }

    public function shouldPublish()
    {
        return $this->approvedAt !== null;
    }
}
