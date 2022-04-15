<?php
/**
 * 退出登录
 * 2021.7.29
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogoutController extends Controller
{
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function index()
    {
        DB::table('sys_login_logs')->where('id', $this->request->loginId)->update(['logoutAt'=>time()]);
        return response()->json($this->success([], '退出成功'));
    }
}