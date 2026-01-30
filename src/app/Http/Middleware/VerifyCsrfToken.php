<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * Stripe の Webhook は外部サービスからの POST のため
     * CSRF トークンを送らない → 必ず除外する
     *
     * @var array<int, string>
     */
    protected $except = [
        'stripe/webhook',
    ];
}