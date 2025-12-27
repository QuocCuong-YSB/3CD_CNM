<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class CheckTokenExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $token = $user->currentAccessToken();

        if ($token->expires_at && Carbon::parse($token->expires_at)->lt(now())) {
            $token->delete();
            return response()->json(['message' => 'Token expired'], 401);
        }

        return $next($request);
    }
}
