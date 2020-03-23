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

class PaymentInfo
{
    /**
     * @var int
     */
    private $invoiceId;

    /**
     * @var string
     */
    private $invoiceLink;

    public function getInvoiceId(): int
    {
        return $this->invoiceId;
    }

    public function setInvoiceId(int $invoiceId): void
    {
        $this->invoiceId = $invoiceId;
    }

    public function getInvoiceLink(): string
    {
        return $this->invoiceLink;
    }

    public function setInvoiceLink(string $invoiceLink): void
    {
        $this->invoiceLink = $invoiceLink;
    }
}
