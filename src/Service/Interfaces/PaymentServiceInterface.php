<?php

/*
 * This file is part of the vseth-musikzimmer-pay project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use App\Model\Bill;
use App\Model\PaymentInfo;
use App\Model\TransactionInfo;

interface PaymentServiceInterface
{
    /**
     * @throws \Payrexx\PayrexxException
     *
     * @return PaymentInfo
     */
    public function startPayment(Bill $bill, string $successUrl);

    /**
     * @return bool
     */
    public function paymentSuccessful(PaymentInfo $paymentInfo, ?TransactionInfo &$transactionInfo);

    /**
     * @throws \Payrexx\PayrexxException
     *
     * @return void
     */
    public function closePayment(PaymentInfo $paymentInfo);
}
