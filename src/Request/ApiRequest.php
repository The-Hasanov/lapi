<?php

namespace Lapi\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @method rulesIndex()
 * @method rulesStore()
 * @method rulesShow()
 * @method rulesUpdate()
 * @method rulesDestroy()
 */
class ApiRequest extends FormRequest
{

    public function rules()
    {
        $action = $this->route() ? $this->route()->getActionMethod() : '';

        return method_exists($this, $methodName = 'rules' . Str::studly($action))
            ? $this->{$methodName}()
            : [];
    }

    public function ruleInputs()
    {
        return Arr::only($this->all(), $this->normilizeRuleNames(array_keys($this->rules())));
    }

    private function normilizeRuleNames($names)
    {
        return array_map(function ($name) {
            return current(explode('.', $name));
        }, $names);
    }

    public function page()
    {
        return (int)$this->get('page', 1);
    }

    public function limit()
    {
        if ($limit = $this->get('limit')) {
            return $limit > ($maximumLimit = $this->maximumPaginationLimit())
                ? $maximumLimit
                : $limit;
        }
        return $this->defaultPaginationLimit();
    }

    protected function defaultPaginationLimit(): int
    {
        return (int)app('config')->get('api.pagination.default_limit', 15);
    }

    protected function maximumPaginationLimit(): int
    {
        return (int)app('config')->get('api.pagination.max_limit', 100);
    }

    public function parameter($parameter = null)
    {
        if ($parameter !== null) {
            return $this->route()->parameter($parameter);
        }
        return defined(static::class . '::PARAMETER')
            ? $this->route()->parameter(static::PARAMETER)
            : null;
    }

}
