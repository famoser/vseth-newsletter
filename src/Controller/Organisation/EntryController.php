<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Organisation;

use App\Controller\Administration\Base\BaseController;
use App\Entity\Entry;
use App\Entity\Organisation;
use App\Model\Breadcrumb;
use App\Security\Voter\EntryVoter;
use App\Security\Voter\OrganisationVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{organisation}/entry")
 */
class EntryController extends BaseController
{
    /**
     * @var Organisation
     */
    private $organisation;

    /**
     * @Route("/new", name="organisation_entry_new")
     *
     * @return Response
     */
    public function newAction(Organisation $organisation, Request $request)
    {
        $this->denyAccessUnlessGranted(OrganisationVoter::ADD_ENTRY, $organisation);

        if ($request->query->has('copy-id')) {
            $cloneEntry = $this->getDoctrine()->getRepository(Entry::class)->find($request->query->get('copy-id'));
            $this->denyAccessUnlessGranted(EntryVoter::VIEW, $cloneEntry);
            $entry = clone $cloneEntry;
        } else {
            $entry = new Entry();
            $entry->setOrganizer($organisation->getName());
        }

        $entry->setOrganisation($organisation);
        $entry->setPriority($organisation->getCategory() * 1000 + \ord($organisation->getName()));

        //process form
        $form = $this->handleCreateForm($request, $entry);
        if ($form instanceof Response) {
            return $form;
        }

        $this->organisation = $organisation;

        return $this->render('organisation/entry/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{entry}/edit", name="organisation_entry_edit")
     *
     * @return Response
     */
    public function editAction(Organisation $organisation, Request $request, Entry $entry)
    {
        $this->denyAccessUnlessGranted(EntryVoter::EDIT, $entry);

        $form = $this->handleUpdateForm($request, $entry);
        if ($form instanceof Response) {
            return $form;
        }

        $this->organisation = $organisation;

        return $this->render('organisation/entry/edit.html.twig', ['form' => $form->createView()]);
    }

    /**     *
     * @Route("/{entry}/remove", name="organisation_entry_remove")
     *
     * @return Response
     */
    public function removeAction(Organisation $organisation, Request $request, Entry $entry)
    {
        $this->denyAccessUnlessGranted(EntryVoter::EDIT, $entry);

        $form = $this->handleDeleteForm($request, $entry);
        if ($form === null) {
            return $this->redirectToRoute('organisation_view', ['organisation' => $organisation->getId()]);
        }

        $this->organisation = $organisation;

        return $this->render('organisation/entry/remove.html.twig', ['form' => $form->createView()]);
    }

    /**
     * get the breadcrumbs leading to this controller.
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs()
    {
        // test in frontend
        return array_merge(parent::getIndexBreadcrumbs(), [
            new Breadcrumb(
                $this->generateUrl('organisation_view', ['organisation' => $this->organisation->getId()]),
                $this->getTranslator()->trans('view.title', [], 'organisation')
            ),
        ]);
    }
}
