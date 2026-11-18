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
            'image'    => 'nullable|image|max:5120', // 画像は任意。jpg/png/gif/webp など・5MB まで
        ];
    }
}
