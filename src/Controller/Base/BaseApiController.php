<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Base;

use App\Entity\Organisation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class BaseApiController extends BaseDoctrineController
{
    public static function getSubscribedServices()
    {
        return parent::getSubscribedServices() +
            [
                'serializer' => SerializerInterface::class,
            ];
    }

    /**
     * @return SerializerInterface
     */
    private function getSerializer()
    {
        return $this->get('serializer');
    }

    /**
     * @param Organisation[] $organisations
     *
     * @return JsonResponse
     */
    protected function returnOrganisations($organisations)
    {
        return new JsonResponse(
            $this->getSerializer()->serialize(
                $organisations,
                'json',
                ['attributes' => ['name', 'email']]
            ),
            200,
            [],
            true
        );
    }
}
