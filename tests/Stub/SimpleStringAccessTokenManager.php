<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Test\Stub;

use OAuth2\Client\ClientInterface;
use OAuth2\Configuration\ConfigurationInterface;
use OAuth2\ResourceOwner\ResourceOwnerInterface;
use OAuth2\Token\AccessToken;
use OAuth2\Token\AccessTokenInterface;
use OAuth2\Token\RefreshTokenInterface;
use OAuth2\Token\SimpleStringAccessTokenManager as Base;

class SimpleStringAccessTokenManager extends Base
{
    /**
     * @var \OAuth2\Token\AccessTokenInterface[]
     */
    private $access_tokens = [];

    /**
     * AccessTokenManager constructor.
     *
     * @param \OAuth2\Configuration\ConfigurationInterface $configuration
     */
    public function __construct(ConfigurationInterface $configuration)
    {
        parent::__construct($configuration);

        $abcd = new AccessToken();
        $abcd->setExpiresAt(time() + 3600);
        $abcd->setResourceOwnerPublicId(null);
        $abcd->setScope([]);
        $abcd->setClientPublicId('bar');
        $abcd->setRefreshToken(null);
        $abcd->setToken('ABCD');

        $efgh = new AccessToken();
        $efgh->setExpiresAt(time() + 3600);
        $efgh->setResourceOwnerPublicId(null);
        $efgh->setScope([]);
        $efgh->setClientPublicId('foo');
        $efgh->setRefreshToken('REFRESH_EFGH');
        $efgh->setToken('EFGH');

        $this->access_tokens['ABCD'] = $abcd;
        $this->access_tokens['EFGH'] = $efgh;
    }

    /**
     * {@inheritdoc}
     */
    protected function addAccessToken($token, $expiresAt, ClientInterface $client, ResourceOwnerInterface $resourceOwner, array $scope = [], RefreshTokenInterface $refresh_token = null)
    {
        $access_token = new AccessToken();
        $access_token->setExpiresAt($expiresAt);
        $access_token->setScope($scope);
        $access_token->setResourceOwnerPublicId(null === $resourceOwner ? null : $resourceOwner->getPublicId());
        $access_token->setClientPublicId($client->getPublicId());
        $access_token->setRefreshToken(null === $refresh_token ? null : $refresh_token->getToken());
        $access_token->setToken($token);

        $this->access_tokens[$access_token->getToken()] = $access_token;

        return $access_token;
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken(AccessTokenInterface $access_token)
    {
        if (isset($this->access_tokens[$access_token->getToken()])) {
            unset($this->access_tokens[$access_token->getToken()]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($token)
    {
        return isset($this->access_tokens[$token]) ? $this->access_tokens[$token] : null;
    }
}
