<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:100',
            'semester' => 'required|string|max:50',
            'year' => 'required|integer',
            'max_students' => 'required|integer|min:1',
        ];
    }
}