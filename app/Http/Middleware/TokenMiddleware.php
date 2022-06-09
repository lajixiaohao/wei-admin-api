<?php
/**
* token验证
* 验证不通过，将返回HTTP状态码：401
*/
namespace App\Http\Middleware;

use Closure;
use App\Helps\ApiResponse;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

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
        try {
            $decrypted = Crypt::decryptString($request->header('token', ''));

            $token = json_decode($decrypted, true);

            // 验证是否过期
            if ($token['expire'] < time()) {
                return response()->json($this->fail('Token Expire'), 401);
            }

            foreach ($token['data'] as $k => $v) {
                $request->$k = $v;
            }
        } catch (\DecryptException $e) {
            return response()->json($this->fail($this->errMessage), 401);
        }

        return $next($request);
    }
}