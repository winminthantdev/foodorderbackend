<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $user = $request->user('admin-api');

        if( !$user  ) {
            return response()->json(["message"=> "Access Denied. Admins only."], 403);
        }
        return $response;

    }
}   
