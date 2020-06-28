<?php

namespace Lapi\Response;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Validation\ValidationException;

trait ApiExceptionResponse
{

    public function apiExceptionRender($request, \Throwable $exception)
    {
        if ($exception instanceof Responsable) {
            return $exception->toResponse($request);
        }

        return api()
            ->mergeWithBody($this->prepareExceptionBody($exception))
            ->status($this->getExceiptionHttpStatusCode($exception))
            ->toResponse($request)
            ->withHeaders($this->getExceptionHttpHeaders($exception));
    }

    protected function prepareExceptionBody(\Throwable $exception)
    {
        $debug = app('config')->get('app.debug', false);
        $exceptionBody = [
            'message'   => $exception->getMessage(),
            'exception' => $debug ? get_class($exception) : class_basename($exception),
        ];

        if ($debug) {
            $exceptionBody += [
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];
        }

        if ($exception instanceof ValidationException) {
            $exceptionBody['errors'] = $exception->errors();
        }
        return $exceptionBody;
    }

    private function getExceiptionHttpStatusCode($exception)
    {
        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }
        return 400;
    }

    private function getExceptionHttpHeaders($exception)
    {
        if (method_exists($exception, 'getHeaders')) {
            return $exception->getHeaders();
        }
        return [];
    }

}
