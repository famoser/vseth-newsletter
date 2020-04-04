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

use App\Entity\Newsletter;
use App\Service\Interfaces\NewsletterServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

class NewsletterService implements NewsletterServiceInterface
{
    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $replyEmail;

    /**
     * @var string
     */
    private $testNewsletterEmail;

    /**
     * @var string
     */
    private $newsletterEmail;

    /**
     * EmailService constructor.
     */
    public function __construct(MailerInterface $mailer, LoggerInterface $logger, Environment $twig, ParameterBagInterface $parameterBag)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->replyEmail = $parameterBag->get('REPLY_EMAIL');
        $this->testNewsletterEmail = $parameterBag->get('TEST_NEWSLETTER_EMAIL');
        $this->newsletterEmail = $parameterBag->get('NEWSLETTER_EMAIL');
    }

    /**
     * @return bool
     */
    public function send(Newsletter $newsletter)
    {
        return $this->sendNewsletterTo($newsletter, $this->newsletterEmail);
    }

    /**
     * @return bool
     */
    public function sendTest(Newsletter $newsletter)
    {
        return $this->sendNewsletterTo($newsletter, $this->testNewsletterEmail);
    }

    /**
     * @param string[] $options
     *
     * @return bool
     */
    private function sendNewsletterTo(Newsletter $newsletter, string $receiver)
    {
        $message = (new TemplatedEmail())
            ->subject('Newsletter')
            ->from($this->replyEmail)
            ->replyTo($this->replyEmail)
            ->to($receiver);

        $message->htmlTemplate('email/newsletter.html.twig')
            ->textTemplate('email/newsletter.txt.twig')
            ->context(['newsletter' => $newsletter]);

        //send message & check if at least one receiver was reached
        return $this->mailer->send($message) > 0;
    }
}
