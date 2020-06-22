<?php

namespace Lapi\Response;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Validation\ValidationException;

trait ApiExceptionResponse
{

    public function render($request, \Exception $e)
    {
        if ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        return api()
            ->mergeWithBody($this->prepareExceptionBody($e))
            ->status($e->status ?? 400)
            ->toResponse($request);
    }


    protected function prepareExceptionBody(\Exception $e)
    {
        $exceptionBody = [
            'message'   => $e->getMessage(),
            'exception' => class_basename(self::class)
        ];

        if ($e instanceof ValidationException) {
            $exceptionBody['errors'] = $e->errors();
        }
        return $exceptionBody;
    }

}