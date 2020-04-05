<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\Organisation;
use App\Enum\OrganisationCategoryType;
use App\Service\Interfaces\SemesterlyReportsApiServiceInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SemesterlyReportsApiService implements SemesterlyReportsApiServiceInterface
{
    /**
     * @var string
     */
    private $semesterlyReportsApiUrl;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ParameterBagInterface $parameterBag, ManagerRegistry $managerRegistry, LoggerInterface $logger)
    {
        $this->semesterlyReportsApiUrl = $parameterBag->get('SEMESTERLY_REPORTS_API_URL');
        $this->managerRegistry = $managerRegistry;
        $this->logger = $logger;
    }

    public function isEnabled()
    {
        return mb_strlen($this->semesterlyReportsApiUrl) > 5;
    }

    public function syncRecognisedOrganisations()
    {
        $organisations = $this->managerRegistry->getRepository(Organisation::class)->findBy(['category' => OrganisationCategoryType::RECOGNISED]);
        /** @var Organisation[] $organisationLookup */
        $organisationLookup = [];
        foreach ($organisations as $organisation) {
            $organisationLookup[$organisation->getEmail()] = $organisation;
        }

        $added = 0;
        $updated = 0;
        $hidden = 0;

        $apiOrganisationsJson = file_get_contents($this->semesterlyReportsApiUrl . '/organisations');
        $apiOrganisations = json_decode($apiOrganisationsJson);

        $manager = $this->managerRegistry->getManager();
        foreach ($apiOrganisations as $apiOrganisation) {
            $name = $apiOrganisation->name;
            $email = $apiOrganisation->email;

            if (isset($organisationLookup[$email])) {
                $existing = $organisationLookup[$email];
                $existing->setName($name);
                $existing->unhide();
                $manager->persist($existing);

                ++$updated;
                unset($organisationLookup[$email]);
            } else {
                $organisation = new Organisation();
                $organisation->setEmail($email);
                $organisation->setName($name);
                $organisation->generateAuthenticationCode();
                $organisation->setCategory(OrganisationCategoryType::RECOGNISED);

                ++$added;
                $manager->persist($organisation);
            }
        }

        foreach ($organisationLookup as $organisation) {
            if ($organisation->getHiddenAt() === null) {
                $organisation->hide();
                $manager->persist($organisation);
                ++$hidden;
            }
        }

        $manager->flush();

        return [$added, $updated, $hidden];
    }
}
