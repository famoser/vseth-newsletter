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
use App\Entity\Category;
use App\Entity\Entry;
use App\Entity\Newsletter;
use App\Entity\Traits\PriorityTrait;
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
        $myForm = $this->handleCreateForm($request, $newsletter, function () use (&$created) {
            $created = true;

            return true;
        });

        //copy categories on create
        if ($created && \count($lastNewsletters) > 0) {
            $newCategories = [];
            foreach ($lastNewsletters[0]->getCategories() as $category) {
                $newCategory = new Category();
                $newCategory->setNewsletter($newsletter);

                $newCategory->setNameDe($category->getNameDe());
                $newCategory->setNameEn($category->getNameEn());
                $newCategory->setPriority($category->getPriority());

                $newCategories[] = $newCategory;
            }
            $this->fastSave(...$newCategories);
        }

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
    public function entriesAction(Request $request, Newsletter $newsletter, NewsletterServiceInterface $newsletterService, TranslatorInterface $translator)
    {
        // TODO: continue refactor to use newsletter publish model

        if ($request->request->has('submit')) {
            $orderedIds = $request->request->get('entry_id');

            /** @var Entry[][] $entryLookupByCategory */
            $entryLookupByCategory = [];
            foreach ($newsletter->getEntries() as $entry) {
                if (\in_array($entry->getId(), $orderedIds, true)) {
                    $categoryId = $entry->getCategory() !== null ? $entry->getCategory()->getId() : 'default';
                    $entryLookupByCategory[$categoryId][$entry->getId()] = $entry;
                }
            }

            foreach ($entryLookupByCategory as $categoryId => $entryLookup) {
                $entryIds = [];
                foreach ($orderedIds as $orderedId) {
                    if (array_key_exists($orderedId, $entryLookup)) {
                        $entryIds[] = $orderedId;
                    }
                }
                $this->savePriorities($entryLookupByCategory[$categoryId], $entryIds);
            }

            $success = $translator->trans('entries.success.priorities_saved', [], 'administration_newsletter');
            $this->displaySuccess($success);
        }

        $this->getDoctrine()->getManager()->refresh($newsletter);
        $newsletterModel = $newsletterService->createPublishModel($newsletter);

        /** @var Entry[] $newEntries */
        $newEntries = [];
        foreach ($newsletter->getEntries() as $entry) {
            if ($entry->getApprovedAt() === null && $entry->getRejectReason() === null) {
                $newEntries[] = $entry;
            }
        }

        $this->newsletter = $newsletter;

        return $this->render('administration/newsletter/entries.html.twig', [
            'newsletter' => $newsletterModel,
            'new_entries' => $newEntries,
        ]);
    }

    /**
     * @Route("/{newsletter}/categories", name="administration_newsletter_categories")
     *
     * @return Response
     */
    public function categoriesAction(Request $request, Newsletter $newsletter, TranslatorInterface $translator)
    {
        if ($request->request->has('submit')) {
            /** @var Category[] $categoryLookup */
            $categoryLookup = [];
            foreach ($newsletter->getCategories() as $category) {
                $categoryLookup[$category->getId()] = $category;
            }

            $this->savePriorities($categoryLookup, $request->request->get('category_id'));

            $success = $translator->trans('categories.success.priorities_saved', [], 'administration_newsletter');
            $this->displaySuccess($success);
        }

        $this->getDoctrine()->getManager()->refresh($newsletter);

        $this->newsletter = $newsletter;

        return $this->render('administration/newsletter/categories.html.twig', [
            'newsletter' => $newsletter,
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

    /**
     * @param PriorityTrait[] $priorityLookup
     * @param string[] $newEntryIdOrder
     */
    private function savePriorities(array $priorityLookup, array $newEntryIdOrder)
    {
        /** @var Entry[] $orderedEntries */
        $orderedEntries = [];
        foreach ($newEntryIdOrder as $entryId) {
            $orderedEntries[] = $priorityLookup[$entryId];
        }

        // adapt order of all decreased priorities
        $longestIncreasingSubsequence = $this->findLongestIncreasingSubsequenceWithLowestStart($newEntryIdOrder);
        $currentLISIndex = \count($longestIncreasingSubsequence) - 1;
        $maxPriority = PHP_INT_MAX;
        for ($i = \count($orderedEntries) - 1; $i >= 0; --$i) {
            if ($currentLISIndex >= 1 && $longestIncreasingSubsequence[$currentLISIndex] === $orderedEntries[$i]->getId()) {
                --$currentLISIndex;
                $maxPriority = $priorityLookup[$longestIncreasingSubsequence[$currentLISIndex]]->getPriority();
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

    /**
     * @param string[] $idOrder
     *
     * @return string[]
     */
    private function findLongestIncreasingSubsequenceWithLowestStart(array $idOrder)
    {
        $increasingSubsequences = [];
        foreach ($idOrder as $key => $currentValue) {
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
