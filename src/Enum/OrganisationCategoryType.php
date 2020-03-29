<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Enum;

use App\Enum\Base\BaseEnum;

class OrganisationCategoryType extends BaseEnum
{
    const VSETH = 0;
    const COMMISSION = 1;
    const STUDY_ASSOCIATION = 2;
    const ASSOCIATED = 3;
    const RECOGNISED = 4;
    const EXTERNAL = 5;
}
