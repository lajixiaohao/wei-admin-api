<?php
/**
* 基类控制器
*/
namespace App\Http\Controllers\V1;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use phpseclib\Crypt\RSA;

class Controller extends BaseController
{
    //Illuminate\Http\Request对象
    protected $request = null;

    //try catch发生错误统一返回的提示
    protected $errMessage = '服务器发生错误';

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
    * 返回成功code=0
    * @param array $data
    * @param string $msg
    * @return array
    */
    protected function success($data = [], $msg = 'success')
    {
        $ret = ['code'=>0, 'msg'=>$msg];

        if ($data) {
            $ret['data'] = $data;
        }

        return $ret;
    }

    /**
    * 返回失败code=1
    * @param string $msg
    * @return array
    */
    protected function fail($msg = 'error')
    {
        return ['code'=>1, 'msg'=>$msg];
    }

    /**
    * 加密
    * @param string $str
    * @return string|bool
    */
    protected function encryptData($str = '')
    {
        try {
            $rsa = new RSA();
            $rsa->loadKey(file_get_contents(storage_path('keys/pub.key')));
            $rsa->setEncryptionMode(2);
            return base64_encode($rsa->encrypt($str));
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
    * 解密
    * @param string $str
    * @return string|bool
    */
    protected function decryptData($str = '')
    {
        try {
            $rsa = new RSA();
            $rsa->loadKey(file_get_contents(storage_path('keys/pri.key')));
            $rsa->setEncryptionMode(2);
            return $rsa->decrypt(base64_decode($str));
        } catch (\Exception $e) {
            return false;
        }
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
    * 登录账号验证
    * 长度5~20
    * @param string $account
    * @return bool
    */
    protected function isValidAccount($account = '')
    {
        if (preg_match('/^[a-zA-Z][a-zA-Z0-9_]{4,19}$/', $account) === 1) {
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
