<?php

namespace Lapi\Response;

abstract class ApiHttpException extends \Exception
{
    abstract public function getStatusCode();

    abstract public function getErrorMessage();

    abstract public function shouldReport();

    public function addition(): array
    {
        return [];
    }
}
