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

use App\Entity\User;
use App\Enum\RoomType;
use App\Enum\UserCategoryType;
use App\Helper\DateTimeHelper;
use App\Model\Bill;
use App\Model\Bill\Reservation;
use App\Model\Bill\Subscription;
use App\Service\Interfaces\BillServiceInterface;
use App\Service\Interfaces\SettingsServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class BillService implements BillServiceInterface
{
    /**
     * @var SettingsServiceInterface
     */
    private $settingService;

    /**
     * @var TranslatorInterface
     */
    private $translatorService;

    /**
     * BillService constructor.
     */
    public function __construct(SettingsServiceInterface $settingService, TranslatorInterface $translatorService)
    {
        $this->settingService = $settingService;
        $this->translatorService = $translatorService;
    }

    /**
     * @throws \Exception
     *
     * @return Bill
     */
    public function createBill(User $user)
    {
        $bill = new Bill();

        $bill->setRecipient($user->createRecipient());

        $setting = $this->settingService->get();
        $bill->setId($setting->getPaymentPrefix() . '-' . $user->getId() . '-' . $user->getPaymentRemainder()->getId());
        $bill->setPeriodStart($setting->getPeriodStart());
        $bill->setPeriodEnd($setting->getPeriodEnd());
        $bill->setCategory($user->getCategory());

        $reservations = $this->getReservations($user->getReservations()->toArray(), $user->getCategory(), $reservationsSubtotal);
        $bill->setReservations($reservations);
        $bill->setReservationsSubtotal($reservationsSubtotal);
        $bill->setTotal($bill->getTotal() + $reservationsSubtotal);

        $bill->setLastPayedSubscriptionEnd($user->getLastPayedPeriodicFeeEnd());
        $subscriptions = $this->getSubscriptions($reservations, $user->getLastPayedPeriodicFeeEnd(), $user->getCategory(), $subscriptionsSubtotal);
        $bill->setSubscriptions($subscriptions);
        $bill->setSubscriptionsSubtotal($subscriptionsSubtotal);
        $bill->setTotal($bill->getTotal() + $subscriptionsSubtotal);

        $remainderFee = $user->getPaymentRemainder()->getFee();
        $bill->setBillFee($remainderFee);
        $bill->setTotal($bill->getTotal() + $remainderFee);

        $bill->setDiscount($user->getDiscount());
        $bill->setDiscountDescription($user->getDiscountDescription());
        $bill->setTotal($bill->getTotal() - $user->getDiscount());

        return $bill;
    }

    /**
     * @throws \Exception
     *
     * @return int
     */
    public function getAmountOwed(User $user)
    {
        $reservations = $this->getReservations($user->getReservations()->toArray(), $user->getCategory(), $reservationsSubtotal);

        $this->getSubscriptions($reservations, $user->getLastPayedPeriodicFeeEnd(), $user->getCategory(), $subscriptionsSubtotal);

        return $reservationsSubtotal + $subscriptionsSubtotal;
    }

    /**
     * @param \App\Entity\Reservation[] $reservations
     * @param int $reservationTotal
     *
     * @return Reservation[]
     */
    private function getReservations(array $reservations, int $userCategory, &$reservationTotal)
    {
        $reservationTotal = 0;
        $models = [];
        foreach ($reservations as $reservation) {
            $model = new Reservation();

            $model->setStartAt($reservation->getStart());
            $model->setEndAt($reservation->getEnd());

            $room = RoomType::getTranslation($reservation->getRoom(), $this->translatorService);
            $model->setRoom($room);
            $model->setPricePerHour($this->getPricePerHour($reservation->getRoom(), $userCategory));

            $duration = $model->getStartAt()->diff($model->getEndAt());
            $hours = $this->getTotalHours($duration);
            $model->setTotal($hours * $model->getPricePerHour());

            $reservationTotal += $model->getTotal();
            $models[] = $model;
        }

        return $models;
    }

    /**
     * @param Reservation[] $reservations
     * @param \DateTime $lastPayedPeriodicFeeEnd
     * @param int $subscriptionTotal
     *
     * @throws \Exception
     *
     * @return Subscription[]
     */
    private function getSubscriptions(array $reservations, ?\DateTime $lastPayedPeriodicFeeEnd, int $userCategory, &$subscriptionTotal)
    {
        $subscriptionTotal = 0;

        $dateFormat = 'Y-m-d';
        $currentLastValidDate = $lastPayedPeriodicFeeEnd !== null ? $lastPayedPeriodicFeeEnd->format($dateFormat) : '';
        /** @var Subscription[] $subscriptions */
        $subscriptions = [];
        foreach ($reservations as $reservation) {
            $currentDate = $reservation->getStartAt()->format($dateFormat);
            if ($currentDate <= $currentLastValidDate) {
                continue;
            }

            $subscription = new Subscription();
            $subscription->setPrice($this->getSubscriptionPrice($userCategory));

            $subscription->setStartAt(new \DateTime($currentDate));
            $subscription->setEndAt(DateTimeHelper::getSubscriptionEnd(clone $subscription->getStartAt()));

            $subscriptionTotal += $subscription->getPrice();
            $subscriptions[] = $subscription;

            $currentLastValidDate = $subscription->getEndAt()->format($dateFormat);
        }

        return $subscriptions;
    }

    /**
     * @return float|int
     */
    private function getTotalHours(\DateInterval $int)
    {
        return ($int->days * 24) + $int->h;
    }

    /**
     * @return int
     */
    private function getPricePerHour(int $room, int $userCategory)
    {
        // bandraum
        if ($room === RoomType::HPI_D_5_2) {
            switch ($userCategory) {
                case UserCategoryType::STUDENT:
                    return 5;
                case UserCategoryType::PHD:
                    return 10;
                case UserCategoryType::ETH_UNIVERSITY_STAFF:
                    return 20;
                default:
                    return 30;
            }
        }

        switch ($userCategory) {
            case UserCategoryType::STUDENT:
                return 1;
            case UserCategoryType::PHD:
                return 3;
            case UserCategoryType::ETH_UNIVERSITY_STAFF:
                return 6;
            default:
                return 8;
        }
    }

    /**
     * @param int $room
     *
     * @return int
     */
    private function getSubscriptionPrice(int $userCategory)
    {
        switch ($userCategory) {
            case UserCategoryType::STUDENT:
                return 25;
            case UserCategoryType::PHD:
                return 40;
            case UserCategoryType::ETH_UNIVERSITY_STAFF:
                return 50;
            default:
                return 60;
        }
    }
}
