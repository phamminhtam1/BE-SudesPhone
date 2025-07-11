<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RefreshTokenExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Refresh token expiration if user is authenticated
        if ($request->user()) {
            $token = $request->user()->currentAccessToken();

            if ($token) {
                // Update expires_at to 30 minutes from now
                \Laravel\Sanctum\PersonalAccessToken::where('id', $token->id)
                    ->update(['expires_at' => now()->addMinutes(30)]);
            }
        }
        return $response;
    }
}
