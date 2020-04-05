<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\Organisation;
use App\Service\Interfaces\AuthenticationServiceInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthenticationService implements AuthenticationServiceInterface
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $replyEmail;

    /**
     * AuthenticationService constructor.
     */
    public function __construct(MailerInterface $mailer, ParameterBagInterface $parameterBag, TranslatorInterface $translator)
    {
        $this->replyEmail = $parameterBag->get('REPLY_EMAIL');
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    public function sendAuthenticationCode(Organisation $organisation, string $url)
    {
        $message = (new Email())
            ->from($this->replyEmail)
            ->replyTo($this->replyEmail)
            ->to($organisation->getEmail());

        $message
            ->subject($this->translator->trans('request_code.email.subject', [], 'login'))
            ->text($this->translator->trans('request_code.email.body', ['%url%' => $url, '%organisation_name%' => $organisation->getName()], 'login'));

        return $this->mailer->send($message) > 0;
    }
}
