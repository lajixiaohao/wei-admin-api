<?php
/**
* 验证token
*/
namespace App\Http\Middleware;

use Closure;

class TokenMiddleware
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
        $token = $request->header('token', '');
        if (! $token) {
            return response()->json(['code'=>1, 'msg'=>'Token Missing']);
        }

        try {
            $token = openssl_decrypt($token, 'AES-256-ECB', env('TOKEN'));
            if ($token === false) {
                return response()->json(['code'=>2001, 'msg'=>'Token Involid']);
            }

            $token = json_decode($token, true);

            // 验证是否过期
            if ($token['expire'] < time()) {
                return response()->json(['code'=>2001, 'msg'=>'Token Involid']);
            }

            foreach ($token['data'] as $k => $v) {
                $request->$k = $v;
            }
        } catch (\Exception $e) {
            return response()->json(['code'=>2001, 'msg'=>$e->getMessage()]);
        }

        return $next($request);
    }
}