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

use App\Entity\Newsletter;
use App\Model\UserModel;
use App\Security\Voter\Base\BaseVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class NewsletterVoter extends BaseVoter
{
    const ADD_ENTRY = 10;
    const ADD_CATEGORY = 11;

    /**
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Newsletter;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string $attribute
     * @param Newsletter $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $newsletterNotSent = $subject->getSentAt() === null;
        $isAdmin = \in_array(UserModel::ROLE_ADMIN, $token->getRoleNames(), true);

        switch ($attribute) {
            case self::VIEW:
                return true;
            case self::ADD_ENTRY:
                return $newsletterNotSent;
            case self::ADD_CATEGORY:
            case self::EDIT:
                return $newsletterNotSent && $isAdmin;
        }

        return false;
    }
}
