<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, string> */
    public function rules(): array
    {
        return [
            'body' => 'required|string|max:10000',
            'image' => 'nullable|image|max:2048',
            'remove_image' => 'nullable|boolean',
        ];
    }
}
