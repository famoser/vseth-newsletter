<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Administration\Base\BaseController;
use App\Entity\Newsletter;
use App\Model\Breadcrumb;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/administration")
 */
class AdministrationController extends BaseController
{
    /**
     * @Route("", name="administration")
     *
     * @return Response
     */
    public function indexAction()
    {
        $newsletters = $this->getDoctrine()->getRepository(Newsletter::class)->findBy([], ['plannedSendAt' => 'DESC']);

        return $this->render('administration.html.twig', ['newsletters' => $newsletters]);
    }

    /**
     * @return Breadcrumb[]|array
     */
    protected function getIndexBreadcrumbs()
    {
        return $this->getNewsletterBreadcrumbs(null);
    }
}
