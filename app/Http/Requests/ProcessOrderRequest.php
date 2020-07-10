<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ProcessOrderRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "first_name" => "required|string",
            "last_name" => "required|string",
            "address1" => "required|string",
            "address2" => "string",
            "city" => "required|string",
            "country" => "required|string",
            "postcode" => "required|string",
            "state" => "required|string"
        ];
    }
}
