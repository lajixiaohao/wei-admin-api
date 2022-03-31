<?php
/**
 * 角色管理
 * 2021.7.26
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
    * 角色列表（直属下级角色）
    */
    public function list()
    {
        $page = $this->request->input('page', 1);
        $size = $this->request->input('size', 10);
        $offset = (($page * $size) - $size);

        $where = [
            ['parent_id','=',$this->request->roleId]
        ];

        $role_name = trim($this->request->input('role_name', ''));
        if ($role_name) {
            $where[] = ['role_name', 'like', '%'.$role_name.'%'];
        }
        
        $list = DB::table('admin_roles')
          ->where($where)
          ->select('id','role_name','role_describe','is_able','created_at')
          ->offset($offset)
          ->limit($size)
          ->orderBy('id', 'desc')
        ->get();
        $count = DB::table('admin_roles')->where($where)->count();
        
        return response()->json($this->success(['list'=>$list, 'count'=>$count]));
    }

    /**
    * 添加角色
    */
    public function add()
    {
        $role_name = $this->request->input('role_name', '');
        if (mb_strlen($role_name) < 2 || mb_strlen($role_name) > 20) {
            return response()->json($this->fail('角色名长度在 2~20 个字符'));
        }

        //同一角色下，不能有相同的角色
        $where = [
            ['parent_id', '=', $this->request->roleId],
            ['role_name', '=', $role_name]
        ];
        if (DB::table('admin_roles')->where($where)->exists()) {
            return response()->json($this->fail('该角色已存在'));
        }

        $field['parent_id'] = $this->request->roleId;
        $field['role_name'] = $role_name;
        $field['role_describe'] = trim($this->request->input('role_describe', ''));
        $field['is_able'] = intval($this->request->input('is_able', 1));
        $field['created_at'] = $field['updated_at'] = date('Y-m-d H:i:s');

        $insertId = DB::table('admin_roles')->insertGetId($field);
        if ($insertId <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        //写入日志
        $this->recordLog('添加角色:'.$role_name);

        return response()->json($this->success([], '添加成功'));
    }

    /**
    * 编辑角色
    */
    public function edit()
    {
        $id = intval($this->request->input('id', 0));
        $param['role_name'] = trim($this->request->input('role_name', ''));
        $param['role_describe'] = trim($this->request->input('role_describe', ''));
        $param['updated_at'] = date('Y-m-d H:i:s');
        $param['is_able'] = intval($this->request->input('is_able', 1));

        if (mb_strlen($param['role_name']) < 2 || mb_strlen($param['role_name']) > 20) {
            return response()->json($this->fail('角色名长度在 2~20 个字符'));
        }

        $where = [
            ['parent_id', '=', $this->request->roleId],
            ['id', '=', $id]
        ];
        if (! DB::table('admin_roles')->where($where)->exists()) {
            return response()->json($this->fail('该角色不存在'));
        }

        //角色名不能重复
        $where = [
            ['id', '<>', $id],
            ['role_name', '=', $param['role_name']]
        ];
        if (DB::table('admin_roles')->where($where)->exists()) {
            return response()->json($this->fail('该角色已存在'));
        }

        if (DB::table('admin_roles')->where('id', $id)->update($param) === FALSE) {
            return response()->json($this->fail('编辑失败'));
        }

        //写入日志
        $this->recordLog('编辑角色id:'.$id);

        return response()->json($this->success([], '编辑成功'));
    }

    /**
    * 删除角色
    */
    public function remove()
    {
        $id = intval($this->request->input('id', 0));

        $where = [
            ['parent_id', '=', $this->request->roleId],
            ['id', '=', $id]
        ];
        $data = DB::table('admin_roles')->where($where)->select('role_name')->first();
        if (! $data) {
            return response()->json($this->fail('该角色不存在'));
        }

        //下级角色
        $ids = $this->_getSubordinate([], $id);

        DB::beginTransaction();
        try {
            DB::table('admin_roles')->whereIn('id', $ids)->delete();
            DB::table('admin_role_permissions')->whereIn('role_id', $ids)->delete();
            //写入日志
            $this->recordLog('删除角色:'.$data->role_name.',及其下属角色');

            DB::commit();
            return response()->json($this->success([], '删除成功'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->fail($this->errMessage));
        }

        return response()->json($this->fail('删除失败'));
    }

    /**
    * 递归获取下级角色
    * @param array $ids
    * @param int $id
    * @return array
    */
    private function _getSubordinate($ids = [], $id = 0)
    {
        $ids[] = $id;

        $data = DB::table('admin_roles')->where('parent_id', $id)->select('id')->get()->toArray();
        if ($data) {
            foreach ($data as $v) {
                $ids = $this->_getSubordinate($ids, $v->id);
            }
        }

        return $ids;
    }

    /**
    * 角色关系树
    * @return json
    */
    public function tree()
    {
        $id = intval($this->request->input('id', 0));
        $id = $id > 0 ? $id : $this->request->roleId;

        $data = DB::table('admin_roles')->where('parent_id', $id)->select('id','role_name')->orderBy('id', 'desc')->get()->toArray();
        if ($data) {
            foreach ($data as $k => $v) {
                //默认为叶子节点
                $data[$k]->leaf = true;
                $count = DB::table('admin_roles')->where('parent_id', $v->id)->count();
                if ($count) {
                    $data[$k]->leaf = false;
                    //当前节点下子节点个数
                    $data[$k]->role_name .= '('.$count.')';
                }
            }
        }

        return response()->json($this->success(['list'=>$data]));
    }

    /**
    * 权限分配
    */
    public function permissionAssign()
    {
        $role_id = intval($this->request->input('role_id', 0));

        //分配前获取权限信息
        $is_init = intval($this->request->input('is_init', 0));
        if ($is_init == 1) {
            return $this->_getPermissionInfo($role_id);
        }

        //验证1
        if ($role_id == $this->request->roleId) {
            return response()->json($this->fail('不能给自己授权'));
        }

        //验证2
        $where = [
            ['id', '=', $role_id],
            ['parent_id', '=', $this->request->roleId]
        ];
        if (! DB::table('admin_roles')->where($where)->exists()) {
            return response()->json($this->fail('该角色不存在'));
        }

        //当前是提交的权限，可能不包含一级菜单
        $ids = explode(',', $this->request->input('ids', ''));
        if (! $ids) {
            return response()->json($this->fail('请选择权限'));
        }

        $field = [];
        foreach ($ids as $v) {
            $field[] = ['role_id'=>$role_id, 'menu_id'=>intval($v)];
        }

        try {
            //先删除再分配
            DB::table('admin_role_permissions')->where('role_id', $role_id)->delete();
            DB::table('admin_role_permissions')->insert($field);

            //写入日志
            $this->recordLog('分配角色权限。被分配角色id='.$role_id);
            
            return response()->json($this->success([], '分配成功'));
        } catch (\Exception $e) {
            return response()->json($this->fail($this->errMessage));
        }

        return response()->json($this->fail('提交失败'));
    }

    /**
    * 分配权限前获取权限信息
    * @param int $role_id 被分配者角色ID
    * @return json
    */
    private function _getPermissionInfo($role_id = 0)
    {
        //菜单权限树、选中的节点
        $tree = $checked = [];

        //分配者菜单、权限
        $assignPermission = [];
        if ($this->request->roleId == 1) {
            $assign = DB::table('admin_menus')->select('id')->get()->toArray();
            if ($assign) {
                $assignPermission = array_map(function ($item) {
                    return $item->id;
                }, $assign);
            }
        } else {
            $assign = DB::table('admin_role_permissions')->where('role_id', $this->request->roleId)->select('menu_id')->get()->toArray();
            if ($assign) {
                $assignPermission = array_map(function ($item) {
                    return $item->menu_id;
                }, $assign);
            }
        }

        //被分配者菜单、权限
        $assignedPermission = [];
        $assigned = DB::table('admin_role_permissions')->where('role_id', $role_id)->select('menu_id')->get()->toArray();
        if ($assigned) {
            $assignedPermission = array_map(function ($item) {
                return $item->menu_id;
            }, $assigned);
        }

        //1、获取一级菜单
        $where = [
            ['parent_id','=',0],
            ['is_show','=',1]
        ];
        $firstMenu = DB::table('admin_menus')
          ->whereIn('id', $assignPermission)
          ->where($where)
          ->select('id', 'title')
          ->orderBy('sort')
        ->get()->toArray();
        if ($firstMenu) {
            foreach ($firstMenu as $k1 => $v1) {
                $tree[$k1]['id'] = $v1->id;
                $tree[$k1]['label'] = $v1->title;
                $tree[$k1]['children'] = [];

                //被分配者拥有的权限，默认选择
                if (in_array($v1->id, $assignedPermission)) {
                    $checked[] = $v1->id;
                }

                //二级菜单
                $where = [
                    ['parent_id','=',$v1->id],
                    ['menu_type','=',1],
                    ['is_show','=',1]
                ];
                $secondMenu = DB::table('admin_menus')
                  ->where($where)
                  ->select('id', 'title')
                  ->orderBy('sort')
                ->get()->toArray();
                if ($secondMenu) {
                    $c2 = [];
                    foreach ($secondMenu as $k2 => $v2) {
                        //禁止分配菜单管理
                        if ($v2->id != 2 && in_array($v2->id, $assignPermission)) {
                            $c2[$k2]['id'] = $v2->id;
                            $c2[$k2]['label'] = $v2->title;
                            $c2[$k2]['children'] = [];

                            //被分配者拥有的权限
                            if (in_array($v2->id, $assignedPermission)) {
                                $checked[] = $v2->id;
                            }

                            //权限
                            $thirdMenu = DB::table('admin_menus')
                              ->where([['parent_id','=',$v2->id], ['menu_type','=',3]])
                              ->select('id', 'title', 'menu_type')
                              ->orderBy('sort')
                            ->get()->toArray();
                            if ($thirdMenu) {
                                $c3 = [];
                                foreach ($thirdMenu as $k3 => $v3) {
                                    if (in_array($v3->id, $assignPermission)) {
                                        $c3[$k3]['id'] = $v3->id;
                                        $c3[$k3]['label'] = '【权限】'.$v3->title;
                                        $c3[$k3]['children'] = [];

                                        //被分配者拥有的权限
                                        if (in_array($v3->id, $assignedPermission)) {
                                            $checked[] = $v3->id;
                                        }
                                    }
                                }
                                $c2[$k2]['children'] = array_values($c3);
                            }
                        }
                    }
                    $tree[$k1]['children'] = array_values($c2);
                } else {
                    //二级权限
                    $where = [
                        ['parent_id','=',$v1->id],
                        ['menu_type','=',3]
                    ];
                    $secondPermission = DB::table('admin_menus')
                      ->where($where)
                      ->select('id', 'title')
                      ->orderBy('sort')
                    ->get()->toArray();
                    if ($secondPermission) {
                        $c2 = [];
                        foreach ($secondPermission as $k2 => $v2) {
                            if (in_array($v2->id, $assignPermission)) {
                                $c2[$k2]['id'] = $v2->id;
                                $c2[$k2]['label'] = '【权限】'.$v2->title;
                                $c2[$k2]['children'] = [];

                                //被分配者拥有的权限
                                if (in_array($v2->id, $assignedPermission)) {
                                    $checked[] = $v2->id;
                                }
                            }
                        }
                        $tree[$k1]['children'] = array_values($c2);
                    }
                }
            }
        }

        return response()->json($this->success(['trees'=>$tree, 'checked'=>$checked]));
    }
}