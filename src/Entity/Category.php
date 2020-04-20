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
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class Category extends BaseEntity
{
    use IdTrait;
    use TimeTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $nameDe;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $nameEn;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $priority;

    /**
     * @var Newsletter
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Newsletter", inversedBy="categories")
     */
    private $newsletter;

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

    public function getName(string $locale): ?string
    {
        if ($locale === 'de') {
            return $this->nameDe;
        }

        return $this->nameEn;
    }

    public function getNameDe(): ?string
    {
        return $this->nameDe;
    }

    public function setNameDe(string $nameDe): void
    {
        $this->nameDe = $nameDe;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(string $nameEn): void
    {
        $this->nameEn = $nameEn;
    }

    public function getNewsletter(): ?Newsletter
    {
        return $this->newsletter;
    }

    public function setNewsletter(Newsletter $newsletter): void
    {
        $this->newsletter = $newsletter;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    /**
     * @return Entry[]|ArrayCollection
     */
    public function getEntries()
    {
        return $this->entries;
    }
}
