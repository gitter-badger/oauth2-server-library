<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Grant;

use Jose\DecrypterInterface;
use Jose\LoaderInterface;
use Jose\Object\JWSInterface;
use Jose\VerifierInterface;
use OAuth2\Behaviour\HasConfiguration;
use OAuth2\Behaviour\HasExceptionManager;
use OAuth2\Behaviour\HasJWTLoader;
use OAuth2\Client\ClientInterface;
use OAuth2\Client\JWTClientInterface;
use OAuth2\Configuration\ConfigurationInterface;
use OAuth2\Exception\ExceptionManagerInterface;
use OAuth2\Util\RequestBody;
use Psr\Http\Message\ServerRequestInterface;

final class JWTBearerGrantType implements GrantTypeSupportInterface
{
    use HasExceptionManager;
    use HasConfiguration;
    use HasJWTLoader;

    /**
     * JWTBearerGrantType constructor.
     *
     * @param \Jose\LoaderInterface                        $loader
     * @param \Jose\VerifierInterface                      $verifier
     * @param \Jose\DecrypterInterface                     $decrypter
     * @param \OAuth2\Exception\ExceptionManagerInterface  $exception_manager
     * @param \OAuth2\Configuration\ConfigurationInterface $configuration
     */
    public function __construct(
        LoaderInterface $loader,
        VerifierInterface $verifier,
        DecrypterInterface $decrypter,
        ExceptionManagerInterface $exception_manager,
        ConfigurationInterface $configuration
    )
    {
        $this->setJWTLoader($loader);
        /**
         * @TODO
         */
        $this->setExceptionManager($exception_manager);
        $this->setConfiguration($configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function getGrantType()
    {
        return 'urn:ietf:params:oauth:grant-type:jwt-bearer';
    }

    /**
     * {@inheritdoc}
     */
    public function prepareGrantTypeResponse(ServerRequestInterface $request, GrantTypeResponseInterface &$grant_type_response)
    {
        $assertion = RequestBody::getParameter($request, 'assertion');
        if (null === $assertion) {
            throw $this->getExceptionManager()->getException(ExceptionManagerInterface::BAD_REQUEST, ExceptionManagerInterface::INVALID_REQUEST, 'Parameter "assertion" is missing.');
        }

        $jwt = $this->getJWTLoader()->load($assertion);
        if (!$jwt instanceof JWSInterface) {
            throw $this->getExceptionManager()->getException(ExceptionManagerInterface::BAD_REQUEST, ExceptionManagerInterface::INVALID_REQUEST, 'Assertion does not contain signed claims.');
        }

        if (!$jwt->hasClaim('sub')) {
            throw $this->getExceptionManager()->getException(ExceptionManagerInterface::BAD_REQUEST, ExceptionManagerInterface::INVALID_REQUEST, 'Assertion does not contain "sub" claims.');
        }

        //We modify the response:
        // - We add the subject as the client public id
        // - We transmit the JWT to the response for further needs
        $grant_type_response->setClientPublicId($jwt->getClaim('sub'))
            ->setAdditionalData('jwt', $jwt);
    }

    /**
     * {@inheritdoc}
     */
    public function grantAccessToken(ServerRequestInterface $request, ClientInterface $client, GrantTypeResponseInterface &$grant_type_response)
    {
        if (!$client instanceof JWTClientInterface) {
            throw $this->getExceptionManager()->getException(ExceptionManagerInterface::BAD_REQUEST, ExceptionManagerInterface::INVALID_CLIENT, 'The client is not a JWT client');
        }
        $jwt = $grant_type_response->getAdditionalData('jwt');

        $this->getJWTLoader()->verifySignature($jwt, $client);

        $issue_refresh_token = $this->getConfiguration()->get('issue_refresh_token_with_client_credentials_grant_type', false);
        $scope = RequestBody::getParameter($request, 'scope');

        $grant_type_response->setRequestedScope($scope)
                 ->setAvailableScope(null)
                 ->setResourceOwnerPublicId($client->getPublicId())
                 ->setRefreshTokenIssued($issue_refresh_token)
                 ->setRefreshTokenScope($scope)
                 ->setRefreshTokenRevoked(null);
    }
}
