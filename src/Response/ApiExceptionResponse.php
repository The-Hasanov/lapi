<?php

namespace Lapi\Response;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Validation\ValidationException;

trait ApiExceptionResponse
{

    public function apiExceptionRender($request, \Throwable $e)
    {
        if ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        return api()
            ->mergeWithBody($this->prepareExceptionBody($e))
            ->status($e->status ?? 400)
            ->toResponse($request);
    }

    protected function prepareExceptionBody(\Throwable $e)
    {
        $debug = app('config')->get('app.debug', false);
        $exceptionBody = [
            'message'   => $e->getMessage(),
            'exception' => $debug ? get_class($e) : class_basename($e),
        ];

        if ($debug) {
            $exceptionBody += [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }

        if ($e instanceof ValidationException) {
            $exceptionBody['errors'] = $e->errors();
        }
        return $exceptionBody;
    }

}
