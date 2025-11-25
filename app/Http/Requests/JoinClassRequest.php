<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // চেক করুন, ব্যবহারকারী এই রিকোয়েস্ট করতে অনুমতি পায় কি না
        return true; // যদি অনুমতি দেন
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // কোডটি স্ট্রিং এবং 6 অক্ষরের মধ্যে হতে হবে
            'code' => 'required|string|size:9|exists:classes,code', // ক্লাস কোড যাচাই
        ];
    }
}