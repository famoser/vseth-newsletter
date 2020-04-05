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
use App\Form\Entry\RejectEntryType;
use App\Model\Breadcrumb;
use App\Security\Voter\EntryVoter;
use App\Security\Voter\NewsletterVoter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/entry")
 */
class EntryController extends BaseController
{
    /**
     * @var Newsletter
     */
    private $newsletter;

    /**
     * @Route("/new/{newsletter}", name="administration_entry_new")
     *
     * @return Response
     */
    public function newAction(Request $request, Newsletter $newsletter)
    {
        $this->denyAccessUnlessGranted(NewsletterVoter::ADD_ENTRY, $newsletter);

        //create the entry
        $entry = new Entry();
        $entry->setPriority(0);
        $entry->setNewsletter($newsletter);

        //process form
        $form = $this->handleCreateForm($request, $entry);
        if ($form instanceof Response) {
            return $form;
        }

        //process form
        if ($form instanceof Response) {
            return $form;
        }

        $this->newsletter = $newsletter;

        return $this->render('administration/entry/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{entry}/edit", name="administration_entry_edit")
     *
     * @return Response
     */
    public function editAction(Request $request, Entry $entry)
    {
        $this->denyAccessUnlessGranted(EntryVoter::EDIT, $entry);

        $form = $this->handleUpdateForm($request, $entry);
        if ($form instanceof Response) {
            return $form;
        }

        //process form
        if ($form instanceof Response) {
            return $form;
        }

        $this->newsletter = $entry->getNewsletter();

        return $this->render('administration/entry/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * prepends admin to use admin forms.
     *
     * @param string $classWithNamespace
     * @param string $prepend
     * @param bool $repeatClass
     *
     * @return string
     */
    protected function classToFormType($classWithNamespace, $prepend = '', $repeatClass = true)
    {
        if ($prepend === '') {
            $prepend = 'Admin';
        }

        return parent::classToFormType($classWithNamespace, $prepend, $repeatClass);
    }

    /**
     * @Route("/{entry}/reject", name="administration_entry_reject")
     *
     * @return Response
     */
    public function rejectAction(Request $request, Entry $entry, TranslatorInterface $translator)
    {
        $this->denyAccessUnlessGranted(EntryVoter::EDIT, $entry);

        //create persist callable
        $myOnSuccessCallable = function () use ($entry, $translator) {
            $this->fastSave($entry);

            $successfulText = $translator->trans('reject.successful', [], 'administration_entry');
            $this->displaySuccess($successfulText);

            //recreate form so values are not filled out already
            return $this->redirectToRoute('administration_newsletter', ['newsletter' => $entry->getNewsletter()->getId()]);
        };

        //handle the form
        $buttonLabel = $translator->trans('reject.title', [], 'administration_entry');
        $form = $this->handleForm(
            $this->createForm(RejectEntryType::class, $entry)
                ->add('submit', SubmitType::class, ['label' => $buttonLabel, 'translation_domain' => false]),
            $request,
            $myOnSuccessCallable
        );

        if ($form instanceof Response) {
            return $form;
        }

        $this->newsletter = $entry->getNewsletter();

        return $this->render('administration/entry/reject.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{entry}/approve", name="administration_entry_approve")
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function approveAction(Entry $entry)
    {
        $this->denyAccessUnlessGranted(EntryVoter::EDIT, $entry);

        $entry->setApprovedAt(new \DateTime());
        $this->fastSave($entry);

        return $this->redirectToRoute('administration_newsletter_entries', ['newsletter' => $entry->getNewsletter()->getId()]);
    }

    /**
     * @Route("/{entry}/disapprove", name="administration_entry_disapprove")
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function disapproveAction(Entry $entry)
    {
        $this->denyAccessUnlessGranted(EntryVoter::EDIT, $entry);

        $entry->setApprovedAt(null);
        $this->fastSave($entry);

        return $this->redirectToRoute('administration_newsletter_entries', ['newsletter' => $entry->getNewsletter()->getId()]);
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
}
