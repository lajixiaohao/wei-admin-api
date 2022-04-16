<?php
/**
* 权限验证
* 验证不通过，将返回HTTP状态码：403
*/
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\DB;
use App\Helps\ApiResponse;

class AuthMiddleware
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
            if ($request->roleId !== 1) {
                $path = str_replace('/', ':', $request->path());
                $where = [
                    ['a.path', '=', $path],
                    ['b.roleId', '=', $request->roleId]
                ];
                $has = DB::table('sys_menus as a')
                    ->join('sys_role_permissions as b', 'b.menuId', '=', 'a.id')
                    ->where($where)
                    ->exists();
                if (! $has) {
                    return response()->json($this->fail('403 Forbidden'), 403);
                }
            }
        } catch (\Exception $e) {
            return response()->json($this->fail($this->errMessage), 403);
        }

        return $next($request);
    }
}