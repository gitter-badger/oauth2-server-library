<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Token;

use OAuth2\Client\ClientInterface;
use OAuth2\ResourceOwner\ResourceOwnerInterface;

/**
 * @see    http://tools.ietf.org/html/rfc6749#section-6
 * @see    http://tools.ietf.org/html/rfc6749#section-1.5
 */
interface RefreshTokenManagerInterface
{
    /**
     * Grant refresh access tokens.
     *
     * Retrieve the stored data for the given refresh token.
     *
     * @param string $refreshToken Refresh token string.
     *
     * @return \OAuth2\Token\RefreshTokenInterface
     *
     * @see     http://tools.ietf.org/html/rfc6749#section-6
     */
    public function getRefreshToken($refreshToken);

    /**
     * Take the provided refresh token values and store them somewhere.
     *
     * This function should be the storage counterpart to getRefreshToken().
     * If storage fails for some reason, we're not currently checking for
     * any sort of success/failure, so you should bail out of the script
     * and provide a descriptive fail message.
     *
     * @param \OAuth2\Client\ClientInterface               $client        The client associated with this refresh token.
     * @param \OAuth2\ResourceOwner\ResourceOwnerInterface $resourceOwner Resource owner associated with the refresh token.
     * @param string[]                                     $scope         (optional) Scopes of the refresh token.
     */
    public function createRefreshToken(ClientInterface $client, ResourceOwnerInterface $resourceOwner, array $scope = []);

    /**
     * Revoke a refresh token.
     *
     * @param \OAuth2\Token\RefreshTokenInterface $refreshToken The refresh token string to revoke.
     */
    public function revokeRefreshToken(RefreshTokenInterface $refreshToken);

    /**
     * Expire a used refresh token.
     *
     * This is not explicitly required in the spec, but is almost implied. After granting a new refresh token, the old
     * one is no longer useful and so should be forcibly expired in the data store so it can't be used again.
     * If storage fails for some reason, we're not currently checking for any sort of success/failure, so you should
     * bail out of the script and provide a descriptive fail message.
     *
     * @param \OAuth2\Token\RefreshTokenInterface $refreshToken The refresh token string to expire.
     */
    public function markRefreshTokenAsUsed(RefreshTokenInterface $refreshToken);
}
