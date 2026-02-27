<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMomentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'body' => [
                Rule::requiredIf(fn () => ! $this->hasFile('image')),
                'nullable',
                'string',
                'max:10000',
            ],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
