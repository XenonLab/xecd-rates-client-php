<?php

namespace Xe\Xecd\Client\Rates;

use Psr\Http\Message\RequestInterface;

final class Middleware
{
    /**
     * Middleware that modifies the request to the API.
     *
     * @return callable Returns a function that accepts the next handler
     */
    public static function mapRequest()
    {
        return \GuzzleHttp\Middleware::mapRequest(function (RequestInterface $request) {
            // Add api version into url.
            $request = $request->withUri($request->getUri()->withPath("v1{$request->getUri()->getPath()}"));

            // Add format into url.
            $request = $request->withUri($request->getUri()->withPath("{$request->getUri()->getPath()}.json"));

            return $request;
        });
    }
}
