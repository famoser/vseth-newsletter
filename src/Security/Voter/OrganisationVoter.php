<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Voter;

use App\Entity\Organisation;
use App\Model\User;
use App\Security\Voter\Base\BaseVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class OrganisationVoter extends BaseVoter
{
    const ADD_ENTRY = 10;

    /**
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Organisation;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param Organisation $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $isAdmin = \in_array(User::ROLE_ADMIN, $token->getRoleNames(), true);
        $isOwner = $subject->getEmail() === $token->getUser()->getUsername();

        switch ($attribute) {
            case self::VIEW:
                return true;
            case self::ADD_ENTRY:
                return $isAdmin || $isOwner;
            case self::EDIT:
                return $isAdmin;
        }

        return false;
    }
}
