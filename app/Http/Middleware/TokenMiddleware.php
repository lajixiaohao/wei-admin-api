<?php
/**
* 验证token
*/
namespace App\Http\Middleware;

use Closure;
use phpseclib\Crypt\RSA;

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
            $rsa = new RSA();
            $rsa->loadKey(file_get_contents(storage_path('keys/pri.key')));
            $rsa->setEncryptionMode(2);

            $data = json_decode($rsa->decrypt(base64_decode($token)), true);
            if (! $data) {
                return response()->json(['code'=>2001, 'msg'=>'Token Involid']);
            }

            //appkey验证
            if ($data['appkey'] != env('APP_KEY')) {
                return response()->json(['code'=>2001, 'msg'=>'Token Involid']);
            }

            //是否过期验证
            if ((time() + $data['expire']) < time()) {
                return response()->json(['code'=>2001, 'msg'=>'Token Involid']);
            }

            $request->adminId = $data['data']['admin_id'];
            $request->roleId = $data['data']['role_id'];
            $request->departmentId = $data['data']['department_id'];
            $request->postId = $data['data']['post_id'];
            $request->loginId = $data['data']['login_id'];
        } catch (\Exception $e) {
            return response()->json(['code'=>2001, 'msg'=>$e->getMessage()]);
        }

        return $next($request);
    }
}