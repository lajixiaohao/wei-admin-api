<?php
/**
 * 菜单管理
 * 2022.4.17
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
    * 菜单树
    */
    public function tree()
    {
        // 父级ID
    	$parentId = intval($this->request->input('parentId', 0));

        $data = DB::table('admin_menus')
            ->where('parentId', $parentId)
            ->orderBy('sort')
            ->get()
            ->toArray();
        if ($data) {
            foreach ($data as $k => $v) {
                // 默认为叶子节点，即没有下一级
                $data[$k]->leaf = true;
                if (DB::table('admin_menus')->where('parentId', $v->id)->count()) {
                    $data[$k]->leaf = false;
                }
            }
        }

    	return response()->json($this->success(['menu'=>$data]));
    }

    /**
    * 添加菜单
    */
    public function add()
    {
        // 初始化，获取所有显示菜单
        $init = $this->request->input('init', false);
        if ($init === true) {
            $data['menu'] = $this->_getMenus();
            return response()->json($this->success($data));
        }

        $check = $this->_menuCheck();
        if ($check['code'] === 1) {
            return response()->json($this->fail($check['msg']));
        }

        if (DB::table('admin_menus')->insertGetId($check['field']) <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        $this->recordLog('添加菜单：'.json_encode($check['field']));

        return response()->json($this->success([], '添加成功'));
    }

    /**
    * 添加、编辑菜单基础验证
    */
    private function _menuCheck($id = 0)
    {
        $ret = ['code'=>1, 'msg'=>'未知错误'];

        // 公用的条件判断
        $idWhere = [];
        // 编辑
        if ($id > 0) {
            // 菜单是否存在
            if (! DB::table('admin_menus')->where('id', $id)->exists()) {
                $ret['msg'] = '该菜单不存在';
                return $ret;
            }

            $idWhere = ['id', '<>', $id];
        }

        // 菜单类型
        $type = intval($this->request->input('type', 0));
        if (! in_array($type, [1, 2])) {
            $ret['msg'] = '非法菜单类型';
            return $ret;
        }

        // 上级菜单ID
        $parentId = intval($this->request->input('parentId', 0));
        // 1、上级菜单有效性验证
        if ($parentId > 0 && ! DB::table('admin_menus')->where('id', $parentId)->exists()) {
            $ret['msg'] = '上级菜单不存在';
            return $ret;
        }
        // 左侧菜单层级不能超过3个
        $parentIds = $this->_getParentIds($parentId);
        if (count($parentIds) > 2) {
            $ret['msg'] = '左侧菜单层级不能超过3个';
            return $ret;
        }

        // 2、顶级菜单不能为隐式菜单
        if ($parentId === 0 && $type === 2) {
            $ret['msg'] = '顶级菜单类型不能为隐式菜单';
            return $ret;
        }

        // 菜单名称
        $title = trim($this->request->input('title', ''));
        if (! $title) {
            $ret['msg'] = '请输入菜单名称';
            return $ret;
        }

        // 3、同级下菜单名称不能重复
        $where = [
            ['parentId', '=', $parentId],
            ['title', '=', $title]
        ];
        if ($id > 0) {
            $where[] = $idWhere;
        }
        if (DB::table('admin_menus')->where($where)->exists()) {
            $ret['msg'] = '同级下该菜单已存在';
            return $ret;
        }

        // 4、路由地址不能重复
        $path = trim($this->request->input('path', ''));
        $where = [
            ['path', '=', $path]
        ];
        if ($id > 0) {
            $where[] = $idWhere;
        }
        if ($path && DB::table('admin_menus')->where($where)->exists()) {
            $ret['msg'] = '该路由地址已存在';
            return $ret;
        }

        // 编辑时入库字段
        $field = [
            'title'=>$title,
            'path'=>$path,
            'componentName'=>trim($this->request->input('componentName', '')),
            'componentPath'=>trim($this->request->input('componentPath', '')),
            'isCache'=>intval($this->request->input('isCache', 0)),
            'icon'=>trim($this->request->input('icon', '')),
            'sort'=>intval($this->request->input('sort', 1)),
            'isShow'=>intval($this->request->input('isShow', 0))
        ];
        if ($id <= 0) {
            $field['parentId'] = $parentId;
            $field['type'] = $type;
        }

        return ['code'=>0, 'field'=>$field];
    }

    /**
    * 编辑菜单
    */
    public function edit()
    {
        // 菜单ID
        $id = intval($this->request->input('id', 0));
        if ($id <= 0) {
            return response()->json($this->fail('无效参数'));
        }

        // 初始化，获取基本数据
        $init = $this->request->input('init', false);
        if ($init === true) {
            $info = DB::table('admin_menus')->where('id', $id)->first();
            if (! $info) {
                return response()->json($this->fail('菜单不存在'));
            }

            $parentIds = $this->_getParentIds($info->parentId);
            sort($parentIds);

            $data = [
                'menu'=>$this->_getMenus(),
                'parentIds'=>$parentIds,
                'info'=>$info
            ];
            return response()->json($this->success($data));
        }

        // 基础验证
        $check = $this->_menuCheck($id);
        if ($check['code'] === 1) {
            return response()->json($this->fail($check['msg']));
        }

        if (DB::table('admin_menus')->where('id', $id)->update($check['field']) === false) {
            return response()->json($this->fail('编辑失败'));
        }

        $this->recordLog('编辑菜单，id='.$id);

        return response()->json($this->success([], '编辑成功'));
    }

    /**
    * 添加权限
    */
    public function addPermission()
    {
        // 初始化，获取所有显示菜单
        $init = $this->request->input('init', false);
        if ($init === true) {
            $data['menu'] = $this->_getMenus();
            return response()->json($this->success($data));
        }

        // 基础验证
        $check = $this->_permissionCheck();
        if ($check['code'] === 1) {
            return response()->json($this->fail($check['msg']));
        }

        if (DB::table('admin_menus')->insertGetId($check['field']) <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        $this->recordLog('添加权限：'.json_encode($check['field']));

        return response()->json($this->success([], '添加成功'));
    }

    /**
    * 编辑权限
    */
    public function editPermission()
    {
        // 权限ID
        $id = intval($this->request->input('id', 0));
        if ($id <= 0) {
            return response()->json($this->fail('无效参数'));
        }

        // 初始化，获取基本数据
        $init = $this->request->input('init', false);
        if ($init === true) {
            $info = DB::table('admin_menus')->where('id', $id)->select('id','parentId','title','path','sort')->first();
            if (! $info) {
                return response()->json($this->fail('权限不存在'));
            }

            $parentIds = $this->_getParentIds($info->parentId);
            sort($parentIds);

            $data = [
                'menu'=>$this->_getMenus(),
                'parentIds'=>$parentIds,
                'info'=>$info
            ];
            return response()->json($this->success($data));
        }

        // 基础验证
        $check = $this->_permissionCheck($id);
        if ($check['code'] === 1) {
            return response()->json($this->fail($check['msg']));
        }

        if (DB::table('admin_menus')->where('id', $id)->update($check['field']) === false) {
            return response()->json($this->fail('编辑失败'));
        }

        $this->recordLog('编辑权限，id='.$id);

        return response()->json($this->success([], '编辑成功'));
    }

    /**
    * 获取所有父级ID
    * @param int $parentI
    * @param array $ids
    * @return array
    */
    private function _getParentIds($parentId = 0, &$ids = [])
    {
        if ($parentId <= 0) {
            return $ids;
        }

        $ids[] = $parentId;

        $data = DB::table('admin_menus')->where('id', $parentId)->select('parentId')->get()->toArray();
        if ($data) {
            foreach ($data as $v) {
                $this->_getParentIds($v->parentId, $ids);
            }
        }

        return $ids;
    }

    /**
    * 添加、编辑权限公共验证
    * @param int $id
    * @return array
    */
    private function _permissionCheck($id = 0)
    {
        $ret = ['code'=>1, 'msg'=>'未知错误'];

        // 公用的条件判断
        $idWhere = [];
        // 编辑
        if ($id > 0) {
            // 权限是否存在
            if (! DB::table('admin_menus')->where('id', $id)->exists()) {
                $ret['msg'] = '该权限不存在';
                return $ret;
            }

            $idWhere = ['id', '<>', $id];
        }

        // 上级ID
        $parentId = intval($this->request->input('parentId', 0));
        if (! DB::table('admin_menus')->where('id', $parentId)->exists()) {
            $ret['msg'] = '上级ID不存在';
            return $ret;
        }

        // 权限名称
        $title = trim($this->request->input('title', ''));
        if (! $title) {
            $ret['msg'] = '请输入权限名称';
            return $ret;
        }

        // 权限标识
        $path = trim($this->request->input('path', ''));
        if (! $path) {
            $ret['msg'] = '请输入权限标识';
            return $ret;
        }

        // 1、同级下权限名称不能重复
        $where = [
            ['parentId', '=', $parentId],
            ['title', '=', $title]
        ];
        if ($id > 0) {
            $where[] = $idWhere;
        }
        if (DB::table('admin_menus')->where($where)->exists()) {
            $ret['msg'] = '同级下该权限名称已存在';
            return $ret;
        }

        // 2、权限标识唯一性校验
        $where = [
            ['path', '=', $path]
        ];
        if ($id > 0) {
            $where[] = $idWhere;
        }
        if (DB::table('admin_menus')->where($where)->exists()) {
            $ret['msg'] = '该权限标识已存在';
            return $ret;
        }

        // 编辑时入库字段
        $field = [
            'title'=>$title,
            'path'=>$path,
            'type'=>3,
            'sort'=>intval($this->request->input('sort', 1))
        ];
        if ($id <= 0) {
            $field['parentId'] = $parentId;
        }

        return ['code'=>0, 'field'=>$field];
    }

    /**
     * 获取所有菜单
     * @param int $parentId
     * @param bool $isDisplayMenu 是否获取的是显示菜单
     * @return array
     * */
    private function _getMenus($parentId = 0)
    {
        $where = [
            ['parentId', '=', $parentId],
            ['type', '=', 1]
        ];
        $data = DB::table('admin_menus')->where($where)->select('id','title')->orderBy('sort')->get()->toArray();
        if ($data) {
            foreach ($data as $k => $v) {
                // 是否还有下级
                $where = [
                    ['parentId', '=', $v->id],
                    ['type', '=', 1]
                ];
                if (DB::table('admin_menus')->where($where)->exists()) {
                    $data[$k]->children = $this->_getMenus($v->id);
                }
            }
        }
        return $data;
    }

    /**
     * 获取所有下级菜单或权限的ID
     * @param int $parentId
     * @param array $ids
     * @return array
     * */
    private function _getChildrenMenuId($parentId = 0, &$ids = [])
    {
        $data = DB::table('admin_menus')->where('parentId', $parentId)->select('id')->get()->toArray();
        if ($data) {
            foreach ($data as $v) {
                $ids[] = $v->id;
                $this->_getChildrenMenuId($v->id, $ids);
            }
        }

        return $ids;
    }

    /**
    * 删除
    * 同步删除下级所有菜单或权限
    */
    public function remove()
    {
        // 菜单或权限ID
        $id = intval($this->request->input('id', 0));

        $title = DB::table('admin_menus')->where('id', $id)->value('title');
        if (! $title) {
            return response()->json($this->fail('该菜单或权限不存在'));
        }

        // 所有下级ID
        $ids = $this->_getChildrenMenuId($id);
        $ids[] = $id;

        if (! DB::table('admin_menus')->whereIn('id', $ids)->delete()) {
            return response()->json($this->fail('删除失败'));
        }

        $this->recordLog('删除菜单或权限：'.$title.'，累计删除：'.count($ids).'个');

        return response()->json($this->success([], '删除成功'));
    }
}