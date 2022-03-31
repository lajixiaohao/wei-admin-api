<?php
/**
 * 登录
 * 2021.7.14
 */
namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

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
        try {
            //验证码
            $uid = trim($this->request->input('uid', ''));
            $captcha = intval($this->request->input('captcha', 0));

            if (! $uid) {
                return response()->json($this->fail('非法请求'));
            }

            $res = Redis::get('captcha_'.$uid);

            if (is_null($res)) {
                return response()->json($this->fail('验证码过期或无效'));
            }

            Redis::del('captcha_'.$uid);

            if (intval($res) != $captcha) {
                return response()->json($this->fail('验证码不正确'));
            }

            //账号
            $account = $this->request->input('account', '');
            if (! $this->isValidAccount($account)){
                return response()->json($this->fail('账号输入有误'));
            }

            //密码
            $pwd = $this->decryptData($this->request->input('pwd', ''));
            if (! $this->isValidPassword($pwd)){
                return response()->json($this->fail('密码输入有误'));
            }

            $where = [
                ['account','=',$account],
                ['is_able','=',1]
            ];
            $admin = DB::table('admin_users')->where($where)->first();
            if (! $admin) {
                return response()->json($this->fail('账号无效'));
            }
            if (! password_verify($pwd, $admin->pwd)) {
                return response()->json($this->fail('密码不正确'));
            }
            // 角色有效性验证
            $where = [
                ['id','=',$admin->role_id],
                ['is_able','=',1]
            ];
            if (! DB::table('admin_roles')->where($where)->exists()) {
                return response()->json($this->fail('该账号尚未分配权限或权限无效'));
            }

            // 登录日志
            $field = [
                'admin_id'=>$admin->id,
                'ip'=>$this->request->getClientIp(),
                'device'=>$this->request->userAgent(),
                'login_at'=>date('Y-m-d H:i:s')
            ];
            $loginId = DB::table('admin_login_logs')->insertGetId($field);

            $data = [
                'admin_id'=>$admin->id,
                'role_id'=>$admin->role_id,
                'department_id'=>$admin->department_id,
                'post_id'=>$admin->post_id,
                'login_id'=>$loginId
            ];
            $token = $this->_createToken($data);
            return response()->json($this->success(['token'=>$token], '登录成功'));
        } catch (\Exception $e) {
            return response()->json($this->fail($e->getMessage()));
        }
    }

    /**
    * 创建生成token
    * @return string
    */
    private function _createToken($data = [], $expire = 3600)
    {
        $token = [
            'appkey'=>env('APP_KEY'),
            'data'=>$data,
            'expire'=>$expire
        ];

        return $this->encryptData(json_encode($token));
    }
}