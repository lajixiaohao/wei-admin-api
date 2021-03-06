<?php
/**
 * 登录控制
 */
namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Crypt;

class LoginController extends Controller
{
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
    * 登录处理
    */
    public function index()
    {
        // 验证码标识
        $cid = trim($this->request->input('cid', ''));
        if (! $cid) {
            return response()->json($this->fail('缺少验证码标识'));
        }

        $res = Redis::get('cid_'.$cid);
        if (is_null($res)) {
            return response()->json($this->fail('验证码过期或无效'));
        }

        // 删除验证码缓存
        Redis::del('cid_'.$cid);

        // 验证码
        $captcha = intval($this->request->input('captcha', 0));

        if (intval($res) !== $captcha) {
            return response()->json($this->fail('验证码不正确'));
        }

        // 账号
        $account = $this->request->input('account', '');
        if (! $this->isValidAccount($account)){
            return response()->json($this->fail('账号输入有误'));
        }

        // 密码
        $pwd = $this->rsaDecrypt($this->request->input('pwd', ''));
        if (! $this->isValidPassword($pwd)) {
            return response()->json($this->fail('密码输入有误'));
        }

        $where = [
            ['account', '=', $account],
            ['isAble', '=', 1]
        ];
        // 管理员信息
        $admin = DB::table('admin_users')->where($where)->first();
        if (! $admin) {
            return response()->json($this->fail('账号无效'));
        }

        // 验证密码
        if (! password_verify($pwd, $admin->pwd)) {
            return response()->json($this->fail('密码不正确'));
        }

        // 角色验证
        $where = [
            ['id', '=', $admin->roleId],
            ['isAble', '=', 1]
        ];
        if (! DB::table('admin_roles')->where($where)->exists()) {
            return response()->json($this->fail('权限验证失败'));
        }

        // 登录日志
        $field = [
            'adminId'=>$admin->id,
            'ip'=>inet_pton($this->request->getClientIp()),
            'device'=>$this->request->userAgent(),
            'loginAt'=>date('Y-m-d H:i:s')
        ];
        $loginId = DB::table('admin_login_logs')->insertGetId($field);
        if ($loginId <= 0) {
            return response()->json($this->fail('登录失败，无法更新登录日志'));
        }

        // 加密基本信息
        $data = [
            'expire'=>time() + env('TOKEN_EXPIRE', 3600),
            'data'=>[
                'adminId'=>$admin->id,
                'roleId'=>$admin->roleId,
                'deptId'=>$admin->deptId,
                'postId'=>$admin->postId,
                'loginId'=>$loginId
            ]
        ];

        return response()->json($this->success(['token'=>Crypt::encryptString(json_encode($data))], '登录成功'));
    }
}