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

use App\Model\Bill;
use App\Model\PaymentInfo;
use App\Model\TransactionInfo;
use App\Service\Interfaces\PaymentServiceInterface;
use Payrexx\Models\Response\Invoice;
use Payrexx\Payrexx;
use Payrexx\PayrexxException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentService implements PaymentServiceInterface
{
    /**
     * @var string
     */
    private $payrexxInstanceName;

    /**
     * @var string
     */
    private $payrexxSecret;

    /**
     * @var int
     */
    private $payrexxPsp;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(ParameterBagInterface $parameterBag, TranslatorInterface $translator)
    {
        $this->payrexxInstanceName = $parameterBag->get('PAYREXX_INSTANCE');
        $this->payrexxSecret = $parameterBag->get('PAYREXX_SECRET');
        $this->payrexxPsp = (int)$parameterBag->get('PAYREXX_PSP');

        $this->translator = $translator;
    }

    /**
     * @throws PayrexxException
     *
     * @return Payrexx
     */
    private function getPayrexx()
    {
        return new Payrexx($this->payrexxInstanceName, $this->payrexxSecret);
    }

    /**
     * {@inheritdoc}
     */
    public function startPayment(Bill $bill, string $successUrl)
    {
        $invoice = new Invoice();
        $invoice->setReferenceId($bill->getId()); // info for payment link (reference id)

        $title = $this->translator->trans('index.title', [], 'payment');
        $description = $this->translator->trans('index.description', [], 'payment');
        $invoice->setTitle($title);
        $invoice->setDescription($description);

        $billingPeriod = $this->translator->trans('index.billing_period', [], 'payment');
        $purpose = $title . ' ' . $billingPeriod . ' ' . $bill->getPeriodStart()->format('d.m.Y') . ' - ' . $bill->getPeriodEnd()->format('d.m.Y');
        $invoice->setPurpose($purpose);

        $invoice->setPsp($this->payrexxPsp); // see http://developers.payrexx.com/docs/miscellaneous
        $invoice->setSuccessRedirectUrl($successUrl);

        // don't forget to multiply by 100
        $invoice->setAmount($bill->getTotal() * 100);
        $invoice->setVatRate(null);
        $invoice->setCurrency('CHF');

        // add contact information fields which should be filled by customer
        $recipient = $bill->getRecipient();
        $invoice->addField($type = 'email', true, $recipient->getEmail());
        $invoice->addField($type = 'forename', true, $recipient->getGivenName());
        $invoice->addField($type = 'surname', true, $recipient->getFamilyName());
        $invoice->addField($type = 'street', true, $recipient->getStreet());
        $invoice->addField($type = 'postcode', true, $recipient->getPostcode());
        $invoice->addField($type = 'place', true, $recipient->getPlace());
        $invoice->addField($type = 'country', true, 'CH');

        /*
        we most likely do not need this
        $invoice->addField($type = 'terms', $mandatory = true);
        $invoice->addField($type = 'privacy_policy', $mandatory = true);
        */

        $payrexx = $this->getPayrexx();

        /** @var \Payrexx\Models\Response\Invoice $response */
        $response = $payrexx->create($invoice);

        $paymentInfo = new PaymentInfo();
        $paymentInfo->setInvoiceLink($response->getLink());
        $paymentInfo->setInvoiceId($response->getId());

        return $paymentInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function paymentSuccessful(PaymentInfo $paymentInfo, ?TransactionInfo &$transactionInfo)
    {
        $payrexx = $this->getPayrexx();

        $invoice = new Invoice();
        $invoice->setId($paymentInfo->getInvoiceId());

        /** @var Invoice $response */
        $response = $payrexx->getOne($invoice);
        if ($response->getStatus() !== 'confirmed') {
            return false;
        }

        $payedInvoice = $response->getInvoices()[0];
        $payedAmount = $payedInvoice['products'][0]['price'];

        $payedTransaction = $payedInvoice['transactions'][0];
        $transactionId = $payedTransaction['uuid'];

        $transactionInfo = new TransactionInfo($payedAmount, $transactionId);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function closePayment(PaymentInfo $paymentInfo)
    {
        $payrexx = $this->getPayrexx();

        $invoice = new Invoice();
        $invoice->setId($paymentInfo->getInvoiceId());

        $payrexx->delete($invoice);
    }
}
