<?php

/*
 * This file is part of the vseth-musikzimmer-pay project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security;

use App\Entity\User;
use App\Model\UserModel;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    /**
     * @var string
     */
    private $adminPassword;

    /**
     * PasswordContainerProvider constructor.
     */
    public function __construct(ManagerRegistry $registry, ParameterBagInterface $parameterBag)
    {
        $this->registry = $registry;
        $this->adminPassword = $parameterBag->get('ADMIN_PASSWORD');
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @throws UnsupportedUserException if the account is not supported
     *
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof UserModel) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @throws UsernameNotFoundException if the user is not found
     *
     * @return UserInterface|UserModel
     */
    public function loadUserByUsername($username)
    {
        if ($username === 'ia@vseth.ethz.ch') {
            return new UserModel(-1, $this->adminPassword, $username, [UserModel::ROLE_ADMIN]);
        }

        /** @var User|null $user */
        $user = $this->registry->getRepository(User::class)->findOneBy(['email' => $username]);
        if (null !== $user) {
            return new UserModel($user->getId(), $user->getAuthenticationCode(), $user->getEmail(), [UserModel::ROLE_USER]);
        }

        throw new UsernameNotFoundException(sprintf('Username "%s" does not exist in UserProvider.', $username));
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return UserModel::class === $class;
    }
}
