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

use App\Entity\Category;
use App\Model\UserModel;
use App\Security\Voter\Base\NewsletterContentVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CategoryVoter extends NewsletterContentVoter
{
    /**
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Category;
    }

    protected function canAccess($subject, TokenInterface $token)
    {
        return \in_array(UserModel::ROLE_ADMIN, $token->getRoleNames(), true);
    }
}
