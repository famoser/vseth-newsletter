<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Administration;

use App\Controller\Administration\Base\BaseController;
use App\Entity\Entry;
use App\Entity\Newsletter;
use App\Model\Breadcrumb;
use App\Security\Voter\NewsletterVoter;
use App\Service\Interfaces\NewsletterServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/newsletter")
 */
class NewsletterController extends BaseController
{
    /**
     * @var Newsletter
     */
    private $newsletter;

    /**
     * @Route("/{newsletter}", name="administration_newsletter")
     *
     * @return Response
     */
    public function indexAction(Newsletter $newsletter)
    {
        $entryRepository = $this->getDoctrine()->getRepository(Entry::class);
        $entries = $entryRepository->findApprovedByNewsletter($newsletter->getId());
        $moderateEntryCount = $entryRepository->count(['approvedAt' => null, 'rejectReason' => null, 'newsletter' => $newsletter->getId()]);

        $this->newsletter = $newsletter;

        return $this->render('administration/newsletter/index.html.twig', [
            'newsletter' => $newsletter,
            'moderate_entry_count' => $moderateEntryCount,
        ]);
    }

    /**
     * @Route("/new", name="administration_newsletter_new")
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $newsletter = new Newsletter();

        /** @var Newsletter[] $lastNewsletters */
        $lastNewsletters = $this->getDoctrine()->getRepository(Newsletter::class)->findBy([], ['plannedSendAt' => 'DESC'], 2);

        $defaultStart = new \DateTime('today');
        if (\count($lastNewsletters) === 1) {
            $defaultStart = $lastNewsletters[0]->getPlannedSendAt();
        }
        $defaultDiff = new \DateInterval('P14T');
        if (\count($lastNewsletters) === 2) {
            $defaultDiff = $lastNewsletters[0]->getPlannedSendAt()->diff($lastNewsletters[1]->getPlannedSendAt());
        }

        $newStart = $defaultStart->add($defaultDiff);
        $newsletter->setPlannedSendAt($newStart);

        if (\count($lastNewsletters) > 0) {
            $newsletter->setIntroductionDe($lastNewsletters[0]->getIntroductionDe());
            $newsletter->setIntroductionEn($lastNewsletters[0]->getIntroductionEn());
        }

        //process form
        $myForm = $this->handleCreateForm($request, $newsletter);
        if ($myForm instanceof Response) {
            return $myForm;
        }

        return $this->render('administration/newsletter/new.html.twig', ['form' => $myForm->createView()]);
    }

    /**
     * @Route("/{newsletter}/edit", name="administration_newsletter_edit")
     *
     * @return Response
     */
    public function editAction(Request $request, Newsletter $newsletter)
    {
        $this->denyAccessUnlessGranted(NewsletterVoter::EDIT, $newsletter);

        //process form
        $myForm = $this->handleUpdateForm($request, $newsletter);
        if ($myForm instanceof Response) {
            return $myForm;
        }

        $this->newsletter = $newsletter;

        return $this->render('administration/newsletter/edit.html.twig', ['form' => $myForm->createView()]);
    }

    /**
     * @Route("/{newsletter}/entries", name="administration_newsletter_entries")
     *
     * @return Response
     */
    public function entriesAction(Newsletter $newsletter)
    {
        /** @var Entry[] $approvedEntries */
        $approvedEntries = [];
        /** @var Entry[] $newEntries */
        $newEntries = [];
        foreach ($newsletter->getEntries() as $entry) {
            if ($entry->getApprovedAt() !== null) {
                $approvedEntries[] = $entry;
            } elseif ($entry->getRejectReason() === null) {
                $newEntries[] = $entry;
            }
        }

        $this->newsletter = $newsletter;

        return $this->render('administration/newsletter/entries.html.twig', [
            'newsletter' => $newsletter,
            'approved_entries' => $approvedEntries,
            'new_entries' => $newEntries,
        ]);
    }

    /**
     * @Route("/{newsletter}/change_order", name="administration_newsletter_change_order")
     *
     * @return Response
     */
    public function changeOrderAction(Newsletter $newsletter)
    {
        $entries = $this->getDoctrine()->getRepository(Entry::class)->findApprovedByNewsletter($newsletter->getId());

        $this->newsletter = $newsletter;

        return $this->render('administration/newsletter/change_order.html.twig', [
            'newsletter' => $newsletter,
            'entries' => $entries,
        ]);
    }

    /**
     * @Route("/{newsletter}/send", name="administration_newsletter_send")
     *
     * @return Response
     */
    public function sendAction(Request $request, Newsletter $newsletter, NewsletterServiceInterface $newsletterService)
    {
        $entries = $this->getDoctrine()->getRepository(Entry::class)->findApprovedByNewsletter($newsletter->getId());

        $form = $this->createFormBuilder()
            ->add('checkedTestEmail', CheckboxType::class)
            ->add('confirm', SubmitType::class)
            ->getForm();

        $sent = false;
        $this->handleForm($form, $request, function () use (&$sent, $newsletter, $newsletterService) {
            $sent = true;
            $newsletterService->send($newsletter);

            $newsletter->setSentAt(new \DateTime());
            $this->fastSave($newsletter);
        });

        if ($sent) {
            return $this->redirectToRoute('administration_newsletter', ['newsletter' => $newsletter->getId()]);
        }
        $newsletterService->sendTest($newsletter);

        $this->newsletter = $newsletter;

        return $this->render('administration/newsletter/send.html.twig', [
            'newsletter' => $newsletter,
            'entries' => $entries,
        ]);
    }

    /**
     * get the breadcrumbs leading to this controller.
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs()
    {
        $breadcrumbs = array_merge(parent::getIndexBreadcrumbs(), [
            new Breadcrumb(
                $this->generateUrl('administration'),
                $this->getTranslator()->trans('index.title', [], 'administration')
            ),
        ]);

        if ($this->newsletter !== null) {
            $breadcrumbs = array_merge($breadcrumbs, [
                new Breadcrumb(
                    $this->generateUrl('administration_newsletter', ['newsletter' => $this->newsletter->getId()]),
                    $this->newsletter->getPlannedSendAt()->format('d.m.Y')
                ),
            ]);
        }

        return $breadcrumbs;
    }
}
