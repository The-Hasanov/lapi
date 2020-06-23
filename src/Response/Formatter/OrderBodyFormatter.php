<?php

namespace Lapi\Response\Formatter;

use Illuminate\Support\Collection;
use Lapi\Response\ApiResponse;

class OrderBodyFormatter implements ResponseBodyFormatter
{
    protected $defaultSort = [
        'message',
        'data',
        'debug',
    ];

    public function format(ApiResponse $apiResponse, Collection $body): Collection
    {
        $sort = array_values(app('config')->get('api.bodySort', $this->defaultSort));
        return $body->sortBy(function ($value, $key) use ($sort) {
            return (int)array_search($key, $sort, true);
        });
    }
}
