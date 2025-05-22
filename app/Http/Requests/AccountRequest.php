<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'website' => 'required|url',
            'phone' => 'required|string|regex:/^\+380\d{9}$/',
            'account_name' => 'required|string|max:255',
            'deal_name' => 'required|string|max:255',
            'stage' => 'required|string|max:100',
        ];


    }
}
