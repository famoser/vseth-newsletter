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
use App\Entity\Traits\HideTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimeTrait;
use App\Enum\OrganisationCategoryType;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrganisationRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Organisation extends BaseEntity
{
    use IdTrait;
    use TimeTrait;
    use HideTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $email;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $category = OrganisationCategoryType::VSETH;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $comments;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $authenticationCode;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true, options={"default": null})
     */
    private $lastAuthenticationCodeRequestAt;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true, options={"default": null})
     */
    private $lastVisitAt;

    /**
     * @var Entry[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Entry", mappedBy="organisation")
     * @ORM\OrderBy({"createdAt": "DESC"})
     */
    private $entries;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getCategory(): ?int
    {
        return $this->category;
    }

    public function setCategory(int $category): void
    {
        $this->category = $category;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(?string $comments): void
    {
        $this->comments = $comments;
    }

    public function getAuthenticationCode(): string
    {
        return $this->authenticationCode;
    }

    public function setAuthenticationCode(string $authenticationCode): void
    {
        $this->authenticationCode = $authenticationCode;
    }

    public function getLastVisitAt(): ?DateTime
    {
        return $this->lastVisitAt;
    }

    public function setVisitOccurred()
    {
        $this->lastVisitAt = new \DateTime();
    }

    /**
     * @return Entry[]|ArrayCollection
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @throws \Exception
     */
    public function generateAuthenticationCode()
    {
        $this->authenticationCode = Uuid::uuid4();
    }

    public function getLastAuthenticationCodeRequestAt(): ?DateTime
    {
        return $this->lastAuthenticationCodeRequestAt;
    }

    public function setAuthenticationCodeRequestOccurred(): void
    {
        $this->lastAuthenticationCodeRequestAt = new \DateTime();
    }
}
