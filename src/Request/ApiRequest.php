<?php

namespace Lapi\Request;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * @method rulesIndex()
 * @method rulesCreate()
 * @method rulesStore()
 * @method rulesShow()
 * @method rulesEdit()
 * @method rulesUpdate()
 * @method rulesDelete()
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

}