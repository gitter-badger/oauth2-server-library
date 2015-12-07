<?php

namespace OAuth2\Endpoint;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface TokenEndpointInterface
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request  The request
     * @param \Psr\Http\Message\ResponseInterface      $response The response
     *
     * @throws \OAuth2\Exception\BaseExceptionInterface If an error occurred
     */
    public function getAccessToken(ServerRequestInterface $request, ResponseInterface &$response);
}