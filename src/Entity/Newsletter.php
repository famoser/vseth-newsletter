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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * an event determines how the questionnaire looks like.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class Newsletter extends BaseEntity
{
    use IdTrait;
    use TimeTrait;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $plannedSendAt;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $introductionDe;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $introductionEn;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sentAt;

    /**
     * @var Entry[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Entry", mappedBy="newsletter")
     * @ORM\OrderBy({"priority": "ASC"})
     */
    private $entries;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function getPlannedSendAt(): ?\DateTime
    {
        return $this->plannedSendAt;
    }

    public function setPlannedSendAt(?\DateTime $plannedSendAt): void
    {
        $this->plannedSendAt = $plannedSendAt;
    }

    public function getSentAt(): ?\DateTime
    {
        return $this->sentAt;
    }

    public function setSentAt(?\DateTime $sentAt): void
    {
        $this->sentAt = $sentAt;
    }

    /**
     * @return Entry[]|ArrayCollection
     */
    public function getEntries()
    {
        return $this->entries;
    }

    public function getIntroduction(string $locale): ?string
    {
        if ($locale === 'de') {
            return $this->introductionDe;
        }

        return $this->introductionEn;
    }

    public function getIntroductionDe(): ?string
    {
        return $this->introductionDe;
    }

    public function setIntroductionDe(?string $introductionDe): void
    {
        $this->introductionDe = $introductionDe;
    }

    public function getIntroductionEn(): ?string
    {
        return $this->introductionEn;
    }

    public function setIntroductionEn(?string $introductionEn): void
    {
        $this->introductionEn = $introductionEn;
    }
}
