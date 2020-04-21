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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

        $entry = new Entry();
        $entry->setOrganizer($organisation->getName());
        if ($request->query->has('copy-id')) {
            /** @var Entry $cloneEntry */
            $cloneEntry = $this->getDoctrine()->getRepository(Entry::class)->find($request->query->get('copy-id'));
            if ($cloneEntry === null) {
                throw new NotFoundHttpException();
            }

            $this->denyAccessUnlessGranted(EntryVoter::VIEW, $cloneEntry);
            $entry->setOrganizer($cloneEntry->getOrganizer());
            $entry->setTitleDe($cloneEntry->getTitleDe());
            $entry->setTitleEn($cloneEntry->getTitleEn());
            $entry->setDescriptionDe($cloneEntry->getDescriptionDe());
            $entry->setDescriptionEn($cloneEntry->getDescriptionEn());
            $entry->setLinkDe($cloneEntry->getLinkDe());
            $entry->setLinkEn($cloneEntry->getLinkEn());
            $entry->setStartDate($cloneEntry->getStartDate());
            $entry->setStartTime($cloneEntry->getStartTime());
            $entry->setEndDate($cloneEntry->getEndDate());
            $entry->setEndTime($cloneEntry->getEndTime());
            $entry->setLocation($cloneEntry->getLocation());
        }

        $entry->setOrganisation($organisation);
        $entry->setPriority($organisation->getDefaultPriority());

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
        return $this->getOrganisationBreadcrumbs($this->organisation);
    }
}
