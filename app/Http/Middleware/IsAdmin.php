<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            abort(401);
        }

        if (!auth()->user()->admin) {
            return response()->json([
                'message' => 'Sorry you do not have access to perform that function',
            ], 401);
        }

        return $next($request);
    }
}
