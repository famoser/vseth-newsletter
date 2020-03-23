<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_ORGANISATION = 'ROLE_ORGANISATION';

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string[]
     */
    private $roles;

    /**
     * User constructor.
     *
     * @param string[] $roles
     */
    public function __construct(string $password, string $username, array $roles)
    {
        $this->password = $password;
        $this->username = $username;
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        // skipped
    }
}
