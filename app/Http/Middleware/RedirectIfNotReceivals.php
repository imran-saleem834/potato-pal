<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotReceivals
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && in_array('admin', Auth::user()->role, true)) {
            return $next($request);
        }
        if (Auth::check() && in_array('receivals', Auth::user()->role, true)) {
            return $next($request);
        }

        return redirect(RouteServiceProvider::HOME);
    }
}
