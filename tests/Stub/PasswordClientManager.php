<?php

namespace OAuth2\Test\Stub;

use OAuth2\Client\PasswordClientInterface;
use OAuth2\Client\PasswordClientManager as Base;

class PasswordClientManager extends Base
{
    private $clients = [];

    public function __construct()
    {
        $bar = new PasswordClient();
        $bar->setPublicId('bar')
            ->setSecret('secret')
            ->setAllowedGrantTypes(['client_credentials', 'password', 'token', 'refresh_token', 'code', 'authorization_code'])
            ->setRedirectUris(['http://example.com/test?good=false']);

        $baz = new PasswordClient();
        $baz->setPublicId('baz')
            ->setSecret('secret')
            ->setAllowedGrantTypes(['authorization_code'])
            ->setRedirectUris([]);
        $this->clients['bar'] = $bar;
        $this->clients['baz'] = $baz;
    }

    /**
     * {@inheritdoc}
     */
    public function getClient($client_id)
    {
        return isset($this->clients[$client_id]) ? $this->clients[$client_id] : null;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkClientCredentials(PasswordClientInterface $client, $secret)
    {
        if (!$client instanceof PasswordClient) {
            return false;
        }

        return $client->getSecret() === $secret;
    }
}
