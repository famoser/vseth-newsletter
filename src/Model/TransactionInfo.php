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

class TransactionInfo
{
    /**
     * @var int
     */
    private $amount;

    /**
     * @var string
     */
    private $id;

    /**
     * TransactionInfo constructor.
     */
    public function __construct(int $amount, string $id)
    {
        $this->amount = $amount;
        $this->id = $id;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getId(): string
    {
        return $this->id;
    }
}
