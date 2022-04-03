<?php
/**
* 基类控制器
*/
namespace App\Http\Controllers\V1;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use App\Helps\ApiResponse;

class Controller extends BaseController
{
    use ApiResponse;

    //Illuminate\Http\Request对象
    protected $request = null;

    /**
    * 记录操作日志
    * @param string $describe
    * @return viod
    */
    protected function recordLog($describe = '')
    {
        try {
            $field = [
                'admin_id'=>$this->request->adminId,
                'api'=>str_replace('/', ':', $this->request->path()),
                'describe'=>$describe,
                'ip'=>$this->request->getClientIp(),
                'device'=>$this->request->userAgent(),
            ];
            $field['created_at'] = $field['updated_at'] = date('Y-m-d H:i:s');
            DB::table('admin_operation_logs')->insert($field);
        } catch (\Exception $e) {}
    }

    /**
    * 使用私钥解密
    * https://www.php.net/manual/zh/function.openssl-private-decrypt.php
    * @param string $str
    * @return string|bool
    */
    protected function rsaDecrypt($str = '')
    {
        if (openssl_private_decrypt(base64_decode($str), $res, file_get_contents(storage_path('keys/pri.key')))) {
            return $res;
        }

        return false;
    }

    /**
    * 获取下级部门（包含当前部门）
    * @param int $id
    * @param array $ids
    * @return array
    */
    protected function getDepartmentSubordinateId($id = 0, $ids = [])
    {
        if ($id <= 0) {
            return [];
        }

        //首次进入验证有效性
        if (empty($ids) && ! DB::table('admin_departments')->where('id', $id)->exists()) {
            return [];
        }

        $ids[] = $id;

        $data = DB::table('admin_departments')->where('parent_id', $id)->select('id')->get()->toArray();
        if ($data) {
            foreach ($data as $v) {
                $ids = $this->getDepartmentSubordinateId($v->id, $ids);
            }
        }

        return $ids;
    }

    /**
    * 姓名验证，长度在2~20之间即可
    * @param string $name
    * @return bool
    */
    protected function isValidName($name = '')
    {
        if (preg_match('/^.{2,20}$/', $name) === 1) {
            return true;
        }

        return false;
    }

    /**
    * 登录账号验证，支持111位手机号^(13[0-9]|14[5|7]|15[0|1|2|3|4|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9])\d{8}$
    * 长度5~20
    * @param string $account
    * @return bool
    */
    protected function isValidAccount($account = '')
    {
        $m1 = '/^[a-zA-Z][a-zA-Z0-9_]{4,19}$/';
        $m2 = '/^(13[0-9]|14[5|7]|15[0|1|2|3|4|5|6|7|8|9]|18[0|1|2|3|5|6|7|8|9])\d{8}$/';

        if (preg_match($m1, $account) === 1 || preg_match($m2, $account) === 1) {
            return true;
        }

        return false;
    }

    /**
    * 密码验证
    * 长度6~10
    * @param string $pwd
    * @return bool
    */
    protected function isValidPassword($pwd = '')
    {
        if (preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,10}$/', $pwd) === 1) {
            return true;
        }

        return false;
    }
}
