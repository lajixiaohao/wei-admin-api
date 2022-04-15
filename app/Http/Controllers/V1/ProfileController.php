<?php
/**
 * 个人资料
 * 2022.4.15
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
        $data = DB::table('sys_admins as a')
          ->leftJoin('sys_roles as b', 'b.id', '=', 'a.roleId')
          ->where('a.id', $this->request->adminId)
          ->select('a.parentId','a.account','a.trueName','a.createdAt','b.roleName AS role')
        ->first();
        if ($data) {
            //上级管理员
            $data->superior = '';
            $superiorAdmin = DB::table('sys_admins')->where('id', $data->parentId)->select('account','trueName')->first();
            unset($data->parentId);
            if ($superiorAdmin) {
                $data->superior = $superiorAdmin->trueName ? $superiorAdmin->trueName : $superiorAdmin->account;
            }
            //注册时间
            $data->createdAt = date('Y-m-d', $data->createdAt);
            //所属部门
            $data->dept = DB::table('sys_depts')->where('id', $this->request->deptId)->value('deptName');
            //所属职位
            $data->post = DB::table('sys_posts')->where('id', $this->request->postId)->value('postName');
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
            'trueName'=>$name,
            'updatedAt'=>time()
        ];
        if (DB::table('sys_admins')->where('id', $this->request->adminId)->update($field) === FALSE) {
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
                'updatedAt'=>time()
            ];
            DB::table('sys_admins')->where('id', $this->request->adminId)->update($field);

            //记录登出日志
            $id = DB::table('sys_login_logs')->where('adminId', $this->request->adminId)->orderBy('id', 'desc')->limit(1)->value('id');
            if ($id) {
                DB::table('sys_login_logs')->where('id', $id)->update(['logoutAt'=>time()]);
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