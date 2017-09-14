<?php

namespace Xe\Xecd\Client\Rates\Tests\Integration;

use GuzzleHttp\HandlerStack;

class XecdRatesClientTest extends \Xe\Xecd\Client\Rates\Tests\Unit\XecdRatesClientTest
{
    /**
     * {@inheritdoc}
     */
    protected function createHandlerStack(callable $handler)
    {
        return HandlerStack::create();
    }
}
