<?php

namespace OAuth2\Endpoint;

use OAuth2\Util\Uri;
use Psr\Http\Message\ResponseInterface;

final class QueryResponseMode implements ResponseModeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'query';
    }

    /**
     * {@inheritdoc}
     */
    public function prepareResponse($redirect_uri, array $data, ResponseInterface &$response)
    {
        $params = empty($data) ? [] : [$this->getName() => $data];

        $response = $response->withStatus(302)
            ->withHeader('Location', Uri::buildUri($redirect_uri, $params));
    }
}