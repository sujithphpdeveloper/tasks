<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'min:5', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in(TaskStatus::values())],
            'priority' => ['sometimes', 'required', 'string', Rule::in(TaskPriority::values())],
            'assigned_to' => ['sometimes', 'nullable', 'integer', 'exists:users,id'], //Ensuring the assigned user exists
            'due_date' => ['sometimes', 'nullable', 'date'],
            'tags' => ['sometimes', 'nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'metadata' => ['sometimes', 'nullable', 'array'],
            'version'     => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'version.required' => 'The Version number is required for updates',
            'version.integer'  => 'The Version must be a valid integer',
        ];
    }
}
