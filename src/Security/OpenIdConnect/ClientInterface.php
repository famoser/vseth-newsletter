<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\OpenIdConnect;

use Symfony\Component\HttpFoundation\Request;

interface ClientInterface
{
    /**
     * @return bool
     */
    public function isEnabled();

    /**
     * @return string
     */
    public function redirect(string $redirectUrl, string $state);

    /**
     * @return bool
     */
    public function login(Request $request);
}
