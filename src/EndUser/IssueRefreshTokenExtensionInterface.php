<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\EndUser;

use OAuth2\Client\ClientInterface;

interface IssueRefreshTokenExtensionInterface
{
    /**
     * Indicates if the end user allows the issuance of refresh tokens according to the client and the grant type.
     *
     * @param \OAuth2\Client\ClientInterface $client     The client
     * @param string                         $grant_type The grant type
     *
     * @return bool|null Return true if refresh token issuance is allowed for the client and grant type. Returns false if not allowed or null if no rule is set.
     */
    public function isRefreshTokenIssuanceAllowed(ClientInterface $client, $grant_type);
}
