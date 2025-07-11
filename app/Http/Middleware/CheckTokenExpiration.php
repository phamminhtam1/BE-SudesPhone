<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $token = $request->user()->currentAccessToken();

            // Check if token has expired
            if ($token && $token->expires_at && $token->expires_at->isPast()) {
                PersonalAccessToken::where('id', $token->id)->delete();
                return response()->json(['error' => 'Token has expired'], 401);
            }
        }

        return $next($request);
    }
}
