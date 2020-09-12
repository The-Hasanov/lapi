<?php

namespace Lapi\Response;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

trait ApiExceptionResponse
{

    public function apiExceptionReport(\Throwable $exception)
    {
        if ($exception instanceof ApiHttpException && !$exception->shouldReport()) {
            return;
        }
        return parent::report($exception);
    }

    public function apiExceptionRender($request, \Throwable $exception)
    {
        if ($exception instanceof Responsable) {
            return $exception->toResponse($request);
        }

        return api()
            ->mergeWithBody($this->prepareExceptionBody($exception))
            ->status($this->getExceptionHttpStatusCode($exception))
            ->when($exception instanceof ApiHttpException, function (ApiResponse $apiResponse) use ($exception) {
                return $apiResponse->mergeWithBody($exception->addition());
            })
            ->toResponse($request)
            ->withHeaders($this->getExceptionHttpHeaders($exception));
    }

    protected function prepareExceptionBody(\Throwable $exception)
    {
        $debug = app('config')->get('app.debug', false);
        $exceptionBody = [
            'message'   => $this->getExceptionMessage($exception),
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

    private function getExceptionMessage($exception)
    {
        if ($exception instanceof ApiHttpException) {
            return $exception->getErrorMessage();
        }
        if ($message = $exception->getMessage()) {
            return $message;
        }
        if ($httpCode = $this->getExceptionHttpStatusCode($exception)) {
            return array_key_exists($httpCode, Response::$statusTexts)
                ? Response::$statusTexts[$httpCode]
                : Response::$statusTexts[500];
        }
    }

    private function getExceptionHttpStatusCode($exception)
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
