<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMomentRequest extends FormRequest
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
                Rule::requiredIf(function () {
                    $hasNewImages = $this->hasFile('images');
                    $moment = $this->route('moment');
                    $remaining = $moment->images()->count() - count($this->input('remove_images', []));

                    return ! $hasNewImages && $remaining <= 0;
                }),
                'nullable',
                'string',
                'max:10000',
            ],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:2048'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer', 'exists:moment_images,id'],
        ];
    }
}
