<?php

namespace App\Http\Requests\Api;

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
                    $moment = $this->route('moment');
                    $remaining = $moment->images()->count()
                        - count($this->input('remove_images', []))
                        + count($this->input('add_images', []));
                    $bodyAfterUpdate = $this->has('body') ? $this->input('body') : $moment->body;

                    return $remaining <= 0 && empty($bodyAfterUpdate);
                }),
                'nullable', 'string', 'max:10000',
            ],
            'add_images' => ['nullable', 'array'],
            'add_images.*' => ['integer', Rule::exists('moment_images', 'id')->whereNull('moment_id')],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer', Rule::exists('moment_images', 'id')->where('moment_id', $this->route('moment')->id)],
        ];
    }
}
