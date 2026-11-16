<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * タスクの入力チェック（store と update で共通）
 */
class TaskRequest extends FormRequest
{
    /**
     * 本人チェックは TaskPolicy（コントローラーの Gate::authorize）で行うので、ここでは true
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => 'required|max:100',
            'description' => 'nullable|max:1000',
            'due_date'    => 'nullable|date',
        ];
    }
}
