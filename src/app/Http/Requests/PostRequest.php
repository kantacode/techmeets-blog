<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * 投稿の入力チェック（store と update で共通）
 */
class PostRequest extends FormRequest
{
    /**
     * 本人チェックは PostPolicy（コントローラーの Gate::authorize）で行うので、ここでは true
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
            'title'    => 'required|max:200',
            'content'  => 'required',
            'category' => 'required|max:50',
        ];
    }
}
