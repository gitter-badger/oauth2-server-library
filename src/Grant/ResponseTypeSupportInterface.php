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

use OAuth2\Endpoint\Authorization;

interface ResponseTypeSupportInterface
{
    /**
     * This function returns the supported response type.
     *
     * @return string The response type
     * @return bool   Return true if it can handle the request
     */
    public function getResponseType();

    /**
     * This is the authorization endpoint of the grant type
     * This function checks the request and returns authorize or not the client.
     *
     * @param \OAuth2\Endpoint\Authorization $authorization The authorization object
     *
     * @throws \OAuth2\Exception\BaseExceptionInterface
     *
     * @return array
     */
    public function grantAuthorization(Authorization $authorization);

    /**
     * Returns the response mode of the response type or the error returned.
     * Possible values are 'query' (in the query string) or 'fragment' (in the fragment URI).
     *
     * @return string
     */
    public function getResponseMode();
}
