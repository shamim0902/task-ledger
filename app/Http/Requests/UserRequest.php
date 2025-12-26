<?php

namespace TaskLedger\App\Http\Requests;

use TaskLedger\Framework\Validator\Rule;
use TaskLedger\Framework\Foundation\RequestGuard;

class UserRequest extends RequestGuard
{
    /**
     * Register your custom rules
     */
    public function __construct()
    {
        // Rule::add(CustomRule::class);
    }

    /**
     * Authorize the request
     * 
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return Array
     */
    public function rules()
    {
        return [];
    }

    /**
     * @return Array
     */
    public function messages()
    {
        return [];
    }

    /**
     * @return Array
     */
    public function beforeValidation()
    {
        $data = $this->all();
        
        // Modify the $data

        return $data;
    }

    /**
     * @return Array
     */
    public function afterValidation($validator)
    {
        $data = $this->all();
        
        // Modify the $data

        return $data;
    }
}
