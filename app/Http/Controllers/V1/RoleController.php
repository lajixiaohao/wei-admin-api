<?php
/**
 * 角色管理
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    // 分配者权限ID
    private $assignIds = [];
    // 被分配者权限ID
    private $assignedIds = [];

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
        // 角色ID
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

        return response()->json($this->success($data));
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
            $menuId = intval($v);
            // 必须继承至上级
            $where = [
                ['roleId', '=', $this->request->roleId],
                ['menuId', '=', $menuId]
            ];
            if ($this->request->roleId != 1 && ! DB::table('admin_role_permissions')->where($where)->exists()) {
                return response()->json($this->fail('非法分配权限'));
            }
            
            $field[] = ['roleId'=>$roleId, 'menuId'=>$menuId];
        }

        try {
            //先删除再分配
            DB::table('admin_role_permissions')->where('roleId', $roleId)->delete();
            DB::table('admin_role_permissions')->insert($field);

            $this->recordLog('分配角色权限。被分配角色id='.$roleId);
            
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
        // 超管
        if ($this->request->roleId == 1) {
            $assign = DB::table('admin_menus')->select('id')->get()->toArray();
            if ($assign) {
                $this->assignIds = array_map(function ($item) {
                    return $item->id;
                }, $assign);
            }
        } else {
            $assign = DB::table('admin_role_permissions')->where('roleId', $this->request->roleId)->select('menuId')->get()->toArray();
            if ($assign) {
                $this->assignIds = array_map(function ($item) {
                    return $item->menuId;
                }, $assign);
            }
        }

        $assigned = DB::table('admin_role_permissions')->where('roleId', $roleId)->select('menuId')->get()->toArray();
        if ($assigned) {
            $this->assignedIds = array_map(function ($item) {
                return $item->menuId;
            }, $assigned);
        }

        // 获取一级菜单
        $where = [
            ['parentId', '=', 0],
            ['isShow', '=', 1],
            ['type', '=', 1]
        ];
        $menus = DB::table('admin_menus')->where($where)->whereIn('id', $this->assignIds)->select('id', 'title AS label')->orderBy('sort')->get()->toArray();
        if ($menus) {
            foreach ($menus as $k => $v) {
                // 下级菜单或权限
                $menus[$k]->children = $this->_getPermission($v->id);
            }
        }

        // 角色名
        $roleName = DB::table('admin_roles')->where('id', $roleId)->value('roleName');

        return response()->json($this->success(['menus'=>$menus, 'checked'=>$this->assignedIds, 'roleName'=>$roleName]));
    }

    /**
    * 递归获取下级菜单或权限
    * @param int $parentId
    * @return array
    */
    private function _getPermission($parentId = 0)
    {
        // 获取下级权限
        $where = [
            ['parentId', '=', $parentId],
            ['type', '=', 3]
        ];
        $data = DB::table('admin_menus')->where($where)->whereIn('id', $this->assignIds)->select('id', 'title AS label')->orderBy('sort')->get()->toArray();
        if ($data) {
            foreach ($data as $k => $v) {
                $data[$k]->label = '【权限】'.$data[$k]->label;
            }
            return $data;
        }

        // 获取下级菜单
        $where = [
            ['parentId', '=', $parentId],
            ['isShow', '=', 1],
            ['type', '=', 1]
        ];
        $data = DB::table('admin_menus')->where($where)->whereIn('id', $this->assignIds)->select('id', 'title AS label')->orderBy('sort')->get()->toArray();
        if ($data) {
            foreach ($data as $k => $v) {
                // 禁止分配二级菜单，前端显示为禁用
                if ($v->id === 5) {
                    $data[$k]->disabled = true;
                }
                $data[$k]->children = $this->_getPermission($v->id);
            }
        }

        return $data;
    }
}