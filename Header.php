<?php

namespace Xe\Xecd\Client\Rates;

final class Header
{
    /**
     * The maximum number of requests that you are permitted to make per usage restriction window.
     */
    const X_RATELIMIT_LIMIT = 'X-RateLimit-Limit';

    /**
     * The number of requests remaining in the current usage restriction window.
     */
    const X_RATELIMIT_REMAINING = 'X-RateLimit-Remaining';

    /**
     * The time at which the current usage restriction window resets as a Unix timestamp.
     */
    const X_RATELIMIT_RESET = 'X-RateLimit-Reset';
}
