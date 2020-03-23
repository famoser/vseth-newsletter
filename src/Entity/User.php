<?php

/*
 * This file is part of the vseth-musikzimmer-pay project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Base\BaseEntity;
use App\Entity\Traits\IdTrait;
use App\Enum\PaymentRemainderStatusType;
use App\Enum\UserCategoryType;
use App\Model\Bill\Recipient;
use App\Model\PaymentInfo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * an event determines how the questionnaire looks like.
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 */
class User extends BaseEntity
{
    use IdTrait;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $authenticationCode;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $givenName;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $familyName;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $phone;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $category = UserCategoryType::STUDENT;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $discount = 0;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $discountDescription;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastPayedPeriodicFeeEnd;

    /**
     * this is in francs.
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $amountOwed;

    /**
     * this is in cents.
     *
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $amountPayed;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $transactionId;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $invoiceId;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $invoiceLink;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $paymentRemainderStatus = PaymentRemainderStatusType::NONE;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime",nullable=true)
     */
    private $paymentRemainderStatusAt;

    /**
     * @var PaymentRemainder|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\PaymentRemainder", inversedBy="users")
     */
    private $paymentRemainder;

    /**
     * @var Reservation[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Reservation", mappedBy="user")
     */
    private $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function generateAuthenticationCode(): void
    {
        $this->authenticationCode = Uuid::uuid4()->toString();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getGivenName(): string
    {
        return $this->givenName;
    }

    public function setGivenName(string $givenName): void
    {
        $this->givenName = $givenName;
    }

    public function getFamilyName(): string
    {
        return $this->familyName;
    }

    public function setFamilyName(string $familyName): void
    {
        $this->familyName = $familyName;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getCategory(): int
    {
        return $this->category;
    }

    public function setCategory(int $category): void
    {
        $this->category = $category;
    }

    public function getDiscount(): int
    {
        return $this->discount;
    }

    public function setDiscount(int $discount): void
    {
        $this->discount = $discount;
    }

    public function getDiscountDescription(): ?string
    {
        return $this->discountDescription;
    }

    public function setDiscountDescription(?string $discountDescription): void
    {
        $this->discountDescription = $discountDescription;
    }

    public function getLastPayedPeriodicFeeEnd(): ?\DateTime
    {
        return $this->lastPayedPeriodicFeeEnd;
    }

    public function setLastPayedPeriodicFeeEnd(?\DateTime $lastPayedPeriodicFeeEnd): void
    {
        $this->lastPayedPeriodicFeeEnd = $lastPayedPeriodicFeeEnd;
    }

    public function getAmountOwed(): int
    {
        return $this->amountOwed;
    }

    public function setAmountOwed(int $amountOwed): void
    {
        $this->amountOwed = $amountOwed;
    }

    public function getInvoiceId(): ?int
    {
        return $this->invoiceId;
    }

    public function setInvoiceId(?int $invoiceId): void
    {
        $this->invoiceId = $invoiceId;
    }

    public function getInvoiceLink(): ?string
    {
        return $this->invoiceLink;
    }

    public function setInvoiceLink(?string $invoiceLink): void
    {
        $this->invoiceLink = $invoiceLink;
    }

    public function getPaymentRemainderStatus(): int
    {
        return $this->paymentRemainderStatus;
    }

    public function setPaymentRemainderStatus(int $paymentRemainderStatus): void
    {
        $this->paymentRemainderStatus = $paymentRemainderStatus;
        $this->paymentRemainderStatusAt = new \DateTime();
    }

    public function getPaymentRemainderStatusAt(): ?\DateTime
    {
        return $this->paymentRemainderStatusAt;
    }

    public function getPaymentRemainder(): ?PaymentRemainder
    {
        return $this->paymentRemainder;
    }

    public function setPaymentRemainder(?PaymentRemainder $paymentRemainder): void
    {
        $this->paymentRemainder = $paymentRemainder;
    }

    /**
     * @return Reservation[]|ArrayCollection
     */
    public function getReservations()
    {
        return $this->reservations;
    }

    public function writePaymentInfo(PaymentInfo $paymentInfo)
    {
        $this->invoiceId = $paymentInfo->getInvoiceId();
        $this->invoiceLink = $paymentInfo->getInvoiceLink();
    }

    /**
     * @return PaymentInfo
     */
    public function getPaymentInfo()
    {
        if ($this->invoiceId === null || $this->invoiceLink === null) {
            throw new \Exception('no payment info available');
        }

        $paymentInfo = new PaymentInfo();

        $paymentInfo->setInvoiceId($this->invoiceId);
        $paymentInfo->setInvoiceLink($this->invoiceLink);

        return $paymentInfo;
    }

    public function clearPaymentInfo()
    {
        $this->invoiceLink = null;
        $this->invoiceId = null;
    }

    /**
     * @return Recipient
     */
    public function createRecipient()
    {
        $recipient = new Recipient();
        $recipient->setEmail($this->email);

        $recipient->setGivenName($this->givenName);
        $recipient->setFamilyName($this->familyName);

        $addressLines = explode("\n", $this->address);
        if (\count($addressLines) > 0) {
            $recipient->setStreet($addressLines[0]);
        }

        if (\count($addressLines) > 1) {
            $cityLine = $addressLines[\count($addressLines) - 1];
            $firstSpace = mb_strpos($cityLine, ' ');
            if ($firstSpace !== false) {
                $recipient->setPostcode(mb_substr($cityLine, 0, $firstSpace));
                $recipient->setPlace(mb_substr($cityLine, $firstSpace + 1));
            } else {
                $recipient->setPlace($cityLine);
            }
        }

        return $recipient;
    }

    public function getAuthenticationCode(): string
    {
        return $this->authenticationCode;
    }

    public function getAmountPayed(): ?int
    {
        return $this->amountPayed;
    }

    public function setAmountPayed(?int $amountPayed): void
    {
        $this->amountPayed = $amountPayed;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $transactionId): void
    {
        $this->transactionId = $transactionId;
    }
}
