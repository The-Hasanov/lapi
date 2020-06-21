<?php

use Lapi\Response\ApiResponse;

if (!function_exists('api')) {
    function api()
    {
        return new ApiResponse();
    }
}