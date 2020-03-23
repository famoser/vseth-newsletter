<?php

/*
 * This file is part of the vseth-musikzimmer-pay project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Service\Interfaces\EmailServiceInterface;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EmailService implements EmailServiceInterface
{
    /**
     * @var Swift_Mailer
     */
    private $mailer;

    /**
     * @var string
     */
    private $replyEmail;

    /**
     * EmailService constructor.
     */
    public function __construct(Swift_Mailer $mailer, ParameterBagInterface $parameterBag)
    {
        $this->mailer = $mailer;
        $this->replyEmail = $parameterBag->get('REPLY_EMAIL');
    }

    /**
     * @param string[] $options
     *
     * @return bool
     */
    public function sendEmail(string $receiver, string $subject, string $body)
    {
        $message = (new Swift_Message())
            ->setSubject($subject)
            ->setFrom($this->replyEmail)
            ->setReplyTo($this->replyEmail)
            ->setTo($receiver)
            ->setBody($body);

        //send message & check if at least one receiver was reached
        return $this->mailer->send($message) > 0;
    }
}
