<?php

use Lapi\Response\ApiResponse;

if (!function_exists('api')) {
    function api($data = null)
    {
        return $data
            ? (new ApiResponse())->setData($data)
            : new ApiResponse();
    }
}
