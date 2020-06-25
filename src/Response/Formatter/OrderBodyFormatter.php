<?php

namespace Lapi\Response\Formatter;

use Illuminate\Support\Collection;
use Lapi\Response\ApiResponse;

class OrderBodyFormatter implements ResponseBodyFormatter
{
    public const DEFAULT_ORDER = [
        'message',
        'data',
        'total',
        'debug',
    ];

    public function format(ApiResponse $apiResponse, Collection $body): Collection
    {
        $sort = array_values(app('config')->get('api.order_body', self::DEFAULT_ORDER));
        return $body->sortBy(function ($value, $key) use ($sort) {
            return (int)array_search($key, $sort, true);
        });
    }
}
