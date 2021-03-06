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

use OAuth2\Behaviour\HasAccessTokenManager;
use OAuth2\Endpoint\Authorization;
use OAuth2\Token\AccessTokenInterface;
use OAuth2\Token\AccessTokenManagerInterface;

/**
 * This response type has been introduced by OpenID Connect
 * It creates an access token, but does not returns anything.
 *
 * At this time, this response type is not complete, because it always redirect the client.
 * But if no redirect URI is specified, no redirection should occurred as per OpenID Connect specification.
 *
 * @see http://openid.net/specs/oauth-v2-multiple-response-types-1_0.html#none
 */
final class NoneResponseType implements ResponseTypeSupportInterface
{
    use HasAccessTokenManager;

    /**
     * NoneResponseType constructor.
     *
     * @param \OAuth2\Token\AccessTokenManagerInterface $access_token_manager
     */
    public function __construct(AccessTokenManagerInterface $access_token_manager)
    {
        $this->setAccessTokenManager($access_token_manager);
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseType()
    {
        return 'none';
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseMode()
    {
        return 'query';
    }

    /**
     * {@inheritdoc}
     */
    public function grantAuthorization(Authorization $authorization)
    {
        $token = $this->getAccessTokenManager()->createAccessToken($authorization->getClient(), $authorization->getEndUser(), $authorization->getScope());

        $params = [];
        $state = $authorization->getState();
        if (!empty($state)) {
            $params['state'] = $state;
        }

        return $params;
    }
}
