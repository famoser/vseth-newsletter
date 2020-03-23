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

use App\Entity\User;
use App\Enum\PaymentRemainderStatusType;

class PaymentStatistics
{
    /**
     * @var int
     */
    private $owedAmountUserCount = 0;

    /**
     * @var int
     */
    private $owedAmountTotal = 0;

    /**
     * @var int
     */
    private $payedAmountUserCount = 0;

    /**
     * @var int
     */
    private $payedAmountTotal = 0;

    /**
     * @var int
     */
    private $discountUserCount = 0;

    /**
     * @var int
     */
    private $discountTotal = 0;

    public function registerUser(User $user, int $fees)
    {
        if ($user->getPaymentRemainderStatus() === PaymentRemainderStatusType::PAYMENT_SUCCESSFUL) {
            ++$this->payedAmountUserCount;
            $this->payedAmountTotal += $user->getAmountPayed() / 100;
        } else {
            ++$this->owedAmountUserCount;
            $this->owedAmountTotal += $user->getAmountOwed() + $fees - $user->getDiscount();
        }

        if ($user->getDiscount() !== 0) {
            ++$this->discountUserCount;
            $this->discountTotal += $user->getDiscount();
        }
    }

    public function getOwedAmountUserCount(): int
    {
        return $this->owedAmountUserCount;
    }

    public function getOwedAmountTotal(): int
    {
        return $this->owedAmountTotal;
    }

    public function getPayedAmountUserCount(): int
    {
        return $this->payedAmountUserCount;
    }

    public function getPayedAmountTotal(): int
    {
        return $this->payedAmountTotal;
    }

    public function getDiscountUserCount(): int
    {
        return $this->discountUserCount;
    }

    public function getDiscountTotal(): int
    {
        return $this->discountTotal;
    }
}
