<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->student, 403);

        return $next($request);
    }
}
