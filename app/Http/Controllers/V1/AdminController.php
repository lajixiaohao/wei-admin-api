<?php
/**
 * 管理员管理
 * 2021.7.27
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
    * 列表
    */
    public function list()
    {
        $page = $this->request->input('page', 1);
        $size = $this->request->input('size', 10);
        $offset = (($page * $size) - $size);

        $where = [
            ['a.parent_id','=',$this->request->adminId]
        ];

        $account = trim($this->request->input('account', ''));
        if ($account) {
            $where[] = ['a.account', 'like', '%'.$account.'%'];
        }

        $list = DB::table('admin_users as a')
          ->leftJoin('admin_roles as b', 'b.id', '=', 'a.role_id')
          ->leftJoin('admin_departments as c', 'c.id', '=', 'a.department_id')
          ->leftJoin('admin_posts as d', 'd.id', '=', 'a.post_id')
          ->where($where)
          ->select('a.id','a.account','a.true_name','a.role_id','a.department_id','a.post_id','a.is_able','a.created_at','b.role_name','c.name AS department_name','d.name AS post_name')
          ->offset($offset)
          ->limit($size)
          ->orderBy('a.id', 'desc')
        ->get();
        $count = DB::table('admin_users as a')
          ->leftJoin('admin_roles as b', 'b.id', '=', 'a.role_id')
          ->leftJoin('admin_departments as c', 'c.id', '=', 'a.department_id')
          ->leftJoin('admin_posts as d', 'd.id', '=', 'a.post_id')
          ->where($where)
        ->count();
        
        return response()->json($this->success(['list'=>$list, 'count'=>$count]));
    }

    /**
    * 添加、编辑前初始化
    * @return json
    */
    private function _initData()
    {
        $post = DB::table('admin_posts')
            ->select('id','name')
        ->get();

        $role = DB::table('admin_roles')
            ->where('parent_id', $this->request->roleId)
            ->select('id','role_name')
        ->get();

        return response()->json($this->success(['post'=>$post, 'role'=>$role]));
    }

    /**
    * 添加、编辑时懒加载部门
    * @return json
    */
    public function _lazyLoadDepartment()
    {
        $departmentId = intval($this->request->input('department_id', 0));

        $where = [
            ['parent_id','=',$departmentId]
        ];

        //过滤初始化部门
        if ($departmentId <= 0) {
            $where = [
                ['id','=',$this->request->departmentId]
            ];
        }

        $list = DB::table('admin_departments')
            ->where($where)
            ->select('id','name')
            ->get()
            ->toArray();
        if ($list) {
            foreach ($list as $k => $v) {
                $list[$k]->leaf = true;
                if (DB::table('admin_departments')->where('parent_id', $v->id)->exists()) {
                    $list[$k]->leaf = false;
                }
            }
        }

        return response()->json($this->success(['list'=>$list]));
    }

    /**
    * 添加
    */
    public function add()
    {
        //添加、编辑前初始化
        $is_init = intval($this->request->input('is_init', 0));
        if ($is_init == 1) {
            return $this->_initData();
        }

        //添加、编辑时懒加载部门
        $is_load_dept = intval($this->request->input('is_load_dept', 0));
        if ($is_load_dept == 1) {
            return $this->_lazyLoadDepartment();
        }

        //账号
        $account = $this->request->input('account', '');
        if (! $this->isValidAccount($account)){
            return response()->json($this->fail('账号输入有误'));
        }

        //密码
        $pwd = $this->rsaDecrypt($this->request->input('pwd', ''));
        if (! $this->isValidPassword($pwd)){
            return response()->json($this->fail('密码输入有误'));
        }

        //姓名验证
        $true_name = $this->request->input('true_name', '');
        if ($true_name && ! $this->isValidName($true_name)) {
            return response()->json($this->fail('姓名输入有误'));
        }

        //账号唯一性验证
        if (DB::table('admin_users')->where('account', $account)->exists()) {
            return response()->json($this->fail('该账号已存在，请重新输入'));
        }

        //角色验证
        $role_id = intval($this->request->input('role_id', 0));
        if ($role_id > 0) {
            $where = [
                ['id','=',$role_id],
                ['parent_id','=',$this->request->roleId]
            ];
            if (! DB::table('admin_roles')->where($where)->exists()) {
                return response()->json($this->fail('所选角色无效'));
            }
        }

        //部门验证
        $department_id = intval($this->request->input('department_id', 0));
        if ($department_id > 0) {
            //部门有效性
            if (! DB::table('admin_departments')->where('id', $department_id)->exists()) {
                return response()->json($this->fail('所选部门无效'));
            }

            //是否所属下级，也可以同部门
            if (! in_array($department_id, $this->getDepartmentSubordinateId($this->request->departmentId))) {
                return response()->json($this->fail('非法选择部门'));
            }
        }

        //岗位验证
        $post_id = intval($this->request->input('post_id', 0));
        if ($post_id > 0) {
            if (! DB::table('admin_posts')->where('id', $post_id)->exists()) {
                return response()->json($this->fail('所选岗位无效'));
            }
        }

        $field = [
            'parent_id'=>$this->request->adminId,
            'account'=>$account,
            'true_name'=>$true_name,
            'pwd'=>password_hash($pwd, PASSWORD_DEFAULT),
            'role_id'=>$role_id,
            'department_id'=>$department_id,
            'post_id'=>$post_id,
            'is_able'=>intval($this->request->input('is_able', 1))
        ];
        $field['created_at'] = $field['updated_at'] = date('Y-m-d H:i:s');
        $insertId = DB::table('admin_users')->insertGetId($field);
        if ($insertId <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        $this->recordLog('添加管理员account:'.$account);

        return response()->json($this->success([], '添加成功'));
    }

    /**
    * 编辑
    */
    public function edit()
    {
        //添加、编辑前初始化
        $is_init = intval($this->request->input('is_init', 0));
        if ($is_init == 1) {
            return $this->_initData();
        }

        //添加、编辑时懒加载部门
        $is_load_dept = intval($this->request->input('is_load_dept', 0));
        if ($is_load_dept == 1) {
            return $this->_lazyLoadDepartment();
        }

        $id = intval($this->request->input('id', 0));

        //姓名验证
        $true_name = $this->request->input('true_name', '');
        if ($true_name && ! $this->isValidName($true_name)) {
            return response()->json($this->fail('姓名输入有误'));
        }

        //账号有效性验证
        $where = [
            ['id','=',$id],
            ['parent_id','=',$this->request->adminId]
        ];
        $admin = DB::table('admin_users')->where($where)->first();
        if (! $admin) {
            return response()->json($this->fail('账号无效'));
        }

        //角色验证
        $role_id = intval($this->request->input('role_id', 0));
        if ($role_id != $admin->role_id) {
            $where = [
                ['id','=',$role_id],
                ['parent_id','=',$this->request->roleId]
            ];
            if (! DB::table('admin_roles')->where($where)->exists()) {
                return response()->json($this->fail('所选角色无效'));
            }
        }

        //部门验证
        $department_id = intval($this->request->input('department_id', 0));
        if ($department_id > 0 && $department_id != $admin->department_id) {
            //部门有效性
            if (! DB::table('admin_departments')->where('id', $department_id)->exists()) {
                return response()->json($this->fail('所选部门无效'));
            }

            //是否所属下级，也可以同部门
            if (! in_array($department_id, $this->getDepartmentSubordinateId($this->request->departmentId))) {
                return response()->json($this->fail('非法选择部门'));
            }
        }

        //岗位验证
        $post_id = intval($this->request->input('post_id', 0));
        if ($post_id > 0 && $post_id != $admin->post_id) {
            if (! DB::table('admin_posts')->where('id', $post_id)->exists()) {
                return response()->json($this->fail('所选岗位无效'));
            }
        }

        $field = [
            'true_name'=>$true_name,
            'role_id'=>$role_id,
            'department_id'=>$department_id,
            'post_id'=>$post_id,
            'is_able'=>intval($this->request->input('is_able', 1)),
            'updated_at'=>date('Y-m-d H:i:s')
        ];
        if (DB::table('admin_users')->where('id', $id)->update($field) === FALSE) {
            return response()->json($this->fail('编辑失败'));
        }

        $this->recordLog('编辑管理员，account='.$admin->account);

        return response()->json($this->success([], '编辑成功'));
    }

    /**
    * 管理员关系树
    */
    public function tree()
    {
        $id = intval($this->request->input('id', 0));
        $id = $id > 0 ? $id : $this->request->adminId;

        $data = [];
        $res = DB::table('admin_users')->where('parent_id', $id)->select('id','account', 'true_name')->orderBy('id','desc')->get()->toArray();
        if ($res) {
            foreach ($res as $k => $v) {
                $data[$k]['id'] = $v->id;
                $data[$k]['account'] = $v->true_name ? $v->true_name : $v->account;
                //默认为叶子节点
                $data[$k]['leaf'] = true;
                $count = DB::table('admin_users')->where('parent_id', $v->id)->count();
                if ($count > 0) {
                    $data[$k]['leaf'] = false;
                    //当前节点下子节点个数
                    $data[$k]['account'] .= '('.$count.')';
                }
            }
        }

        return response()->json($this->success(['list'=>$data]));
    }

    /**
    * 删除管理员
    */
    public function remove()
    {
        $ids = $this->request->input('ids', []);
        if (! $ids) {
            return response()->json($this->fail('请选择要删除的管理员'));
        }

        $is_ok = true;
        $msg = '';
        $admins = [];
        foreach ($ids as $id) {
            $id = intval($id);
            //是否存在
            $where = [
                ['parent_id', '=', $this->request->adminId],
                ['id', '=', $id]
            ];
            $admin = DB::table('admin_users')->where($where)->select('account')->first();
            if (! $admin) {
                $is_ok = false;
                $msg = '管理员(id='.$id.')不存在，请重新选择';
                break;
            }
            //是否存在下级
            if (DB::table('admin_users')->where('parent_id', $id)->count() > 0) {
                $is_ok = false;
                $msg = '管理员['.$admin->account.']还有下级，禁止删除！';
                break;
            }
            $admins[] = $admin->account;
        }

        if (! $is_ok) {
            return response()->json($this->fail($msg));
        }

        if (! DB::table('admin_users')->whereIn('id', $ids)->delete()) {
            return response()->json($this->fail('删除失败'));
        }

        $this->recordLog('删除管理员:'.implode(',', $admins));

        return response()->json($this->success([], '删除成功'));
    }

    /**
    * 更改密码
    */
    public function modifyPassword()
    {
        $id = intval($this->request->input('id', 0));

        //存在性验证
        $where = [
            ['id', '=', $id],
            ['parent_id', '=', $this->request->adminId]
        ];
        if (! DB::table('admin_users')->where($where)->exists()) {
            return response()->json($this->fail('该账号不存在'));
        }

        //密码
        $pwd = $this->rsaDecrypt($this->request->input('pwd', ''));
        if (! $this->isValidPassword($pwd)){
            return response()->json($this->fail('密码输入有误'));
        }

        if (! DB::table('admin_users')->where('id', $id)->update(['pwd'=>password_hash($pwd, PASSWORD_DEFAULT), 'updated_at'=>date('Y-m-d H:i:s')])) {
            return response()->json($this->fail('重置失败'));
        }

        $this->recordLog('重置管理员密码,id:'.$id);

        return response()->json($this->success([], '重置成功'));
    }

    /**
    * 变更下级接管账号
    */
    public function changeTakeover() 
    {
        //更换前获取数据或判断
        $check = intval($this->request->input('check', 0));

        //判断是否有下级
        if ($check == 1) {
            //当前接管的账号
            $admin_id = intval($this->request->input('admin_id', 0));
            //有效性验证
            $where = [
                ['id','=',$admin_id],
                ['parent_id','=',$this->request->adminId]
            ];
            if (! DB::table('admin_users')->where($where)->exists()) {
                return response()->json($this->fail('该账号不存在'));
            }
            $exists = DB::table('admin_users')->where('parent_id', $admin_id)->exists();
            return response()->json($this->success(['exists'=>$exists]));
        }
        //返回当前管理员下级
        if ($check == 2) {
            $where = [
                ['parent_id','=',$this->request->adminId],
                ['is_able','=',1]
            ];
            $list = DB::table('admin_users')->where($where)->select('id','account')->orderBy('id', 'desc')->get();
            return response()->json($this->success(['list'=>$list]));
        }

        //提交更换
        $old_admin_id = intval($this->request->input('old_admin_id', 0));
        $new_admin_id = intval($this->request->input('new_admin_id', 0));
        if ($old_admin_id == $new_admin_id) {
            return response()->json($this->fail('请选择要变更的账号'));
        }

        //老账号有效性验证
        $where = [
            ['id','=',$old_admin_id],
            ['parent_id','=',$this->request->adminId]
        ];
        if (! DB::table('admin_users')->where($where)->exists()) {
            return response()->json($this->fail('原账号不存在'));
        }
        //新账号有效性验证
        $where = [
            ['id','=',$new_admin_id],
            ['parent_id','=',$this->request->adminId]
        ];
        if (! DB::table('admin_users')->where($where)->exists()) {
            return response()->json($this->fail('新账号不存在'));
        }

        if (! DB::table('admin_users')->where('parent_id', $old_admin_id)->update(['parent_id'=>$new_admin_id, 'updated_at'=>date('Y-m-d H:i:s')])) {
            return response()->json($this->fail('变更失败'));
        }

        $this->recordLog('变更下级直属管理员。原管理员id='.$old_admin_id.'，新管理员id='.$new_admin_id);

        return response()->json($this->success([], '变更成功'));
    }
}