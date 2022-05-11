<?php
/**
 * 管理员管理
 * 2022.5.11
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
        $offset = ($page * $size) - $size;

        $where = [
            ['a.parentId', '=', $this->request->adminId]
        ];

        // 账号或姓名搜索
        $keyword = trim($this->request->input('keyword', ''));
        $like = '%'.$keyword.'%';

        $list = DB::table('admin_users as a')
          ->leftJoin('admin_roles as b', 'b.id', '=', 'a.roleId')
          ->leftJoin('admin_depts as c', 'c.id', '=', 'a.deptId')
          ->leftJoin('admin_posts as d', 'd.id', '=', 'a.postId')
          ->where($where)
          ->where(function($query) use ($keyword, $like) {
            if ($keyword) {
                $query->where('a.account', 'like', $like)->orWhere('a.trueName', 'like', $like);
            }
          })
          ->select('a.id','a.account','a.trueName','a.isAble','a.createdAt','b.roleName','c.deptName','d.postName')
          ->offset($offset)
          ->limit($size)
          ->orderBy('a.id', 'desc')
          ->get();
        $count = DB::table('admin_users as a')
          ->leftJoin('admin_roles as b', 'b.id', '=', 'a.roleId')
          ->leftJoin('admin_depts as c', 'c.id', '=', 'a.deptId')
          ->leftJoin('admin_posts as d', 'd.id', '=', 'a.postId')
          ->where($where)
          ->where(function($query) use ($keyword, $like) {
            if ($keyword) {
                $query->where('a.account', 'like', $like)->orWhere('a.trueName', 'like', $like);
            }
          })
          ->count();
        
        return response()->json($this->success(['list'=>$list, 'count'=>$count]));
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
        // 初始化
        $init = $this->request->input('init', false);
        if ($init === true) {
            return $this->_initData();
        }

        // 基础验证
        $check = $this->_formCheck();
        if ($check['code'] === 1) {
            return response()->json($this->fail($check['msg']));
        }

        $insertId = DB::table('admin_users')->insertGetId($check['field']);
        if ($insertId <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        $this->recordLog('添加管理员:'.json_encode($check['field']));

        return response()->json($this->success([], '添加成功'));
    }

    /**
    * 编辑
    */
    public function edit()
    {
        // 管理员ID
        $id = intval($this->request->input('id', 0));

        //账号有效性验证
        $where = [
            ['id', '=', $id],
            ['parentId','=',$this->request->adminId]
        ];
        $account = DB::table('admin_users')->where($where)->value('account');
        if (! $account) {
            return response()->json($this->fail('账号不存在'));
        }

        // 初始化
        $init = $this->request->input('init', false);
        if ($init === true) {
            return $this->_initData($id);
        }

        // 基础验证
        $check = $this->_formCheck($id);
        if ($check['code'] === 1) {
            return response()->json($this->fail($check['msg']));
        }

        if (DB::table('admin_users')->where('id', $id)->update($check['field']) === false) {
            return response()->json($this->fail('编辑失败'));
        }

        $this->recordLog('编辑管理员，id='.$id);

        return response()->json($this->success([], '编辑成功'));
    }

    /**
    */
    private function _formCheck($id = 0)
    {
        $ret = ['code'=>1, 'msg'=>'未知错误'];

        // 账号
        $account = $this->request->input('account', '');
        if ($id <= 0 && ! $this->isValidAccount($account)){
            $ret['msg'] = '账号输入有误';
            return $ret;
        }

        // 密码
        $pwd = $this->rsaDecrypt($this->request->input('pwd', ''));
        if ($id <= 0 && ! $this->isValidPassword($pwd)){
            $ret['msg'] = '密码输入有误';
            return $ret;
        }

        // 姓名验证
        $trueName = $this->request->input('trueName', '');
        if ($trueName && ! $this->isValidName($trueName)) {
            $ret['msg'] = '姓名输入有误';
            return $ret;
        }

        // 账号唯一性验证
        if ($id <= 0 && DB::table('admin_users')->where('account', $account)->exists()) {
            $ret['msg'] = '该账号已存在，请重新输入';
            return $ret;
        }

        // 角色验证
        $roleId = intval($this->request->input('roleId', 0));
        if ($roleId > 0) {
            $where = [
                ['id', '=', $roleId],
                ['parentId','=',$this->request->roleId]
            ];
            if (! DB::table('admin_roles')->where($where)->exists()) {
                $ret['msg'] = '角色不存在';
                return $ret;
            }
        }

        // 部门验证
        $deptId = intval($this->request->input('deptId', 0));
        if ($deptId > 0) {
            // 部门有效性
            if (! DB::table('admin_depts')->where('id', $deptId)->exists()) {
                $ret['msg'] = '部门不存在';
                return $ret;
            }

            // 是否所属下级，也可以同部门
            $deptIds = $this->getChildrenDeptId($this->request->deptId);
            $deptIds[] = $this->request->deptId;
            if (! in_array($deptId, $deptIds)) {
                $ret['msg'] = '非法选择部门';
                return $ret;
            }
        }

        // 岗位验证
        $postId = intval($this->request->input('postId', 0));
        if ($postId > 0) {
            if (! DB::table('admin_posts')->where('id', $postId)->exists()) {
                $ret['msg'] = '岗位不存在';
                return $ret;
            }
        }

        // 默认为编辑时所需字段
        $date = date('Y-m-d H:i:s');
        $field = [
            'trueName'=>$trueName,
            'roleId'=>$roleId,
            'deptId'=>$deptId,
            'postId'=>$postId,
            'isAble'=>intval($this->request->input('isAble', 1)),
            'updatedAt'=>$date
        ];
        if ($id <= 0) {
            $field['parentId'] = $this->request->adminId;
            $field['account'] = $account;
            $field['pwd'] = password_hash($pwd, PASSWORD_DEFAULT);
            $field['createdAt'] = $date;
        }

        return ['code'=>0, 'field'=>$field];
    }

    /**
    * 添加、编辑前初始化
    * @param int $id
    * @return json
    */
    private function _initData($id = 0)
    {
        // 岗位
        $data['posts'] = DB::table('admin_posts')->select('id', 'postName')->get();
        // 角色
        $data['roles'] = DB::table('admin_roles')->where('parentId', $this->request->roleId)->select('id', 'roleName')->get();
        // 部门
        $data['depts'] = $this->_getDepts($this->request->deptId, true);

        // 编辑时获取基本数据
        if ($id > 0) {
            $data['info'] = DB::table('admin_users')->where('id', $id)->select('id', 'account', 'trueName', 'roleId', 'deptId', 'postId', 'isAble')->first();
            if ($data['info']) {
                $data['info']->roleId = $data['info']->roleId > 0 ? $data['info']->roleId : '';
                $data['info']->postId = $data['info']->postId > 0 ? $data['info']->postId : '';
            }
        }

        return response()->json($this->success($data));
    }

    /**
    * 获取当前账号下的部门
    * @param int $parentId
    * @param bool $init
    * @return array
    */
    private function _getDepts($parentId = 0, $init = false)
    {
        $field = $init ? 'id' : 'parentId';

        $data = DB::table('admin_depts')->where($field, $parentId)->select('id', 'deptName AS label')->orderBy('sort')->get()->toArray();
        if ($data) {
            foreach ($data as $k => $v) {
                $data[$k]->children = $this->_getDepts($v->id);
            }
        }

        return $data;
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
            // 是否存在
            $where = [
                ['id', '=', $id],
                ['parentId', '=', $this->request->adminId]
            ];
            $account = DB::table('admin_users')->where($where)->value('account');
            if (! $account) {
                $is_ok = false;
                $msg = '非法操作';
                break;
            }
            // 是否存在下级
            if (DB::table('admin_users')->where('parentId', $id)->exists()) {
                $is_ok = false;
                $msg = '管理员：“'.$account.'”，还有下级，禁止删除！';
                break;
            }
            $admins[] = $account;
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
            ['parentId', '=', $this->request->adminId]
        ];
        if (! DB::table('admin_users')->where($where)->exists()) {
            return response()->json($this->fail('该账号不存在'));
        }

        //密码
        $pwd = $this->rsaDecrypt($this->request->input('pwd', ''));
        if (! $this->isValidPassword($pwd)){
            return response()->json($this->fail('密码输入有误'));
        }

        if (! DB::table('admin_users')->where('id', $id)->update(['pwd'=>password_hash($pwd, PASSWORD_DEFAULT), 'updatedAt'=>date('Y-m-d H:i:s')])) {
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