<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user('user-api');

        if( $user && $user instanceof \App\Models\User ){
            return $next($request);
        }

        return response()->json(["message"=> "Access Denied. Auth User only."], 403);
    }
}

return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
