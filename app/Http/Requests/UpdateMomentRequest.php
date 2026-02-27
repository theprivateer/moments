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
                    $hasNewImage = $this->hasFile('image');
                    $moment = $this->route('moment');
                    $hasExistingImage = $moment->image_path !== null;
                    $removingImage = (bool) $this->input('remove_image', false);

                    return ! $hasNewImage && (! $hasExistingImage || $removingImage);
                }),
                'nullable',
                'string',
                'max:10000',
            ],
            'image' => ['nullable', 'image', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }
}
