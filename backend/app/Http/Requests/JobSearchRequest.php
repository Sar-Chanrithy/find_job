<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobSearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:80'],
            'location' => ['nullable', 'string', 'max:80'],
            'type' => ['nullable', Rule::in(['Full time', 'Part time', 'Internship'])],
            'remote' => ['sometimes', 'boolean'],
            'sort' => ['nullable', Rule::in(['featured', 'newest'])],
            'page' => ['sometimes', 'integer', 'min:1', 'max:10000'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'ids' => ['sometimes', 'array', 'max:100'],
            'ids.*' => ['integer', 'min:1', 'max:2147483647', 'distinct'],
        ];
    }
}
