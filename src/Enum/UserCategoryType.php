<?php

/*
 * This file is part of the vseth-musikzimmer-pay project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Enum;

use App\Enum\Base\BaseEnum;

class UserCategoryType extends BaseEnum
{
    const STUDENT = 1;
    const PHD = 3;
    const ETH_UNIVERSITY_STAFF = 4;
    const EXTERNAL = 6;
    const SERVICE = 7;
    const ADMIN = 8;
}
