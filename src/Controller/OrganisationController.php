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
use App\Entity\Entry;
use App\Entity\Organisation;
use App\Model\User;
use App\Security\Voter\Base\BaseVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/organisation")
 */
class OrganisationController extends BaseController
{
    /**
     * @Route("/{organisation}", name="organisation_view")
     *
     * @return Response
     */
    public function viewAction(Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(BaseVoter::VIEW, $organisation);

        // remember last visit
        if (\in_array(User::ROLE_ORGANISATION, $this->getUser()->getRoles(), true)) {
            $organisation->setVisitOccurred();
            $this->fastSave($organisation);
        }

        $entries = $this->getDoctrine()->getRepository(Entry::class)->findBy(['organisation' => $organisation->getId()]);

        return $this->render('organisation/view.html.twig', ['entries' => $entries, 'organisation' => $organisation]);
    }
}
