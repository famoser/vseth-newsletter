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

class ImportStatistics
{
    /**
     * @var int
     */
    private $userCount;

    /**
     * @var int
     */
    private $reservationCount;

    /**
     * @var int
     */
    private $totalAmountOwed;

    /**
     * ImportStatistics constructor.
     */
    public function __construct(int $userCount, int $reservationCount, int $totalAmountOwed)
    {
        $this->userCount = $userCount;
        $this->reservationCount = $reservationCount;
        $this->totalAmountOwed = $totalAmountOwed;
    }

    public function getUserCount(): int
    {
        return $this->userCount;
    }

    public function getReservationCount(): int
    {
        return $this->reservationCount;
    }

    public function getTotalAmountOwed(): int
    {
        return $this->totalAmountOwed;
    }
}
