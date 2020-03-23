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
        $allNewsletters = $this->getDoctrine()->getRepository(Newsletter::class)->findAll();
        $futureNewsletters = [];
        $sentNewsletters = [];
        foreach ($allNewsletters as $newsletter) {
            if ($newsletter->getSentAt() === null) {
                $futureNewsletters[] = $newsletter;
            } else {
                $sentNewsletters[] = $newsletter;
            }
        }

        return $this->render('administration.html.twig', ['future_newsletters' => $futureNewsletters, 'sent_newsletters' => $sentNewsletters]);
    }
}
