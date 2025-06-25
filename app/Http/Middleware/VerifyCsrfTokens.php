<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfTokens as Middleware;

class VerifyCsrfTokens extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/payment/callback',
        '/tripay-callback',
        // Tambah route lain jika perlu
    ];
}
