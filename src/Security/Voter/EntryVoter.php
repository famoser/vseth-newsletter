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
use App\Entity\Organisation;
use App\Model\User;
use App\Security\Voter\Base\BaseVoter;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EntryVoter extends BaseVoter
{
    /** @var ManagerRegistry */
    private $doctrine;

    /**
     * EntryVoter constructor.
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof Entry;
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
        if (\in_array(User::ROLE_ADMIN, $token->getRoleNames(), true)) {
            return true;
        }

        $organisation = $this->doctrine->getRepository(Organisation::class)->findOneBy(['email' => $token->getUser()->getUsername()]);

        return $subject->getOrganisation() === $organisation;
    }
}
