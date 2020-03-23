<?php

/*
 * This file is part of the vseth-musikzimmer-pay project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Controller\Administration\Base\BaseController;
use App\Entity\User;
use App\Enum\PaymentRemainderStatusType;
use App\Model\TransactionInfo;
use App\Security\Voter\Base\BaseVoter;
use App\Service\Interfaces\BillServiceInterface;
use App\Service\Interfaces\PaymentServiceInterface;
use App\Service\Interfaces\SettingsServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/payment")
 */
class PaymentController extends BaseController
{
    /**
     * @Route("/{user}", name="payment_index")
     *
     * @return Response
     */
    public function indexAction(User $user, BillServiceInterface $billService, SettingsServiceInterface $settingsService)
    {
        $this->ensureAccessGranted($user);

        if ($user->getPaymentRemainderStatus() === PaymentRemainderStatusType::PAYMENT_SUCCESSFUL) {
            return $this->redirectToRoute('payment_successful', ['user' => $user->getId()]);
        } elseif ($user->getPaymentRemainderStatus() === PaymentRemainderStatusType::PAYMENT_STARTED) {
            return $this->redirectToRoute('payment_confirm', ['user' => $user->getId()]);
        }

        if (\in_array('ROLE_USER', $this->getUser()->getRoles(), true)) {
            $user->setPaymentRemainderStatus(PaymentRemainderStatusType::SEEN);
            $this->fastSave($user);
        }

        $bill = $billService->createBill($user);
        $setting = $settingsService->get();

        return $this->render('payment/view.html.twig', ['user' => $user, 'bill' => $bill, 'setting' => $setting]);
    }

    /**
     * @Route("/{user}/confirm", name="payment_confirm")
     *
     * @throws \Payrexx\PayrexxException
     * @throws \Exception
     *
     * @return Response
     */
    public function confirmAction(User $user, BillServiceInterface $billService, PaymentServiceInterface $paymentService)
    {
        $this->ensureAccessGranted($user);

        if ($user->getPaymentRemainderStatus() === PaymentRemainderStatusType::PAYMENT_STARTED) {
            /** @var TransactionInfo $transactionInfo */
            $successful = $paymentService->paymentSuccessful($user->getPaymentInfo(), $transactionInfo);
            if (!$successful) {
                if ($user->getInvoiceLink() === null) {
                    throw new \Exception('payment started but no invoice saved');
                }

                return $this->redirect($user->getInvoiceLink());
            }

            $user->setAmountPayed($transactionInfo->getAmount());
            $user->setTransactionId($transactionInfo->getId());
            $user->setPaymentRemainderStatus(PaymentRemainderStatusType::PAYMENT_SUCCESSFUL);
            $this->fastSave($user);
        }

        if ($user->getPaymentRemainderStatus() === PaymentRemainderStatusType::PAYMENT_SUCCESSFUL) {
            return $this->redirectToRoute('payment_successful', ['user' => $user->getId()]);
        }

        $successUrl = $this->generateUrl('payment_successful', ['user' => $user->getId()], RouterInterface::ABSOLUTE_URL);
        $bill = $billService->createBill($user);
        $paymentInfo = $paymentService->startPayment($bill, $successUrl);

        $user->writePaymentInfo($paymentInfo);
        $user->setPaymentRemainderStatus(PaymentRemainderStatusType::PAYMENT_STARTED);
        $this->fastSave($user);

        return $this->redirect($user->getInvoiceLink());
    }

    /**
     * @Route("/{user}/successful", name="payment_successful")
     *
     * @return Response
     */
    public function successfulAction(User $user)
    {
        $this->ensureAccessGranted($user);

        if ($user->getPaymentRemainderStatus() === PaymentRemainderStatusType::PAYMENT_STARTED) {
            return $this->redirectToRoute('payment_confirm', ['user' => $user->getId()]);
        }

        if ($user->getPaymentRemainderStatus() !== PaymentRemainderStatusType::PAYMENT_SUCCESSFUL) {
            return $this->redirectToRoute('payment_index', ['user' => $user->getId()]);
        }

        return $this->render('payment/successful.html.twig');
    }

    private function ensureAccessGranted(User $user)
    {
        $this->denyAccessUnlessGranted(BaseVoter::VIEW, $user);
    }
}
