<?php

namespace App\Http\Middleware;

use App\Helpers\JWTAuth;
use Closure;
use Illuminate\Http\Request;

class ApiAuthMiddleware
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
        $token = $request->header('Authorization');
        $jwt = new JWTAuth;
        $check_token = $jwt->check_token($token);

        if($check_token){
            return $next($request);
        }else{
            $data = array(
                'status' => 'Error',
                'code' => 401,
                'message' => 'El usuario no esta identificado correctamente'
            );

            return response()->json($data, $data['code']);
        }
    }
}
