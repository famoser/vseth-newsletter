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
use App\Entity\PaymentRemainder;
use App\Entity\Reservation;
use App\Entity\User;
use App\Enum\PaymentRemainderStatusType;
use App\Enum\RoomType;
use App\Enum\UserCategoryType;
use App\Model\Breadcrumb;
use App\Service\Interfaces\UserPaymentServiceInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/payment_remainder")
 */
class PaymentRemainderController extends BaseController
{
    /**
     * @Route("/new", name="administration_payment_remainder_new")
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function newAction(Request $request, TranslatorInterface $translator)
    {
        $paymentRemainder = new PaymentRemainder();
        $paymentRemainder->setName($translator->trans('default.name', [], 'entity_payment_remainder'));
        $paymentRemainder->setSubject($translator->trans('default.subject', [], 'entity_payment_remainder'));
        $paymentRemainder->setBody($translator->trans('default.body', ['support_email' => $this->getParameter('REPLY_EMAIL')], 'entity_payment_remainder'));

        $paymentRemainder->setFee(0);
        $paymentRemainder->setDueAt((new \DateTime('today'))->add(new \DateInterval('P1M1D')));

        //process form
        $saved = false;
        $myForm = $this->handleCreateForm(
            $request,
            $paymentRemainder,
            function () use ($paymentRemainder, $translator, &$saved) {
                if (!$this->ensureValidPaymentRemainder($paymentRemainder, $translator)) {
                    return false;
                }

                $saved = true;

                return true;
            }
        );
        if ($myForm instanceof Response) {
            return $myForm;
        }

        if ($saved) {
            return $this->redirectToRoute('administration');
        }

        return $this->render('administration/payment_remainder/new.html.twig', ['form' => $myForm->createView()]);
    }

    /**
     * @Route("/{paymentRemainder}/edit", name="administration_payment_remainder_edit")
     *
     * @return Response
     */
    public function editAction(Request $request, PaymentRemainder $paymentRemainder, TranslatorInterface $translator)
    {
        //process form
        $myForm = $this->handleUpdateForm($request, $paymentRemainder, function () use ($paymentRemainder, $translator) {
            return $this->ensureValidPaymentRemainder($paymentRemainder, $translator);
        });

        if ($myForm instanceof Response) {
            return $myForm;
        }

        return $this->render('administration/payment_remainder/edit.html.twig', ['form' => $myForm->createView()]);
    }

    /**
     * @Route("/send_test", name="administration_payment_remainder_send_test")
     *
     * @return Response
     */
    public function sendTestAction(TranslatorInterface $translator, UserPaymentServiceInterface $userPaymentService)
    {
        $replyEmail = $this->getParameter('REPLY_EMAIL');
        $testUser = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $replyEmail]);
        if ($testUser === null) {
            $testUser = $this->createTestUser($replyEmail);
        }

        // close or remove active invoice
        if ($testUser->getInvoiceId() !== null && $testUser->getPaymentRemainderStatus() === PaymentRemainderStatusType::PAYMENT_STARTED) {
            $userPaymentService->closeInvoice($testUser);
        }

        // reset user
        $testUser->clearPaymentInfo();
        $testUser->setPaymentRemainderStatus(PaymentRemainderStatusType::NONE);
        $this->fastSave($testUser);

        // send mail
        $userPaymentService->sendPaymentRemainder($testUser);

        $success = $translator->trans('send_test.successful', ['test_email' => $replyEmail], 'administration_payment_remainder');
        $this->displaySuccess($success);

        return $this->redirectToRoute('administration');
    }

    private function createTestUser(string $email): User
    {
        $testUser = new User();
        $testUser->setGivenName('first name (test)');
        $testUser->setFamilyName('last name (test)');
        $testUser->setPhone('phone (test)');
        $testUser->setEmail($email);
        $testUser->setAddress("steet (test)\ncity (test)");
        $testUser->setCategory(UserCategoryType::STUDENT);
        $testUser->setLastPayedPeriodicFeeEnd(null);
        $testUser->setDiscount(0);
        $testUser->setAmountOwed(27);
        $testUser->generateAuthenticationCode();

        $testReservation = new Reservation();
        $testReservation->setStart(new \DateTime('01.01.2020 03:00'));
        $testReservation->setEnd(new \DateTime('01.01.2020 04:00'));
        $testReservation->setCreatedAt(new \DateTime('01.01.2020 00:00'));
        $testReservation->setModifiedAt(new \DateTime('01.01.2020 00:00'));
        $testReservation->setUser($testUser);
        $testReservation->setRoom(RoomType::UNAUTHORIZED);

        $testReservation2 = new Reservation();
        $testReservation2->setStart(new \DateTime('02.01.2020 03:00'));
        $testReservation2->setEnd(new \DateTime('02.01.2020 04:00'));
        $testReservation2->setCreatedAt(new \DateTime('02.01.2020 00:00'));
        $testReservation2->setModifiedAt(new \DateTime('02.01.2020 00:00'));
        $testReservation2->setUser($testUser);
        $testReservation2->setRoom(RoomType::UNAUTHORIZED);

        $this->fastSave($testUser, $testReservation, $testReservation2);

        return $testUser;
    }

    /**
     * @Route("/send", name="administration_payment_remainder_send")
     *
     * @return Response
     */
    public function sendAction(TranslatorInterface $translator, UserPaymentServiceInterface $userPaymentService, ParameterBagInterface $parameterBag)
    {
        $paymentRemainder = $this->getDoctrine()->getRepository(PaymentRemainder::class)->findActive();
        if ($paymentRemainder->isSentToAll()) {
            return $this->redirectToRoute('administration');
        }

        $batchSize = $parameterBag->get('MAILER_BATCH_SIZE');

        $notPayedUsers = $this->getDoctrine()->getRepository(User::class)->findByNotPayed();
        foreach ($notPayedUsers as $notPayedUser) {
            if ($notPayedUser->getPaymentRemainder() === $paymentRemainder) {
                continue;
            }

            // close active invoice
            if ($notPayedUser->getInvoiceId() !== null) {
                $userPaymentService->closeInvoice($notPayedUser);
            }

            // do not send mail to admins/service
            if ($notPayedUser->getCategory() === UserCategoryType::ADMIN || $notPayedUser->getCategory() === UserCategoryType::SERVICE) {
                continue;
            }

            // send mail
            $userPaymentService->sendPaymentRemainder($notPayedUser);

            // stop if too many mails sent yet
            if (--$batchSize <= 0) {
                break;
            }
        }

        $paymentRemainder->setSentToAll(true);
        $this->fastSave($paymentRemainder);

        $success = $translator->trans('send.successful', [], 'administration_payment_remainder');
        $this->displaySuccess($success);

        return $this->redirectToRoute('administration');
    }

    /**
     * @return bool
     */
    private function ensureValidPaymentRemainder(PaymentRemainder $paymentRemainder, TranslatorInterface $translator)
    {
        if (mb_strrpos($paymentRemainder->getBody(), '(url)') === false) {
            $error = $translator->trans('new.error.missing_url', [], 'administration_payment_remainder');
            $this->displayError($error);

            return false;
        }

        return true;
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
