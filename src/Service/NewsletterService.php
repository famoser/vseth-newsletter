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

use App\Entity\Category;
use App\Entity\Newsletter;
use App\Model\Publish\CategoryModel;
use App\Model\Publish\EntryModel;
use App\Model\Publish\NewsletterModel;
use App\Service\Interfaces\NewsletterServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
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
     * @var TranslatorInterface
     */
    private $translator;

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
    public function __construct(MailerInterface $mailer, LoggerInterface $logger, TranslatorInterface $translator, Environment $twig, ParameterBagInterface $parameterBag)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->translator = $translator;
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

    public function createPublishModel(Newsletter $newsletter): NewsletterModel
    {
        /** @var CategoryModel[] $categories */
        $categories = [];
        foreach ($newsletter->getCategories() as $category) {
            $categories[] = $this->createCategoryModel($category);
        }

        $noCategoryEntries = [];
        foreach ($newsletter->getEntries() as $entry) {
            if ($entry->getCategory() === null && $entry->shouldPublish()) {
                $noCategoryEntries[] = new EntryModel($entry);
            }
        }
        if (\count($noCategoryEntries) > 0) {
            $categories[] = $this->createOtherCategoryModel($noCategoryEntries);
        }

        return new NewsletterModel($newsletter, $categories);
    }

    /**
     * @param EntryModel[] $noCategoryEntries
     */
    private function createOtherCategoryModel(array $noCategoryEntries): CategoryModel
    {
        $category = new Category();
        $category->setNameDe($this->translator->trans('entity.other.name', [], 'entity_category', 'de'));
        $category->setNameEn($this->translator->trans('entity.other.name', [], 'entity_category', 'en'));

        return new CategoryModel($category, $noCategoryEntries);
    }

    private function createCategoryModel(Category $category): CategoryModel
    {
        $entries = [];
        foreach ($category->getEntries() as $entry) {
            if ($entry->shouldPublish()) {
                $entries[] = new EntryModel($entry);
            }
        }

        return new CategoryModel($category, $entries);
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
