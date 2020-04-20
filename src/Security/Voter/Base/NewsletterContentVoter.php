<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Voter\Base;

use App\Entity\Entry;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

abstract class NewsletterContentVoter extends BaseVoter
{
    abstract protected function canAccess($subject, TokenInterface $token);

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
        $allowedToAccess = $this->canAccess($subject, $token);
        $newsletterNotSent = $subject->getNewsletter()->getSentAt() === null;

        switch ($attribute) {
            case self::VIEW:
                return $allowedToAccess;
            case self::EDIT:
                return $allowedToAccess && $newsletterNotSent;
        }

        return false;
    }
}
