<?php

use Illuminate\Pagination\AbstractPaginator;
use Lapi\Response\ApiResponse;

if (!function_exists('api')) {
    function api($data = null, $dataResource = null)
    {
        $apiResponse = new ApiResponse();
        if ($dataResource !== null) {
            $apiResponse->bindDataResource($dataResource);
        }

        if ($data instanceof AbstractPaginator) {
            $apiResponse->setPaginatorData($data);
        } else {
            $apiResponse->setData($data);
        }

        return $apiResponse;
    }
}
