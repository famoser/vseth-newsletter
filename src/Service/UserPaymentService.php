<?php

/*
 * This file is part of the vseth-musikzimmer-pay project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\PaymentRemainder;
use App\Entity\User;
use App\Enum\PaymentRemainderStatusType;
use App\Service\Interfaces\EmailServiceInterface;
use App\Service\Interfaces\PaymentServiceInterface;
use App\Service\Interfaces\UserPaymentServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\RouterInterface;

class UserPaymentService implements UserPaymentServiceInterface
{
    /**
     * @var PaymentServiceInterface
     */
    private $paymentService;

    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var EmailServiceInterface
     */
    private $emailService;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * UserPaymentService constructor.
     *
     * @param EmailServiceInterface $emailService
     */
    public function __construct(PaymentServiceInterface $paymentService, ManagerRegistry $doctrine, Interfaces\EmailServiceInterface $emailService, \Symfony\Component\Routing\RouterInterface $router)
    {
        $this->paymentService = $paymentService;
        $this->doctrine = $doctrine;
        $this->emailService = $emailService;
        $this->router = $router;
    }

    /**
     * @throws \Payrexx\PayrexxException
     * @throws \Exception
     */
    public function closeInvoice(User $user)
    {
        $this->paymentService->closePayment($user->getPaymentInfo());
        $user->setPaymentRemainderStatus(PaymentRemainderStatusType::PAYMENT_ABORTED);
        $user->clearPaymentInfo();

        $this->save($user);
    }

    public function sendPaymentRemainder(User $user)
    {
        $paymentRemainder = $this->doctrine->getRepository(PaymentRemainder::class)->findActive();

        $body = $paymentRemainder->getBody();
        $url = $this->router->generate('login_code', ['code' => $user->getAuthenticationCode()], RouterInterface::ABSOLUTE_URL);
        $body = str_replace('(url)', $url, $body);
        $name = $user->getGivenName() . ' ' . $user->getFamilyName();
        $body = str_replace('(name)', $name, $body);

        $this->emailService->sendEmail($user->getEmail(), $paymentRemainder->getSubject(), $body);

        if ($user->getPaymentRemainder() !== $paymentRemainder) {
            $user->setPaymentRemainderStatus(PaymentRemainderStatusType::SENT);
        }
        $user->setPaymentRemainder($paymentRemainder);
        $this->save($user);
    }

    private function save(User $user)
    {
        $manager = $this->doctrine->getManager();
        $manager->persist($user);
        $manager->flush();
    }
}
