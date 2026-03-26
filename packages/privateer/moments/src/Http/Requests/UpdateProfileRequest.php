<?php

namespace Privateer\Moments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Privateer\Moments\Support\Moments as MomentsSupport;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(MomentsSupport::userTable())->ignore($this->user()->id),
            ],
        ];
    }
}
