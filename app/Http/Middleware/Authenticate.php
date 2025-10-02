<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // ✅ PERBAIKAN KRITIS: Jika request adalah API (atau mengharapkan JSON),
        // kembalikan NULL. Ini memaksa Laravel untuk mengembalikan 401 Unauthorized (JSON)
        // alih-alih me-redirect ke rute 'login' yang menghasilkan HTML.
        if ($request->expectsJson()) {
            return null;
        }

        // Jika bukan, kembali ke perilaku default (redirect ke rute 'login' web)
        return route('login');
    }
}