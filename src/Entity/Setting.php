<?php

/*
 * This file is part of the vseth-musikzimmer-pay project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * an event determines how the questionnaire looks like.
 *
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class Setting extends BaseEntity
{
    use IdTrait;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $periodStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $periodEnd;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $paymentPrefix = 'musikzimmer-2019';

    public function getPeriodStart(): \DateTime
    {
        return $this->periodStart;
    }

    public function setPeriodStart(\DateTime $periodStart): void
    {
        $this->periodStart = $periodStart;
    }

    public function getPeriodEnd(): \DateTime
    {
        return $this->periodEnd;
    }

    public function setPeriodEnd(\DateTime $periodEnd): void
    {
        $this->periodEnd = $periodEnd;
    }

    public function getPaymentPrefix(): string
    {
        return $this->paymentPrefix;
    }

    public function setPaymentPrefix(string $paymentPrefix): void
    {
        $this->paymentPrefix = $paymentPrefix;
    }
}
