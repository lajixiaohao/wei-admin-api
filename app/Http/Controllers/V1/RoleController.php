<?php
/**
 * 角色管理
 * 2022.5.6
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
        $offset = ($page * $size) - $size;

        $where = [
            ['parentId','=',$this->request->roleId]
        ];

        $roleName = trim($this->request->input('roleName', ''));
        if ($roleName) {
            $where[] = ['roleName', 'like', '%'.$roleName.'%'];
        }
        
        $data['list'] = DB::table('admin_roles')
            ->where($where)
            ->offset($offset)
            ->limit($size)
            ->orderBy('id', 'desc')
            ->get();
        $data['count'] = DB::table('admin_roles')->where($where)->count();
        
        return response()->json($this->success($data));
    }

    /**
    * 添加角色
    */
    public function add()
    {
        $roleName = $this->request->input('roleName', '');
        if (! $roleName) {
            return response()->json($this->fail('请输入角色名称'));
        }

        // 同一角色下，不能有相同的角色
        $where = [
            ['parentId', '=', $this->request->roleId],
            ['roleName', '=', $roleName]
        ];
        if (DB::table('admin_roles')->where($where)->exists()) {
            return response()->json($this->fail('该角色已存在'));
        }

        $field = [
            'parentId'=>$this->request->roleId,
            'roleName'=>$roleName,
            'roleIntroduce'=>trim($this->request->input('roleIntroduce', '')),
            'isAble'=>intval($this->request->input('isAble', 1))
        ];
        $field['createdAt'] = $field['updatedAt'] = date('Y-m-d H:i:s');

        $insertId = DB::table('admin_roles')->insertGetId($field);
        if ($insertId <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        $this->recordLog('添加角色:'.$roleName);

        return response()->json($this->success([], '添加成功'));
    }

    /**
    * 编辑角色
    */
    public function edit()
    {
        $id = intval($this->request->input('id', 0));

        $where = [
            ['parentId', '=', $this->request->roleId],
            ['id', '=', $id]
        ];
        if (! DB::table('admin_roles')->where($where)->exists()) {
            return response()->json($this->fail('该角色不存在'));
        }

        $roleName = $this->request->input('roleName', '');
        if (! $roleName) {
            return response()->json($this->fail('请输入角色名称'));
        }

        // 角色名不能重复
        $where = [
            ['id', '<>', $id],
            ['parentId', '=', $this->request->roleId],
            ['roleName', '=', $roleName]
        ];
        if (DB::table('admin_roles')->where($where)->exists()) {
            return response()->json($this->fail('该角色已存在'));
        }

        $field = [
            'roleName'=>$roleName,
            'roleIntroduce'=>trim($this->request->input('roleIntroduce', '')),
            'isAble'=>intval($this->request->input('isAble', 1)),
            'updatedAt'=>date('Y-m-d H:i:s')
        ];
        if (DB::table('admin_roles')->where('id', $id)->update($field) === FALSE) {
            return response()->json($this->fail('编辑失败'));
        }

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
            ['parentId', '=', $this->request->roleId],
            ['id', '=', $id]
        ];
        $data = DB::table('admin_roles')->where($where)->select('roleName')->first();
        if (! $data) {
            return response()->json($this->fail('该角色不存在'));
        }

        // 下级角色ID，包含当前角色ID
        $ids = $this->_getSubordinate([], $id);

        DB::beginTransaction();
        try {
            DB::table('admin_roles')->whereIn('id', $ids)->delete();
            DB::table('admin_role_permissions')->whereIn('roleId', $ids)->delete();
            $this->recordLog('删除角色:'.$data->roleName.',及其下属角色');

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

        $data = DB::table('admin_roles')->where('parentId', $id)->select('id')->get()->toArray();
        if ($data) {
            foreach ($data as $v) {
                $ids = $this->_getSubordinate($ids, $v->id);
            }
        }

        return $ids;
    }

    /**
    * 角色关系树
    */
    public function tree()
    {
        $id = intval($this->request->input('id', 0));
        if ($id <= 0) {
            $id = $this->request->roleId;
        }

        $data = DB::table('admin_roles')->where('parentId', $id)->select('id','roleName')->orderBy('id', 'desc')->get()->toArray();
        if ($data) {
            foreach ($data as $k => $v) {
                // 默认为叶子节点
                $data[$k]->leaf = true;
                $count = DB::table('admin_roles')->where('parentId', $v->id)->count();
                if ($count) {
                    $data[$k]->leaf = false;
                    // 当前节点下子节点个数
                    $data[$k]->roleName .= '('.$count.')';
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
        $roleId = intval($this->request->input('roleId', 0));

        // 不能给自己分配权限
        if ($roleId == $this->request->roleId) {
            return response()->json($this->fail('不能给自己分配权限'));
        }

        // 必须是当前账号直属下级角色
        $where = [
            ['id', '=', $roleId],
            ['parentId', '=', $this->request->roleId]
        ];
        if (! DB::table('admin_roles')->where($where)->exists()) {
            return response()->json($this->fail('该角色不存在'));
        }

        // 分配前获取权限信息
        $isInit = intval($this->request->input('isInit', 0));
        if ($isInit == 1) {
            return $this->_getPermissionInfo($roleId);
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
    * @param int $roleId 被分配者角色ID
    * @return json
    */
    private function _getPermissionInfo($roleId = 0)
    {
        // 分配者菜单及被分配者权限
        $menus = $checked = [];

        // 分配者菜单（权限）
        $assignPermission = [];
        // 超管
        if ($this->request->roleId == 1) {
            $assign = DB::table('admin_menus')->select('id')->get()->toArray();
            if ($assign) {
                $assignPermission = array_map(function ($item) {
                    return $item->id;
                }, $assign);
            }
        } else {
            $assign = DB::table('admin_role_permissions')->where('roleId', $this->request->roleId)->select('menuId')->get()->toArray();
            if ($assign) {
                $assignPermission = array_map(function ($item) {
                    return $item->menuId;
                }, $assign);
            }
        }

        // 被分配者菜单（权限）
        $assignedPermission = [];
        $assigned = DB::table('admin_role_permissions')->where('roleId', $roleId)->select('menuId')->get()->toArray();
        if ($assigned) {
            $assignedPermission = array_map(function ($item) {
                return $item->menuId;
            }, $assigned);
        }

        return response()->json($this->success(['menus'=>$menus, 'checked'=>$checked]));

        /*//菜单权限树、选中的节点
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

        return response()->json($this->success(['trees'=>$tree, 'checked'=>$checked]));*/
    }
}