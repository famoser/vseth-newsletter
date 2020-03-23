<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Helper;

class DateTimeHelper
{
    public static function getSubscriptionEnd(\DateTime $start)
    {
        $sixMonths = new \DateInterval('P6M');
        $oneDay = new \DateInterval('P1D');

        return $start->add($sixMonths)->sub($oneDay);
    }
}
