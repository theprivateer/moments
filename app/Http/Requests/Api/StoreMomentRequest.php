<?php

namespace App\Http\Requests\Api;

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
                Rule::requiredIf(fn () => empty($this->input('images'))),
                'nullable',
                'string',
                'max:10000',
            ],
            'images' => ['nullable', 'array'],
            'images.*' => [
                'integer',
                Rule::exists('moment_images', 'id')->whereNull('moment_id'),
            ],
            'created_at' => ['nullable', 'integer'],
        ];
    }
}
