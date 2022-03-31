<?php
/**
* 跨域请求处理
*/
namespace App\Http\Middleware;
use Closure;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('OPTIONS')) {
            $response = response(null, 204);
        } else {
            $response = $next($request);
        }

        return $this->addCorsHeaders($request, $response);
    }

    /**
     * Add CORS headers.
     */
    private function addCorsHeaders($request, $response)
    {
        foreach ([
            'Access-Control-Allow-Origin'=>'*',
            'Access-Control-Max-Age'=>86400,
            'Access-Control-Allow-Headers'=>$request->header('access-control-request-headers'),
            'Access-Control-Allow-Methods'=>'GET, HEAD, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Credentials'=>'true',
            'Cache-Control'=>'no-store'
        ] as $header => $value) {
            $response->header($header, $value);
        }

        return $response;
    }
}