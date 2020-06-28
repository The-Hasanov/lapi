<?php

namespace Lapi\Response;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Lapi\Response\Formatter\ResponseBodyFormatter;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse implements Responsable, Jsonable, Arrayable
{
    use Macroable;
    /**
     * @var ResponseBodyFormatter[]
     */
    protected static $formatters = [];

    protected $body;

    protected $status = 200;

    protected $response;

    protected $dataResource;

    public function __construct($body = null)
    {
        $this->body = new Collection($body ?? []);
    }

    public static function addFormatter(ResponseBodyFormatter $formatter): void
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

    public function setData($data): self
    {
        $this->body->put('data', collect($this->dataResource
            ? $this->applyDataResource($data)
            : $data
        ));
        return $this;
    }

    public function setDataWhen($condition, $data): self
    {
        if ($condition) {
            $this->setData($data);
        }
        return $this;
    }

    public function setPaginatorData(AbstractPaginator $paginator): self
    {
        $this->body->put('data', $this->applyDataResourceToCollection($paginator->getCollection()));

        if (method_exists($paginator, 'total')) {
            $this->body->put('total', $paginator->total());
        }
        return $this;
    }

    public function mergeWithData($data): self
    {
        $data = $data instanceof AbstractPaginator
            ? $this->applyDataResourceToCollection($data->getCollection())
            : collect($this->applyDataResource($data));

        $dataCollection = $this->body->has('data')
            ? $this->body->get('data')
            : ($this->body['body'] = collect());

        foreach ($data as $index => $value) {
            $dataCollection->put(is_string($index) ? $index : null, $value);
        }
        return $this;
    }

    public function mergeWithDataWhen($condition, $data): self
    {
        if ($condition) {
            $this->mergeWithData($data);
        }
        return $this;
    }

    public function getData(): Collection
    {
        return collect($this->body->get('data', []));
    }

    public function mergeWithBody($data): self
    {
        foreach (collect($data) as $index => $value) {
            $this->body->put(is_string($index) ? $index : null, $value);
        }
        return $this;
    }

    public function mergeWithBodyWhen($condition, $data): self
    {
        if ($condition) {
            $this->mergeWithBody($data);
        }
        return $this;
    }

    public function getBody(): Collection
    {
        return collect($this->body);
    }

    public function setBody($body): self
    {
        $this->body = collect($body);
        return $this;
    }

    public function bindDataResource($resource)
    {
        $this->dataResource = $resource instanceof JsonResource
            ? $resource
            : app()->makeWith($resource, ['resource' => null]);
        return $this;
    }

    protected function applyDataResource($data)
    {
        if ($this->dataResource !== null) {
            $this->dataResource->resource = $data;
            $data = $this->dataResource->resolve();
            $this->dataResource->resource = null;
        }
        return $data;
    }

    protected function applyDataResourceToCollection(Collection $collection): Collection
    {
        return $this->dataResource === null
            ? $collection
            : $collection->map(function ($data) {
                return $this->applyDataResource($data);
            });
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     */
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
        $body = $this->body;
        foreach (static::$formatters as $formatter) {
            $body = $formatter->format($this, $body);
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
