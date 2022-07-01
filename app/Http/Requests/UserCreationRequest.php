<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserCreationRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'firstName' => 'required',
            'empId' => 'required',
            'role' => 'required',
            'lastName' => 'required',
            'department' => 'required',
            'username' => 'required',
            'jobTitle' => 'required',
            'email' => 'required',
            'twoFactor' => 'required',
        ];
    }
}
