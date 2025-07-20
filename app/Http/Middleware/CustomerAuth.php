<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;

class CustomerAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if customer is authenticated via Sanctum
        if (!$request->user('customer')) {
            return response()->json(['error' => 'Unauthorized customer access'], 402);
        }

        // Check if token exists
        $token = $request->user('customer')->currentAccessToken();
        if (!$token) {
            return response()->json(['error' => 'Invalid customer token'], 402);
        }

        return $next($request);
    }
}
