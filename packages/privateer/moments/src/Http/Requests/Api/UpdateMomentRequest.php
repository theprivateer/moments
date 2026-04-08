<?php

namespace Privateer\Moments\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateMomentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => [
                Rule::requiredIf(function (): bool {
                    $moment = $this->route('moment');
                    $remaining = $moment->images()->count()
                        - count($this->input('remove_images', []))
                        + count($this->input('add_images', []));
                    $bodyAfterUpdate = $this->has('body') ? $this->input('body') : $moment->body;

                    return $remaining <= 0 && empty($bodyAfterUpdate);
                }),
                'nullable',
                'string',
                'max:10000',
            ],
            'add_images' => ['nullable', 'array'],
            'add_images.*' => ['integer', 'distinct', Rule::exists('moment_images', 'id')->whereNull('moment_id')],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer', 'distinct', Rule::exists('moment_images', 'id')->where('moment_id', $this->route('moment')->id)],
            'image_order' => ['nullable', 'array'],
            'image_order.*' => ['integer'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $imageOrder = $this->input('image_order');

                if (! is_array($imageOrder)) {
                    return;
                }

                $moment = $this->route('moment');
                $removeImages = array_map('intval', $this->input('remove_images', []));
                $addImages = array_map('intval', $this->input('add_images', []));
                $keptExistingImages = $moment->images()
                    ->whereNotIn('id', $removeImages)
                    ->pluck('id')
                    ->map(fn ($id): int => (int) $id)
                    ->all();
                $expectedImageIds = array_values(array_merge($keptExistingImages, $addImages));
                sort($expectedImageIds);

                $orderedImageIds = array_map('intval', $imageOrder);
                $sortedOrderedImageIds = $orderedImageIds;
                sort($sortedOrderedImageIds);

                if ($sortedOrderedImageIds !== $expectedImageIds) {
                    $validator->errors()->add(
                        'image_order',
                        'The image order must contain each image that will remain attached exactly once.',
                    );
                }
            },
        ];
    }
}
