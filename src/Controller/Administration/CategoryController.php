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
use App\Entity\Category;
use App\Entity\Entry;
use App\Entity\Newsletter;
use App\Model\Breadcrumb;
use App\Security\Voter\CategoryVoter;
use App\Security\Voter\EntryVoter;
use App\Security\Voter\NewsletterVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/category")
 */
class CategoryController extends BaseController
{
    /**
     * @var Newsletter
     */
    private $newsletter;

    /**
     * @Route("/new/{newsletter}", name="administration_category_new")
     *
     * @return Response
     */
    public function newAction(Request $request, Newsletter $newsletter)
    {
        $this->denyAccessUnlessGranted(NewsletterVoter::ADD_CATEGORY, $newsletter);

        //create the entry
        $entry = new Category();
        $entry->setNewsletter($newsletter);

        //process form
        $form = $this->handleCreateForm($request, $entry, function () use ($entry, $newsletter) {
            $max = 0;
            foreach ($newsletter->getCategories() as $category) {
                $max = max($category->getPriority(), $max);
            }
            $entry->setPriority($max + 1);

            return true;
        });
        if ($form instanceof Response) {
            return $form;
        }

        $this->newsletter = $newsletter;

        return $this->render('administration/category/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{category}/edit", name="administration_category_edit")
     *
     * @return Response
     */
    public function editAction(Request $request, Category $category)
    {
        $this->denyAccessUnlessGranted(CategoryVoter::EDIT, $category);

        $form = $this->handleUpdateForm($request, $category);
        if ($form instanceof Response) {
            return $form;
        }

        $this->newsletter = $category->getNewsletter();

        return $this->render('administration/category/edit.html.twig', ['form' => $form->createView()]);
    }

    /**     *
     * @Route("/{category}/remove", name="administration_category_remove")
     *
     * @return Response
     */
    public function removeAction(Request $request, Category $category)
    {
        $this->denyAccessUnlessGranted(EntryVoter::EDIT, $category);

        $form = $this->handleDeleteForm($request, $category);
        if ($form === null) {
            return $this->redirectToRoute('administration_newsletter_categories', ['newsletter' => $category->getNewsletter()->getId()]);
        }

        $this->newsletter = $category->getNewsletter();

        return $this->render('administration/category/remove.html.twig', ['form' => $form->createView()]);
    }

    /**
     * get the breadcrumbs leading to this controller.
     *
     * @return Breadcrumb[]
     */
    protected function getIndexBreadcrumbs()
    {
        return array_merge(
            $this->getNewsletterBreadcrumbs($this->newsletter),
            [new Breadcrumb(
                $this->generateUrl('administration_newsletter_categories', ['newsletter' => $this->newsletter->getId()]),
                $this->getTranslator()->trans('categories.title', [], 'administration_newsletter')
            )]
        );
    }
}
