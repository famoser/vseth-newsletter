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

use App\Entity\Setting;
use App\Service\Interfaces\SettingsServiceInterface;
use Doctrine\Persistence\ManagerRegistry;

class SettingService implements SettingsServiceInterface
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * SettingService constructor.
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        return $this->doctrine->getRepository(Setting::class)->findOneBy([]);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Setting $setting)
    {
        $manager = $this->doctrine->getManager();
        $manager->persist($setting);
        $manager->flush();
    }
}
