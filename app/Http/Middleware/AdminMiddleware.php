<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AdminMiddleware
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
        try{
            $token = JWTAuth::parseToken();
            $userType = $token->getClaim('user_type');
            if($userType !== 'admin'){
                return response()->json([
                    'status' => false,
                    'message' => 'Mohon maaf anda bukanlah seorang admin!'
                ]);
            }
        }catch(Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Forbidden'
            ],422);
        }

        return $next($request);

    }
}
