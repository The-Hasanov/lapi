<?php

namespace Lapi\Response\Formatter;

use Illuminate\Support\Collection;
use Lapi\Response\ApiResponse;

interface ResponseFormatter
{
    public function format(ApiResponse $apiResponse, Collection $body): void;
}