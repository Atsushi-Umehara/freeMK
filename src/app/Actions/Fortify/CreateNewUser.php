<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        Validator::make(
            $input,
            [
                // ✅ 基本設計書：ユーザー名「入力必須、20文字以内」
                'name' => ['required', 'string', 'max:20'],

                // ✅ メール：必須 / 形式 / 一意 / 最大255
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email'),
                ],

                // ✅ パスワード：必須 / 8文字以上 / 確認一致
                'password' => ['required', 'string', Password::min(8), 'confirmed'],
            ],
            [
                // ✅ ユーザー名
                'name.required' => 'お名前を入力してください',
                'name.string'   => 'お名前は文字で入力してください',
                'name.max'      => 'お名前は20文字以内で入力してください',

                // ✅ メール
                'email.required' => 'メールアドレスを入力してください',
                'email.email'    => 'メールアドレスはメール形式で入力してください',
                'email.max'      => 'メールアドレスは255文字以内で入力してください',
                'email.unique'   => 'このメールアドレスは既に使用されています',

                // ✅ パスワード
                'password.required'  => 'パスワードを入力してください',
                'password.min'       => 'パスワードは8文字以上で入力してください',
                'password.confirmed' => 'パスワードと一致しません',
            ]
        )->validate();

        return User::create([
            'name'     => (string) $input['name'],
            'email'    => (string) $input['email'],
            'password' => Hash::make((string) $input['password']),
        ]);
    }
}