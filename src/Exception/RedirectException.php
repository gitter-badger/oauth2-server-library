<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Exception;

use OAuth2\Util\Uri;

final class RedirectException extends BaseException implements RedirectExceptionInterface
{
    protected $redirect_uri;
    protected $transport_mode;

    /**
     * @param string $error             Short name of the error
     * @param string $error_description Description of the error (optional)
     * @param string $error_uri         Uri of the error (optional)
     * @param array  $data              Additional data sent to the exception (optional)
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($error, $error_description = null, $error_uri = null, array $data = [])
    {
        parent::__construct(302, $error, $error_description, $error_uri);

        if (!array_key_exists('redirect_uri', $data)) {
            throw new \InvalidArgumentException('redirect_uri_not_defined');
        }

        if (!array_key_exists('transport_mode', $data) || !in_array($data['transport_mode'], ['query', 'fragment'])) {
            throw new \InvalidArgumentException('invalid_transport_mode');
        }
        $this->transport_mode = $data['transport_mode'];

        $this->redirect_uri = $data['redirect_uri'];

        if (array_key_exists('state', $data) && null !== $data['state']) {
            $this->errorData['state'] = $data['state'];
        }
    }

    public function getResponseBody()
    {
    }

    public function getResponseHeaders()
    {
        $data = $this->errorData;
        if (array_key_exists('error_uri', $data)) {
            $data['error_uri'] = urldecode($data['error_uri']);
        }
        $params = [$this->transport_mode => $data];

        return [
            'Location' => Uri::buildURI($this->redirect_uri, $params),
        ];
    }
}
