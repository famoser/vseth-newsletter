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

use App\Entity\Entry;
use App\Entity\Newsletter;
use App\Model\User;
use App\Security\Voter\Base\BaseVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class NewsletterVoter extends BaseVoter
{
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
     * @param Entry $subject
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!\in_array(User::ROLE_ADMIN, $token->getRoleNames(), true)) {
            return false;
        }

        return $subject->getSentAt() === null;
    }
}
