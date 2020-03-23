<?php

/*
 * This file is part of the vseth-musikzimmer-pay project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Administration;

use App\Controller\Administration\Base\BaseController;
use App\Entity\Setting;
use App\Entity\User;
use App\Form\Setting\InitializeType;
use App\Service\Interfaces\ImportServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/import")
 */
class ImportController extends BaseController
{
    /**
     * @Route("", name="administration_import")
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function indexAction(Request $request, TranslatorInterface $translator, ImportServiceInterface $importService)
    {
        /** @var User[] $users */
        $users = $this->getDoctrine()->getRepository(User::class)->findBy([], ['email' => 'ASC']);
        if (\count($users) > 0) {
            $error = $translator->trans('index.error.already_imported', [], 'administration_import');
            $this->displayError($error);

            return $this->redirectToRoute('administration');
        }

        $myForm = $this->getForm($request, $translator, $importService);
        if ($myForm instanceof Response) {
            return $myForm;
        }

        $help = $translator->trans('index.server_limits', ['upload_max_filesize' => ini_get('upload_max_filesize'), 'post_max_size' => ini_get('post_max_size')], 'administration_import');

        return $this->render('administration/import.html.twig', ['form' => $myForm->createView(), 'server_limit_info' => $help]);
    }

    /**
     * @throws \Exception
     *
     * @return FormInterface
     */
    private function getForm(Request $request, TranslatorInterface $translator, ImportServiceInterface $importService)
    {
        $entity = $this->getDefaultSetting();

        //create persist callable
        $myOnSuccessCallable = function ($form) use ($entity, $translator, $importService) {
            /* @var FormInterface $form */
            $this->fastSave($entity);

            /** @var $reservations UploadedFile */
            $reservations = $form->get('reservations')->getData();
            /** @var $users UploadedFile */
            $users = $form->get('users')->getData();
            $importStatistic = $importService->import($users, $reservations, $entity->getPeriodStart(), $entity->getPeriodEnd());

            $successfulText = $translator->trans('index.success', ['user_count' => $importStatistic->getUserCount(), 'reservation_count' => $importStatistic->getReservationCount(), 'owed_amount' => $importStatistic->getTotalAmountOwed()], 'administration_import');
            $this->displaySuccess($successfulText);

            return $this->redirectToRoute('administration');
        };

        //handle the form
        $buttonLabel = $translator->trans('form.submit_buttons.update', [], 'framework');

        return $this->handleForm(
            $this->createForm(InitializeType::class, $entity)
                ->add('submit', SubmitType::class, ['label' => $buttonLabel, 'translation_domain' => false]),
            $request,
            $myOnSuccessCallable
        );
    }

    /**
     * @throws \Exception
     */
    private function getDefaultSetting(): Setting
    {
        $year = (new \DateTime())->format('Y') - 1;

        $setting = new Setting();
        $setting->setPeriodStart(new \DateTime('01.01.' . $year . ' 00:00:00'));
        $setting->setPeriodEnd(new \DateTime('31.12.' . $year . ' 23:59:59'));
        $setting->setPaymentPrefix('musikzimmer-' . $year);

        return $setting;
    }
}
