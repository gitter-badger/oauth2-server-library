<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Endpoint;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface TokenIntrospectionEndpointInterface
{
    /**
     * @param \OAuth2\Endpoint\TokenIntrospectionEndpointExtensionInterface $extension
     */
    public function addExtension(TokenIntrospectionEndpointExtensionInterface $extension);

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     */
    public function introspect(ServerRequestInterface $request, ResponseInterface &$response);
}
