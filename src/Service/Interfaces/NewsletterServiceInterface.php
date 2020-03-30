<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Interfaces;

use App\Entity\Newsletter;

interface NewsletterServiceInterface
{
    /**
     * @return bool
     */
    public function send(Newsletter $newsletter);

    /**
     * @return bool
     */
    public function sendTest(Newsletter $newsletter);
}
