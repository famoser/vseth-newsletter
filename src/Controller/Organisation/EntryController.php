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
use App\Security\Voter\Base\BaseVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/entry")
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
        $this->denyAccessUnlessGranted(BaseVoter::VIEW, $organisation);

        //create the event
        $entry = new Entry();
        $entry->setOrganisation($organisation);
        $entry->setOrganizer($organisation->getName());

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
        $this->ensureAccessGranted($entry);

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
        $this->ensureAccessGranted($entry);

        $form = $this->handleDeleteForm($request, $entry);
        if ($form === null) {
            return $this->redirectToRoute('organisation_view', ['organisation' => $organisation->getId()]);
        }

        $this->organisation = $organisation;

        return $this->render('organisation/entry/remove.html.twig', ['form' => $form->createView()]);
    }

    private function ensureAccessGranted(Entry $entry)
    {
        $this->denyAccessUnlessGranted(BaseVoter::VIEW, $entry);
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
