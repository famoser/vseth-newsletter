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
use App\Entity\Organisation;
use App\Model\UserModel;
use App\Security\Voter\Base\BaseVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/organisation")
 */
class OrganisationController extends BaseController
{
    /**
     * @var Organisation
     */
    private $organisation;

    /**
     * @Route("/{organisation}", name="organisation_view")
     *
     * @return Response
     */
    public function viewAction(Organisation $organisation)
    {
        $this->denyAccessUnlessGranted(BaseVoter::VIEW, $organisation);

        // remember last visit
        if (\in_array(UserModel::ROLE_ORGANISATION, $this->getUser()->getRoles(), true)) {
            $organisation->setVisitOccurred();
            $this->fastSave($organisation);
        }

        $this->organisation = $organisation;

        return $this->render('organisation/view.html.twig', ['organisation' => $organisation]);
    }

    protected function getIndexBreadcrumbs()
    {
        return $this->getOrganisationBreadcrumbs($this->organisation);
    }
}
