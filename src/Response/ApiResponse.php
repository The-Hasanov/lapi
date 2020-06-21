<?php

namespace Lapi\Response;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Validation\ValidationException;
use Lapi\Response\Formatter\ResponseFormatter;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse implements Responsable, Jsonable, Arrayable
{
    use Macroable;
    /**
     * @var ResponseFormatter[]
     */
    protected static $formatters = [];

    protected $body;

    protected $status = 200;

    protected $response;

    public function __construct()
    {
        $this->body = new Collection();
    }

    public static function addFormatter(ResponseFormatter $formatter)
    {
        static::$formatters[] = $formatter;
    }

    /**
     * Http status Code
     */
    public function status(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function success($message = null): self
    {
        $this->body->put('success', true);
        if ($message !== null) {
            $this->message($message);
        }
        return $this;
    }

    public function fail($message = null): self
    {
        $this->body->put('success', false);
        if ($message !== null) {
            $this->message($message);
        }
        return $this;
    }

    public function message($message): self
    {
        $this->body->put('message', $message);
        return $this;
    }

    public function mergeWithBody($data): self
    {
        foreach ($data as $index => $value) {
            $this->body->put(is_string($index) ? $index : null, $value);
        }
        return $this;
    }

    public function getBody(): Collection
    {
        return $this->body;
    }

    public function setBody(Collection $collection): self
    {
        $this->body = $collection;
        return $this;
    }

    public function toResponse($request): Response
    {
        return ($this->buildResponse())
            ->setStatusCode($this->status)
            ->setData($this);
    }

    public function withResponse(JsonResponse $response): self
    {
        $this->response = $response;
        return $this;
    }

    private function buildResponse(): JsonResponse
    {
        return $this->response ?? new JsonResponse();
    }

    public function toJson($options = 0): string
    {
        return $this->prepareBody()
            ->toJson($options | app('config')->get('api.json_options', 0));
    }

    protected function prepareBody(): Collection
    {
        $body = $this->body->collect();
        foreach (static::$formatters as $formatter) {
            $formatter->format($this, $body);
        }
        return $body;
    }

    public function toArray(): array
    {
        return $this->prepareBody()->toArray();
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}