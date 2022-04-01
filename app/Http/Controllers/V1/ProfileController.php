<?php
/**
 * 个人资料
 * 2021.7.30
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
    * 获取管理员基础信息
    */
    public function get()
    {
        $data = DB::table('admin_users as a')
          ->leftJoin('admin_roles as b', 'b.id', '=', 'a.role_id')
          ->where('a.id', $this->request->adminId)
          ->select('a.parent_id','a.account','a.true_name','a.created_at','b.role_name')
        ->first();
        if ($data) {
            //上级管理员
            $data->superior = '';
            $superiorAdmin = DB::table('admin_users')->where('id', $data->parent_id)->select('account','true_name')->first();
            unset($data->parent_id);
            if ($superiorAdmin) {
                $data->superior = $superiorAdmin->true_name ? $superiorAdmin->true_name : $superiorAdmin->account;
            }
            //注册时间
            $data->created_at = substr($data->created_at, 0, 10);
            //所属部门
            $data->department = DB::table('admin_departments')->where('id', $this->request->departmentId)->value('name');
            //所属职位
            $data->post = DB::table('admin_posts')->where('id', $this->request->postId)->value('name');
        }

        return response()->json($this->success(['profile'=>$data]));
    }

    /**
    * 修改管理员姓名
    */
    public function modifyName()
    {
        //姓名
        $name = $this->request->input('name', '');
        if (! $this->isValidName($name)) {
            return response()->json($this->fail('姓名输入有误'));
        }

        $field = [
            'true_name'=>$name,
            'updated_at'=>date('Y-m-d H:i:s')
        ];
        if (DB::table('admin_users')->where('id', $this->request->adminId)->update($field) === FALSE) {
            return response()->json($this->fail('修改失败'));
        }

        $this->recordLog('修改自己的姓名');

        return response()->json($this->success([], '修改成功'));
    }

    /**
    * 修改管理员密码
    */
    public function modifyPassword()
    {
        $pwd = $this->rsaDecrypt($this->request->input('pwd', ''));
        if (! $this->isValidPassword($pwd)){
            return response()->json($this->fail('请正确输入密码'));
        }

        try {
            DB::beginTransaction();

            $field = [
                'pwd'=>password_hash($pwd, PASSWORD_DEFAULT),
                'updated_at'=>date('Y-m-d H:i:s')
            ];
            DB::table('admin_users')->where('id', $this->request->adminId)->update($field);

            //记录登出日志
            $id = DB::table('admin_login_logs')->where('admin_id', $this->request->adminId)->orderBy('id', 'desc')->limit(1)->value('id');
            if ($id) {
                DB::table('admin_login_logs')->where('id', $id)->update(['remark'=>'成功修改登录密码，正常退出','logout_at'=>date('Y-m-d H:i:s')]);
            }

            $this->recordLog('修改自己的登录密码');

            DB::commit();
            
            return response()->json($this->success([], '修改成功，即将跳转登录页面！'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->fail($this->errMessage));
        }
    }
}