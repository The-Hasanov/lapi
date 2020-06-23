<?php

namespace Lapi\Response\Formatter;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Lapi\Response\ApiResponse;

class DebugFormatter implements ResponseBodyFormatter
{
    public function __construct()
    {
        ApiResponse::macro('debug', function ($status = true) {
            $this->debug = $status;
            return $this;
        });
    }

    public function format(ApiResponse $apiResponse, Collection $body): Collection
    {
        $debug = $apiResponse->debug ?? app('config')->get('app.debug', false);
        if ($debug) {
            $body->put('debug', $this->getDebugInfo());
        }
        return $body;
    }

    public function getDebugInfo(): array
    {
        $debugConfig = app('config')->get('api.debug', []);
        return collect($debugConfig)
            ->filter()
            ->map(function ($status, $index) {
                if (method_exists($this, $methodName = 'info' . Str::studly($index))) {
                    return $this->{$methodName}();
                }
                return [];
            })
            ->toArray();
    }

    private function infoRequest()
    {
        if (!($request = app('request'))) {
            return [];
        }

        return [
            'uri'    => '[' . $request->method() . '] ' . $request->getRequestUri(),
            'params' => $request->query(),
            'post'   => $request->isMethod('POST') || $request->isMethod('PUT')
                ? $request->post()
                : null
        ];
    }

    private function infoRoute()
    {
        if (!($request = app('request'))) {
            return [];
        }
        return $request->route()
            ? $request->route()->action
            : [];
    }

    private function infoDuration()
    {
        if (!($request = app('request'))) {
            return null;
        }
        return round((microtime(true) - $request->server('REQUEST_TIME_FLOAT')) * 1000);
    }

    private function infoQuery()
    {
        if (!($db = app('db'))) {
            return null;
        }
        return $db->getQueryLog();
    }
}
