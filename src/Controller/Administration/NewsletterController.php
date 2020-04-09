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
use Symfony\Contracts\Translation\TranslatorInterface;

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
        $defaultDiff = new \DateInterval('P14D');
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
     * @Route("/{newsletter}", name="administration_newsletter")
     *
     * @return Response
     */
    public function indexAction(Newsletter $newsletter)
    {
        $entryRepository = $this->getDoctrine()->getRepository(Entry::class);
        $moderateEntryCount = $entryRepository->count(['approvedAt' => null, 'rejectReason' => null, 'newsletter' => $newsletter->getId()]);

        $this->newsletter = $newsletter;

        return $this->render('administration/newsletter/index.html.twig', [
            'newsletter' => $newsletter,
            'moderate_entry_count' => $moderateEntryCount,
        ]);
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
     * @Route("/{newsletter}/change_priority", name="administration_newsletter_change_priority")
     *
     * @return Response
     */
    public function changePriorityAction(Request $request, Newsletter $newsletter, TranslatorInterface $translator)
    {
        $entries = $this->getDoctrine()->getRepository(Entry::class)->findApprovedByNewsletter($newsletter->getId());

        $this->newsletter = $newsletter;

        if ($request->request->has('entry_id')) {
            $this->savePriorities($newsletter, $request->request->get('entry_id'));

            $success = $translator->trans('change_priority.success', [], 'administration_newsletter');
            $this->displaySuccess($success);
        }

        $this->getDoctrine()->getManager()->refresh($newsletter);

        return $this->render('administration/newsletter/change_priority.html.twig', [
            'newsletter' => $newsletter,
            'entries' => $entries,
        ]);
    }

    /**
     * @Route("/{newsletter}/preview", name="administration_newsletter_preview")
     *
     * @return Response
     */
    public function previewAction(Newsletter $newsletter)
    {
        return $this->render('administration/newsletter/preview.html.twig', ['newsletter' => $newsletter]);
    }

    /**
     * @Route("/{newsletter}/send", name="administration_newsletter_send")
     *
     * @return Response
     */
    public function sendAction(Request $request, Newsletter $newsletter, NewsletterServiceInterface $newsletterService, TranslatorInterface $translator)
    {
        $testEmail = $this->getParameter('TEST_NEWSLETTER_EMAIL');
        $warningMessage = $translator->trans('send.checked_test_email', ['%email%' => $testEmail], 'administration_newsletter');
        $confirmButton = $translator->trans('send.send_to_all', [], 'administration_newsletter');

        $form = $this->createFormBuilder()
            ->add('checkedTestEmail', CheckboxType::class, ['translation_domain' => false, 'label' => $warningMessage])
            ->add('sendToAll', SubmitType::class, ['translation_domain' => false, 'label' => $confirmButton])
            ->getForm();

        $sent = false;
        $this->handleForm($form, $request, function () use (&$sent, $newsletter, $newsletterService) {
            $sent = true;
            $newsletterService->send($newsletter);

            $newsletter->setSentAt(new \DateTime());
            $this->fastSave($newsletter);
        });

        if ($sent) {
            $successMessage = $translator->trans('send.sent', [], 'administration_newsletter');
            $this->displaySuccess($successMessage);

            return $this->redirectToRoute('administration_newsletter', ['newsletter' => $newsletter->getId()]);
        }

        $newsletterService->sendTest($newsletter);

        $this->newsletter = $newsletter;

        return $this->render('administration/newsletter/send.html.twig', ['form' => $form->createView()]);
    }

    /**
     * get the breadcrumbs leading to this controller.
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs()
    {
        return $this->getNewsletterBreadcrumbs($this->newsletter);
    }

    private function savePriorities(Newsletter $newsletter, array $entryIds)
    {
        /** @var Entry[] $entryLookup */
        $entryLookup = [];
        foreach ($newsletter->getEntries() as $entry) {
            $entryLookup[$entry->getId()] = $entry;
        }

        /** @var Entry[] $orderedEntries */
        $orderedEntries = [];
        foreach ($entryIds as $entryId) {
            $orderedEntries[] = $entryLookup[$entryId];
        }

        // adapt order of all decreased priorities
        $longestIncreasingSubsequence = $this->findLongestIncreasingSubsequenceWithLowestStart($entryIds);
        $currentLISIndex = \count($longestIncreasingSubsequence) - 1;
        $maxPriority = PHP_INT_MAX;
        for ($i = \count($orderedEntries) - 1; $i >= 0; --$i) {
            if ($currentLISIndex >= 0 && $longestIncreasingSubsequence[$currentLISIndex] === $orderedEntries[$i]->getId()) {
                --$currentLISIndex;
                $maxPriority = $entryLookup[$longestIncreasingSubsequence[$currentLISIndex]]->getPriority();
                continue;
            }

            if ($orderedEntries[$i]->getPriority() >= $maxPriority) {
                $orderedEntries[$i]->setPriority($maxPriority - 1);
                --$maxPriority;
            }
        }

        // adapt order of all increased priorities & correct conflicts
        $minPriority = 0;
        foreach ($orderedEntries as $orderedEntry) {
            if ($minPriority >= $orderedEntry->getPriority()) {
                $orderedEntry->setPriority($minPriority + 1);
            }

            $minPriority = $orderedEntry->getPriority();
        }

        $this->fastSave(...$orderedEntries);
    }

    private function findLongestIncreasingSubsequenceWithLowestStart(array $entries)
    {
        $increasingSubsequences = [];
        foreach ($entries as $key => $currentValue) {
            $increasingSubsequences[$key][0] = $currentValue;
            for ($i = $key - 1; $i >= 0; --$i) {
                $lastIndex = \count($increasingSubsequences[$i]) - 1;
                $lastValue = $increasingSubsequences[$i][$lastIndex];
                if ($currentValue > $lastValue) {
                    $increasingSubsequences[$i][] = $currentValue;
                }
            }
        }

        $longestIncreasingSubsequence = [];
        foreach ($increasingSubsequences as $increasingSubsequence) {
            if (\count($increasingSubsequence) > \count($longestIncreasingSubsequence)) {
                $longestIncreasingSubsequence = $increasingSubsequence;
            }
        }

        return $longestIncreasingSubsequence;
    }
}
