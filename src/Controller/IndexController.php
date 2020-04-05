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

use App\Controller\Base\BaseDoctrineController;
use App\Entity\Organisation;
use App\Enum\OrganisationCategoryType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/")
 */
class IndexController extends BaseDoctrineController
{
    /**
     * @Route("", name="index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Security $security)
    {
        $user = $security->getUser();
        if ($user !== null && \in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->redirectToRoute('administration');
        }

        /** @var Organisation[] $organisations */
        $organisations = $this->getDoctrine()->getRepository(Organisation::class)->findBy(['hiddenAt' => null], ['name' => 'ASC']);
        /** @var Organisation[][] $organisationsByCategories */
        $organisationsByCategories = [
            OrganisationCategoryType::VSETH => [],
            OrganisationCategoryType::COMMISSION => [],
            OrganisationCategoryType::STUDY_ASSOCIATION => [],
            OrganisationCategoryType::ASSOCIATED => [],
            OrganisationCategoryType::RECOGNISED => [],
        ];

        foreach ($organisations as $organisation) {
            if (isset($organisationsByCategories[$organisation->getCategory()])) {
                $organisationsByCategories[$organisation->getCategory()][] = $organisation;
            }
        }

        return $this->render('index/index.html.twig', ['organisations_by_categories' => $organisationsByCategories]);
    }

    /**
     * no breadcrumbs on the index.
     *
     * @return \App\Model\Breadcrumb[]|array
     */
    protected function getIndexBreadcrumbs()
    {
        return [];
    }
}
