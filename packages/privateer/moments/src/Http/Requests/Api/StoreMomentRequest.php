<?php

namespace Privateer\Moments\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMomentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
                'distinct',
                Rule::exists('moment_images', 'id')->whereNull('moment_id'),
            ],
            'created_at' => ['nullable', 'integer'],
        ];
    }
}
