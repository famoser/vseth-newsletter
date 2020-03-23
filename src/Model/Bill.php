<?php

/*
 * This file is part of the vseth-musikzimmer-pay project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

use App\Enum\UserCategoryType;
use App\Model\Bill\Recipient;
use App\Model\Bill\Reservation;
use App\Model\Bill\Subscription;

class Bill
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Recipient
     */
    private $recipient;

    /**
     * @var \DateTime
     */
    private $periodStart;

    /**
     * @var \DateTime
     */
    private $periodEnd;

    /**
     * @var int
     */
    private $category = UserCategoryType::STUDENT;

    /**
     * @var Reservation[]
     */
    private $reservations = [];

    /**
     * @var int
     */
    private $reservationsSubtotal = 0;

    /**
     * @var \DateTime|null
     */
    private $lastPayedSubscriptionEnd;

    /**
     * @var Subscription[]
     */
    private $subscriptions = [];

    /**
     * @var int
     */
    private $subscriptionsSubtotal = 0;

    /**
     * @var int
     */
    private $billFee = 0;

    /**
     * @var int
     */
    private $discount = 0;

    /**
     * @var string|null
     */
    private $discountDescription;

    /**
     * @var int
     */
    private $total = 0;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getRecipient(): Recipient
    {
        return $this->recipient;
    }

    public function setRecipient(Recipient $recipient): void
    {
        $this->recipient = $recipient;
    }

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

    public function getCategory(): int
    {
        return $this->category;
    }

    public function setCategory(int $category): void
    {
        $this->category = $category;
    }

    /**
     * @return Reservation[]
     */
    public function getReservations(): array
    {
        return $this->reservations;
    }

    /**
     * @param Reservation[] $reservations
     */
    public function setReservations(array $reservations): void
    {
        $this->reservations = $reservations;
    }

    public function getLastPayedSubscriptionEnd(): ?\DateTime
    {
        return $this->lastPayedSubscriptionEnd;
    }

    public function setLastPayedSubscriptionEnd(?\DateTime $lastPayedSubscriptionEnd): void
    {
        $this->lastPayedSubscriptionEnd = $lastPayedSubscriptionEnd;
    }

    /**
     * @return Subscription[]
     */
    public function getSubscriptions(): array
    {
        return $this->subscriptions;
    }

    /**
     * @param Subscription[] $subscriptions
     */
    public function setSubscriptions(array $subscriptions): void
    {
        $this->subscriptions = $subscriptions;
    }

    public function getBillFee(): int
    {
        return $this->billFee;
    }

    public function setBillFee(int $billFee): void
    {
        $this->billFee = $billFee;
    }

    public function getDiscount(): int
    {
        return $this->discount;
    }

    public function setDiscount(int $discount): void
    {
        $this->discount = $discount;
    }

    public function getDiscountDescription(): ?string
    {
        return $this->discountDescription;
    }

    public function setDiscountDescription(?string $discountDescription): void
    {
        $this->discountDescription = $discountDescription;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function getReservationsSubtotal(): int
    {
        return $this->reservationsSubtotal;
    }

    public function setReservationsSubtotal(int $reservationsSubtotal): void
    {
        $this->reservationsSubtotal = $reservationsSubtotal;
    }

    public function getSubscriptionsSubtotal(): int
    {
        return $this->subscriptionsSubtotal;
    }

    public function setSubscriptionsSubtotal(int $subscriptionsSubtotal): void
    {
        $this->subscriptionsSubtotal = $subscriptionsSubtotal;
    }
}
