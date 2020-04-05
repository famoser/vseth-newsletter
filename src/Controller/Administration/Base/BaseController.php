<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Administration\Base;

use App\Controller\Base\BaseFormController;
use App\Entity\Newsletter;
use App\Entity\Organisation;
use App\Model\Breadcrumb;

class BaseController extends BaseFormController
{
    protected function getNewsletterBreadcrumbs(?Newsletter $newsletter)
    {
        $breadcrumbs = [
            new Breadcrumb(
                $this->generateUrl('administration'),
                $this->getTranslator()->trans('index.title', [], 'administration')
            ),
        ];

        if ($newsletter !== null) {
            $breadcrumbs = array_merge($breadcrumbs, [
                new Breadcrumb(
                    $this->generateUrl('administration_newsletter', ['newsletter' => $newsletter->getId()]),
                    $newsletter->getPlannedSendAt()->format('d.m.Y')
                ),
            ]);
        }

        return $breadcrumbs;
    }

    protected function getOrganisationBreadcrumbs(Organisation $organisation)
    {
        return [
            new Breadcrumb(
                $this->generateUrl('organisation_view', ['organisation' => $organisation->getId()]),
                $this->getTranslator()->trans('index.title', [], 'index')
            ),
        ];
    }
}
