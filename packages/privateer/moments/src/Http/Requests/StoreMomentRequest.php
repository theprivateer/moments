<?php

namespace Privateer\Moments\Http\Requests;

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
                Rule::requiredIf(fn () => ! $this->hasFile('images')),
                'nullable',
                'string',
                'max:10000',
            ],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:'.config('moments.image_max_size')],
        ];
    }
}
