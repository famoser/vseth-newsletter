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

use App\Entity\Reservation;
use App\Entity\User;
use App\Helper\DateTimeHelper;
use App\Model\ImportStatistics;
use App\Service\Interfaces\BillServiceInterface;
use App\Service\Interfaces\ImportServiceInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportService implements ImportServiceInterface
{
    /**
     * @var string
     */
    private $basePath;

    /** @var ManagerRegistry */
    private $doctrine;

    /** @var BillServiceInterface */
    private $billService;

    const DELIMITER = ',';

    /**
     * ImportService constructor.
     *
     * @param BillServiceInterface $billService
     */
    public function __construct(ParameterBagInterface $parameterBag, ManagerRegistry $doctrine, Interfaces\BillServiceInterface $billService)
    {
        $this->basePath = $parameterBag->get('UPLOAD_DIR');
        $this->doctrine = $doctrine;
        $this->billService = $billService;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function import(UploadedFile $users, UploadedFile $reservations, \DateTime $periodStart, \DateTime $periodEnd)
    {
        $reservationsPath = $this->uploadFile($reservations, 'reservations.csv');
        $usersPath = $this->uploadFile($users, 'users.csv');

        $periodStartString = $periodStart->format('Y-m-d');
        $periodEndString = $periodEnd->format('Y-m-d');

        /** @var string[] $lastSubscriptionEndByUser */
        $lastSubscriptionEndByUser = [];
        /** @var Reservation[][] $reservationsByUser */
        $reservationsByUser = [];
        $this->parseReservations($reservationsPath, $periodStartString, $periodEndString, $lastSubscriptionEndByUser, $reservationsByUser);

        /** @var User[] $users */
        $users = [];
        $this->parseUsers($usersPath, $reservationsByUser, $lastSubscriptionEndByUser, $users);

        $this->calculateAmountOwed($users);

        $this->saveAll($users, $reservationsByUser);

        $totalReservations = 0;
        foreach ($reservationsByUser as $item) {
            $totalReservations += \count($item);
        }

        $totalAmountOwed = 0;
        foreach ($users as $user) {
            $totalAmountOwed += $user->getAmountOwed();
        }

        return new ImportStatistics(\count($users), $totalReservations, $totalAmountOwed);
    }

    /**
     * @param User[] $users
     */
    private function calculateAmountOwed(array $users)
    {
        foreach ($users as $user) {
            $amountOwed = $this->billService->getAmountOwed($user);
            $user->setAmountOwed($amountOwed);
        }
    }

    /**
     * @throws \Exception
     */
    private function parseReservations(string $reservationsPath, string $periodStart, string $periodEnd, array &$lastSubscriptionEndByUser, array &$reservationsByUser)
    {
        $row = 1;
        if (($handle = fopen($reservationsPath, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, self::DELIMITER)) !== false) {
                if ($row++ === 1) {
                    $this->ensureReservationHeader($data);
                }

                $num = \count($data);
                if ($num < 9) {
                    continue;
                }

                $user = $data[5];
                $start = $data[7];
                if ($start < $periodStart) {
                    $periodDate = mb_substr($data[7], 0, 10); // 2019-01-01
                    if (isset($lastSubscriptionEndByUser[$user]) && $lastSubscriptionEndByUser[$user] > $periodDate) {
                        continue;
                    }

                    // remember new subscription
                    $newSubscriptiondEnd = DateTimeHelper::getSubscriptionEnd(new \DateTime($periodDate));
                    $lastSubscriptionEndByUser[$user] = $newSubscriptiondEnd->format('Y-m-d');

                    continue;
                }

                if ($start >= $periodStart && $start <= $periodEnd) {
                    $reservation = new Reservation();
                    $reservation->setStart(new \DateTime($start));
                    $reservation->setEnd(new \DateTime($data[8]));

                    if ($reservation->getEnd()->format('i') === '59') { // add one minute if datetime ends in :59
                        $newEnd = $reservation->getEnd()->add(new \DateInterval('PT1M'));
                        $reservation->setEnd($newEnd);
                    }

                    // abort if other weird date failures
                    if ($reservation->getEnd()->format('i') !== '00' || $reservation->getStart()->format('i') !== '00') {
                        throw new \Exception('can not handle non-full hour start/end times: start=' . $reservation->getStart()->format('c') . ' end=' . $reservation->getEnd()->format('c'));
                    }

                    $reservation->setRoom($data[6]);

                    $reservation->setModifiedAt(new \DateTime($data[2]));
                    $reservation->setCreatedAt(new \DateTime($data[3]));

                    $reservationsByUser[$user][] = $reservation;

                    continue;
                }
            }
            fclose($handle);
        }
    }

    /**
     * @param Reservation[][] $reservationsByUser
     * @param string[] $lastSubscriptionEndByUser
     * @param User[] $users
     *
     * @throws \Exception
     */
    private function parseUsers(string $usersPath, array &$reservationsByUser, array $lastSubscriptionEndByUser, array &$users)
    {
        $row = 1;
        if (($handle = fopen($usersPath, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, self::DELIMITER)) !== false) {
                if ($row++ === 1) {
                    $this->ensureUsersHeader($data);
                }

                if (\count($data) < 14) {
                    continue;
                }

                $userId = $data[0];
                if (!isset($reservationsByUser[$userId]) || \count($reservationsByUser[$userId]) === 0) {
                    continue;
                }

                $user = new User();
                $user->setGivenName($data[8]);
                $user->setFamilyName($data[9]);
                $user->setAddress($data[10]);
                $user->setEmail($data[11]);
                $user->setPhone($data[12]);
                $user->setCategory($data[14]);

                $subscriptionEnd = isset($lastSubscriptionEndByUser[$userId]) ? new \DateTime($lastSubscriptionEndByUser[$userId]) : null;
                $user->setLastPayedPeriodicFeeEnd($subscriptionEnd);

                foreach ($reservationsByUser[$userId] as $reservation) {
                    $reservation->setUser($user);
                    $user->getReservations()->add($reservation);
                }

                $users[] = $user;
            }
        }
    }

    private function ensureReservationHeader(array $data)
    {
        $expectedSize = 10;
        if (\count($data) !== $expectedSize) {
            throw new \Exception('reservation header wrong count');
        }

        $expectedContent = ['id', 'archived', 'moddate', 'createdate', 'position', 'user', 'raum', 'start', 'ende', 'apiid'];
        for ($i = 0; $i < $expectedSize; ++$i) {
            if ($data[$i] !== $expectedContent[$i]) {
                throw new \Exception('the header fields must be ' . implode('', $expectedContent) . ' in that order.');
            }
        }
    }

    private function ensureUsersHeader(array $data)
    {
        $expectedSize = 20;
        if (\count($data) !== $expectedSize) {
            throw new \Exception('reservation header wrong count');
        }

        $expectedContent = ['id', 'archived', 'moddate', 'createdate', 'passwort', 'seitenpagemounts', 'seitenentrytypes', 'anrede', 'vorname', 'nachname', 'adresse', 'email', 'tel', 'admin', 'category', 'passwordhash', 'badge', 'birthday', 'benutzername', 'pwchanged'];
        for ($i = 0; $i < $expectedSize; ++$i) {
            if ($data[$i] !== $expectedContent[$i]) {
                throw new \Exception('the header fields must be ' . implode('', $expectedContent) . ' in that order.');
            }
        }
    }

    private function uploadFile(UploadedFile $file, string $fileName)
    {
        $file->move($this->basePath, $fileName);

        return $this->basePath . \DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * @param User[] $users
     * @param Reservation[][] $reservationsByUser
     */
    private function saveAll(array $users, array $reservationsByUser)
    {
        $manager = $this->doctrine->getManager();

        foreach ($users as $user) {
            $user->generateAuthenticationCode();
            $manager->persist($user);
        }

        foreach ($reservationsByUser as $reservationsBySingleUser) {
            foreach ($reservationsBySingleUser as $reservation) {
                $manager->persist($reservation);
            }
        }

        $manager->flush();
    }
}
