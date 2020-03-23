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

class RoomType extends BaseEnum
{
    const UNAUTHORIZED = 1;
    const HPI_D_2_2 = 2;
    const HPI_D_4_1 = 3;
    const MM_A_71_1 = 4;
    const MM_A_98 = 5;
    const HPI_D_4_2 = 6;
    const HPI_D_5_1 = 7;
    const HPI_D_5_2 = 8;
    const MM_A_71_3 = 9;
    const MM_A_71_4 = 10;
    const MM_B_71_1 = 11;
    const HPI_D_2_1 = 12;
}
