<?php
/**
* 权限验证
*/
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\DB;

class AuthMiddleware
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
        try {
            if ($request->roleId != 1) {
                $path = str_replace('/', ':', $request->path());

                $where = [
                    ['a.path', '=', $path],
                    ['b.role_id', '=', $request->roleId]
                ];
                $has = DB::table('admin_menus as a')
                    ->join('admin_role_permissions as b', 'b.menu_id', '=', 'a.id')
                    ->where($where)
                    ->exists();
                if (! $has) {
                    return response()->json(['code'=>1, 'msg'=>'403 Forbidden'], 403);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['code'=>1, 'msg'=>'403 Forbidden'], 403);
        }

        return $next($request);
    }
}