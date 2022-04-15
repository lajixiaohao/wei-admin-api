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
                'adminId'=>$this->request->adminId,
                'api'=>str_replace('/', ':', $this->request->path()),
                'describe'=>$describe,
                'ip'=>$this->request->getClientIp(),
                'requestJson'=>json_encode($this->request->all()),
                'device'=>$this->request->userAgent(),
                'createdAt'=>time()
            ];
            DB::table('sys_operation_logs')->insert($field);
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
    /*protected function getDepartmentSubordinateId($id = 0, $ids = [])
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
    }*/

    /**
    * 姓名验证，长度在2~20之间即可
    * @param string $name
    * @return bool
    */
    protected function isValidName($name = '')
    {
        if (preg_match('/^.{2,20}$/', $name)) {
            return true;
        }

        return false;
    }

    /**
    * 账号验证
    * 长度5~20
    * @param string $str
    * @return bool
    */
    protected function isValidAccount($str = '')
    {
        if (preg_match('/^[a-zA-Z][a-zA-Z0-9_]{4,19}$/', $str)) {
            return true;
        }

        return false;
    }

    /**
    * 密码验证
    * 长度6~10
    * @param string $str
    * @return bool
    */
    protected function isValidPassword($str = '')
    {
        if (preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,10}$/', $str)) {
            return true;
        }

        return false;
    }
}
