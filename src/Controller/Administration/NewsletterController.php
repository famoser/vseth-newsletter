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
use App\Entity\Newsletter;
use App\Model\Breadcrumb;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/newsletter")
 */
class NewsletterController extends BaseController
{
    /**
     * @Route("/new", name="administration_newsletter_new")
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
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

        //create the event
        $newsletter = new Newsletter();
        $newsletter->setPlannedSendAt($newStart);

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
        //process form
        $myForm = $this->handleUpdateForm($request, $newsletter);
        if ($myForm instanceof Response) {
            return $myForm;
        }

        return $this->render('administration/newsletter/edit.html.twig', ['form' => $myForm->createView()]);
    }

    /**
     * get the breadcrumbs leading to this controller.
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs()
    {
        return array_merge(parent::getIndexBreadcrumbs(), [
            new Breadcrumb(
                $this->generateUrl('administration'),
                $this->getTranslator()->trans('index.title', [], 'administration')
            ),
        ]);
    }
}
