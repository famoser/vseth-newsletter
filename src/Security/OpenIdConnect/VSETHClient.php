<?php

/*
 * This file is part of the vseth-newsletter project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\OpenIdConnect;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class VSETHClient.
 */
class VSETHClient implements ClientInterface
{
    /**
     * @var string
     */
    private $baseEndpoint = 'https://auth.vseth.ethz.ch/auth/realms/VSETH/protocol/openid-connect';

    /**
     * @var string
     */
    private $clientId;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->clientId = $parameterBag->get('OPEN_ID_CONNECT_CLIENT_ID');
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->clientId !== 'null';
    }

    /**
     * {@inheritdoc}
     */
    public function redirect(string $redirectUrl, string $state)
    {
        $parameters = [];
        $parameters['client_id'] = $this->clientId;
        $parameters['response_type'] = 'id_token';
        $parameters['redirect_uri'] = $redirectUrl;
        $parameters['scopes'] = 'roles vseth-profile';
        $parameters['state'] = $state;

        $queryies = [];
        foreach ($parameters as $key => $value) {
            $queryies[] = $key . '=' . $value;
        }

        $redirectUrl = $this->baseEndpoint . '/auth?' . implode('&', $queryies);

        return new RedirectResponse($redirectUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function login(Request $request)
    {
        // TODO: Implement login() method.
    }
}
