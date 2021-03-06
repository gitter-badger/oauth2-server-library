<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2015 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace OAuth2\Util;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

final class DigestData
{
    /**
     * @var array
     */
    private $elements = [];

    /**
     * @var string
     */
    private $header;

    /**
     * @var int
     */
    private $nonceExpiryTime;

    /**
     * @param string $header
     */
    public function __construct($header)
    {
        $this->header = $header;
        preg_match_all('/(\w+)=("((?:[^"\\\\]|\\\\.)+)"|([^\s,$]+))/', $header, $matches, PREG_SET_ORDER);
        if (!is_array($matches)) {
            throw new \InvalidArgumentException('Unable to parse header');
        }
        foreach ($matches as $match) {
            if (isset($match[1]) && isset($match[3])) {
                $this->elements[$match[1]] = isset($match[4]) ? $match[4] : $match[3];
            }
        }
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->elements['response'];
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return strtr($this->elements['username'], ['\\"' => '"', '\\\\' => '\\']);
    }

    /**
     * @param string $entryPointKey
     * @param string $expectedRealm
     *
     * @throws \InvalidArgumentException
     */
    public function validateAndDecode($entryPointKey, $expectedRealm)
    {
        $this->checkElements();
        $this->checkQualityOfProtection();
        $this->checkRealm($expectedRealm);
        $this->checkOpaque($expectedRealm, $entryPointKey);
        $this->checkNonce($entryPointKey);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function checkElements()
    {
        if ($keys = array_diff(['username', 'realm', 'nonce', 'uri', 'response', 'opaque'], array_keys($this->elements))) {
            throw new \InvalidArgumentException(sprintf('Missing mandatory digest value(s): %s.', implode(', ', $keys)));
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function checkQualityOfProtection()
    {
        if (in_array($this->elements['qop'], ['auth', 'auth-int'])) {
            if (!isset($this->elements['nc']) || !isset($this->elements['cnonce'])) {
                throw new \InvalidArgumentException(sprintf('Missing mandatory digest value "nc" or "cnonce".'));
            }
        }
    }

    /**
     * @param string $expectedRealm
     *
     * @throws \InvalidArgumentException
     */
    public function checkRealm($expectedRealm)
    {
        if ($expectedRealm !== $this->elements['realm']) {
            throw new \InvalidArgumentException(sprintf('Response realm name "%s" does not match system realm name of "%s".', $this->elements['realm'], $expectedRealm));
        }
    }

    /**
     * @param string $expectedRealm
     * @param string $entryPointKey
     *
     * @throws \InvalidArgumentException
     */
    public function checkOpaque($expectedRealm, $entryPointKey)
    {
        $opaque = base64_encode(hash_hmac('sha512', $this->elements['nonce'].$expectedRealm, $entryPointKey, true));

        if ($opaque !== $this->elements['opaque']) {
            throw new \InvalidArgumentException('Invalid "opaque" value.');
        }
    }

    /**
     * @param string $entryPointKey
     *
     * @throws \InvalidArgumentException
     */
    public function checkNonce($entryPointKey)
    {
        if (false === $nonceAsPlainText = base64_decode($this->elements['nonce'])) {
            throw new \InvalidArgumentException(sprintf('Nonce is not encoded in Base64; received nonce "%s".', $this->elements['nonce']));
        }
        $nonceTokens = explode(':', $nonceAsPlainText);
        if (2 !== count($nonceTokens)) {
            throw new \InvalidArgumentException(sprintf('Nonce should have yielded two tokens but was "%s".', $nonceAsPlainText));
        }
        $this->nonceExpiryTime = $nonceTokens[0];
        if (hash_hmac('sha512', $this->nonceExpiryTime.$entryPointKey, $entryPointKey) !== $nonceTokens[1]) {
            throw new \InvalidArgumentException(sprintf('Nonce token compromised "%s".', $nonceAsPlainText));
        }
    }

    /**
     * @param string      $password
     * @param string      $httpMethod
     * @param string|null $algorithm
     * @param string      $content_hash
     *
     * @return string
     */
    public function calculateServerDigestUsingPassword($password, $httpMethod, $algorithm = 'MD5', $content_hash = '')
    {
        $a1Md5 = md5($this->elements['username'].':'.$this->elements['realm'].':'.$password);

        return $this->calculateServerDigestUsingA1MD5($a1Md5, $httpMethod, $algorithm, $content_hash);
    }

    /**
     * @param string      $a1Md5
     * @param string      $httpMethod
     * @param string|null $algorithm
     * @param string      $content_hash
     *
     * @return string
     */
    public function calculateServerDigestUsingA1MD5($a1Md5, $httpMethod, $algorithm = 'MD5', $content_hash = '')
    {
        if ('MD5-sess' === $algorithm) {
            $a1Md5 = md5($a1Md5.':'.$this->elements['nonce'].':'.$this->elements['cnonce']);
        }
        $a2 = strtoupper($httpMethod).':'.$this->elements['uri'];
        $digest = $a1Md5.':'.$this->elements['nonce'];
        if (isset($this->elements['qop'])) {
            if ('auth' === $this->elements['qop']) {
                $digest .= ':'.$this->elements['nc'].':'.$this->elements['cnonce'].':'.$this->elements['qop'];
            } elseif ('auth-int' === $this->elements['qop']) {
                $digest .= ':'.$this->elements['nc'].':'.$this->elements['cnonce'].':'.$this->elements['qop'];
                $a2 .= ':'.$content_hash;
            } else {
                throw new \InvalidArgumentException('This method does not support a qop: "%s".', $this->elements['qop']);
            }
        }
        $a2Md5 = md5($a2);
        $digest .= ':'.$a2Md5;

        return md5($digest);
    }

    /**
     * @return bool
     */
    public function isNonceExpired()
    {
        return $this->nonceExpiryTime < microtime(true);
    }
}
