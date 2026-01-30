<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'コメント本文を入力してください。',
            'body.max'      => 'コメントは255文字以内で入力してください。',
        ];
    }

    protected function prepareForValidation(): void
    {
        $body = (string) $this->input('body');

        // 半角/全角スペースをトリム
        $body = preg_replace('/\A[\s　]+|[\s　]+\z/u', '', $body);

        $this->merge([
            'body' => $body,
        ]);
    }
}