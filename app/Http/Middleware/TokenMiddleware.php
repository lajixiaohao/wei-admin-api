<?php
/**
* token验证
* 验证不通过，将返回HTTP状态码：401
*/
namespace App\Http\Middleware;

use Closure;
use App\Helps\ApiResponse;

class TokenMiddleware
{
    use ApiResponse;
    
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
            return response()->json($this->fail('Token Missing'), 401);
        }

        try {
            $token = openssl_decrypt($token, 'AES-256-ECB', env('TOKEN'));
            if ($token === false) {
                return response()->json($this->fail('Token Involid'), 401);
            }

            $token = json_decode($token, true);

            // 验证是否过期
            if ($token['expire'] < time()) {
                return response()->json($this->fail('Token Expire'), 401);
            }

            foreach ($token['data'] as $k => $v) {
                $request->$k = $v;
            }
        } catch (\Exception $e) {
            return response()->json($this->fail($this->errMessage), 401);
        }

        return $next($request);
    }
}