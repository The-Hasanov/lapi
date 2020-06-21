<?php

namespace Lapi\Response\Formatter;

use Illuminate\Support\Collection;
use Lapi\Response\ApiResponse;

class DebugFormatter implements ResponseFormatter
{
    public function __construct()
    {
        ApiResponse::macro('debug', function ($status = true) {
            $this->debug = $status;
            return $this;
        });
    }

    public function format(ApiResponse $apiResponse, Collection $body): void
    {
        $debug = $apiResponse->debug ?? false;
        if ($debug) {
            $body->put('debug', $this->getDebugInfo());
        }
    }


    public function getDebugInfo(): array
    {
        return [
            'APP_DEBUG' => true
        ];
    }
}