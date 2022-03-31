<?php
/**
 * 菜单管理
 * 2021.7.24
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
    * 菜单列表
    */
    public function list()
    {
    	$page = $this->request->input('page', 1);
        $size = $this->request->input('size', 10);
        $offset = (($page * $size) - $size);

        $list = DB::table('admin_menus')
          ->where('parent_id', 0)
          ->offset($offset)
          ->limit($size)
          ->orderBy('sort')
        ->get()->toArray();
        $count = DB::table('admin_menus')->where('parent_id', 0)->count();
        if ($list) {
            foreach ($list as $k => $v) {
                //判断是否还有下级
                $second = DB::table('admin_menus')->where('parent_id', $v->id)->orderBy('sort')->get()->toArray();
                if ($second) {
                    //第三级仅为注册路由
                    foreach ($second as $k1 => $v1) {
                        if ($v1->menu_type > 1) {
                            $second[$k1]->parents = [$v->id];
                        }
                        $third = DB::table('admin_menus')->where('parent_id', $v1->id)->orderBy('sort')->get()->toArray();
                        if ($third) {
                            foreach ($third as $k2 => $v2) {
                                //编辑注册路由、权限时使用
                                $third[$k2]->parents = [$v->id, $v1->id];
                            }
                            $second[$k1]->children = $third;
                        }
                    }
                    $list[$k]->children = $second;
                }
            }
        }

        // 所有一级菜单
        $first_level_menu = [];
        if ($page == 1) {
            $first_level_menu = $this->_getFirstLevelMenu();
        }

    	return response()->json($this->success(['first_level_menu'=>$first_level_menu, 'list'=>$list, 'count'=>$count]));
    }

    /**
    * 获取所有一级菜单
    * @return json
    */
    private function _getFirstLevelMenu() {
        return DB::table('admin_menus')->where('parent_id', 0)->select('id','title')->orderBy('sort')->get();
    }

    /**
    * 添加菜单
    */
    public function add()
    {
        $parent_id = intval($this->request->input('parent_id', 0));

        $title = trim($this->request->input('title', ''));
        if (! $title) {
            return response()->json($this->fail('请输入菜单名称'));
        }

        $path = trim($this->request->input('path', ''));

        //共同验证
        $where = [
            ['parent_id', '=', $parent_id],
            ['title', '=', $title]
        ];
        if (DB::table('admin_menus')->where($where)->exists()) {
            return response()->json($this->fail('同一上级菜单下，该菜单名称已存在'));
        }

        //添加二级菜单验证
        if ($parent_id > 0) {
            if (! $path) {
                return response()->json($this->fail('请输入页面路径'));
            }

            if (DB::table('admin_menus')->where('path', $path)->exists()) {
                return response()->json($this->fail('该页面路径已存在'));
            }
        }

        $field = [
            'parent_id'=>$parent_id,
            'title'=>$title,
            'path'=>$path,
            'component_name'=>trim($this->request->input('component_name', '')),
            'component_path'=>trim($this->request->input('component_path', '')),
            'icon'=>trim($this->request->input('icon', '')),
            'sort'=>intval($this->request->input('sort', 1)),
            'is_show'=>intval($this->request->input('is_show', 0)),
            'is_cache'=>intval($this->request->input('is_cache', 0))
        ];
        $insertId = DB::table('admin_menus')->insertGetId($field);
        if ($insertId <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        $this->recordLog('添加菜单：'.$title.'，id='.$insertId);

        return response()->json($this->success([], '添加成功'));
    }

    /**
    * 编辑菜单
    */
    public function edit()
    {
        $id = intval($this->request->input('id', 0));
        $info = DB::table('admin_menus')->where('id', $id)->select('parent_id')->first();
        if (! $info) {
            return response()->json($this->fail('菜单数据未找到'));
        }

        $title = trim($this->request->input('title', ''));
        if (! $title) {
            return response()->json($this->fail('请输入菜单名称'));
        }

        $path = trim($this->request->input('path', ''));

        //共同验证
        $where = [
            ['id', '<>', $id],
            ['parent_id', '=', $info->parent_id],
            ['title', '=', $title]
        ];
        if (DB::table('admin_menus')->where($where)->exists()) {
            return response()->json($this->fail('同一上级菜单下，该菜单名称已存在'));
        }

        //添加二级菜单验证
        if ($info->parent_id > 0) {
            if (! $path) {
                return response()->json($this->fail('请输入页面路径'));
            }

            $where = [
                ['id', '<>', $id],
                ['path', '=', $path]
            ];
            if (DB::table('admin_menus')->where($where)->exists()) {
                return response()->json($this->fail('该页面路径已存在'));
            }
        }

        $field = [
            'title'=>$title,
            'path'=>$path,
            'component_name'=>trim($this->request->input('component_name', '')),
            'component_path'=>trim($this->request->input('component_path', '')),
            'icon'=>trim($this->request->input('icon', '')),
            'sort'=>intval($this->request->input('sort', 1)),
            'is_show'=>intval($this->request->input('is_show', 0)),
            'is_cache'=>intval($this->request->input('is_cache', 0))
        ];
        if (DB::table('admin_menus')->where('id', $id)->update($field) === FALSE) {
            return response()->json($this->fail('编辑失败'));
        }

        $this->recordLog('编辑菜单，id='.$id);

        return response()->json($this->success([], '编辑成功'));
    }

    /**
    * 删除菜单
    */
    public function remove()
    {
        // ID
        $id = intval($this->request->input('id', 0));
        if ($id <= 0) {
        	return response()->json($this->fail('参数有误'));
        }

        $menu = DB::table('admin_menus')->where('id', $id)->select('title')->first();
        if (! $menu) {
            return response()->json($this->fail('该菜单不存在'));
        }

        // 二级菜单、权限、按钮级菜单存在则删除
        $ids = $title = [];
        $second = DB::table('admin_menus')->where('parent_id', $id)->select('id','title')->get()->toArray();
        if ($second) {
            foreach ($second as $v1) {
                $ids[] = $v1->id;
                $title[] = $v1->title;
                //第三级
                $third = DB::table('admin_menus')->where('parent_id', $v1->id)->select('id','title')->get()->toArray();
                if ($third) {
                    foreach ($third as $v2) {
                        $ids[] = $v2->id;
                        $title[] = $v2->title;
                    }
                }
            }
        }
        $ids[] = $id;
        $title[] = $menu->title;

        DB::beginTransaction();
        try {
            DB::table('admin_menus')->whereIn('id', $ids)->delete();
            //同步删除权限
            DB::table('admin_role_permissions')->whereIn('menu_id', $ids)->delete();
            $this->recordLog('删除菜单或权限：'.implode('|', $title));

            DB::commit();
            return response()->json($this->success([], '删除成功'));
        } catch (\Exception $e) {
        	DB::rollBack();
            return response()->json($this->fail($this->errMessage));
        }

        return response()->json($this->fail('删除失败'));
    }

    /**
    * 添加页面按钮级菜单
    */
    public function addRegisterRoute()
    {
        //父级ID
        $parents = $this->request->input('parents', []);
        $count = count($parents);
        if ($count <= 0) {
            return response()->json($this->fail('参数有误'));
        }

        //默认
        $parent_id = $parents[0];
        if ($count == 2) {
            $parent_id = $parents[1];
        }
        $where = [
            ['id','=',$parent_id]
        ];
        //验证菜单有效性
        if (! DB::table('admin_menus')->where($where)->exists()) {
            return response()->json($this->fail('上级菜单不存在'));
        }

        // 页面路径
        $field['path'] = trim($this->request->input('path', ''));
        if (! $field['path']) {
            return response()->json($this->fail('请输入页面路径'));
        }
        if (DB::table('admin_menus')->where('path', $field['path'])->exists()) {
            return response()->json($this->fail('该菜单已存在'));
        }

        $field['parent_id'] = $parent_id;
        $field['menu_type'] = 2;
        $field['is_show'] = 1;
        $field['is_cache'] = intval($this->request->input('is_cache', 0));

        // 路由名称
        $field['title'] = trim($this->request->input('title', ''));
        if (! $field['title']) {
            return response()->json($this->fail('请输入路由名称'));
        }

        // 组件名称
        $field['component_name'] = trim($this->request->input('component_name', ''));
        if (! $field['component_name']) {
            return response()->json($this->fail('请输入组件名称'));
        }

        // 组件路径
        $field['component_path'] = trim($this->request->input('component_path', ''));
        if (! $field['component_path']) {
            return response()->json($this->fail('请输入组件路径'));
        }

        $insertId = DB::table('admin_menus')->insertGetId($field);
        if ($insertId <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        $this->recordLog('添加页面按钮级菜单：'.$field['title'].'，id='.$insertId);

        return response()->json($this->success([], '添加成功'));
    }

    /**
    * 编辑页面按钮级菜单
    */
    public function editRegisterRoute()
    {
        $id = intval($this->request->input('id', 0));

        //父级ID
        $parents = $this->request->input('parents', []);
        $count = count($parents);
        if ($count <= 0) {
            return response()->json($this->fail('参数有误'));
        }

        //默认
        $parent_id = $parents[0];
        if ($count == 2) {
            $parent_id = $parents[1];
        }
        $where = [
            ['id','=',$parent_id]
        ];
        //验证菜单有效性
        if (! DB::table('admin_menus')->where($where)->exists()) {
            return response()->json($this->fail('上级菜单不存在'));
        }

        // 路由名称
        $field['title'] = trim($this->request->input('title', ''));
        if (! $field['title']) {
            return response()->json($this->fail('请输入路由名称'));
        }

        // 页面路径
        $field['path'] = trim($this->request->input('path', ''));
        if (! $field['path']) {
            return response()->json($this->fail('请输入页面路径'));
        }
        $where = [
            ['id','<>',$id],
            ['path','=',$field['path']]
        ];
        if (DB::table('admin_menus')->where($where)->exists()) {
            return response()->json($this->fail('该菜单已存在'));
        }

        $field['parent_id'] = $parent_id;
        $field['is_cache'] = intval($this->request->input('is_cache', 0));

        // 组件名称
        $field['component_name'] = trim($this->request->input('component_name', ''));
        if (! $field['component_name']) {
            return response()->json($this->fail('请输入组件名称'));
        }

        // 组件路径
        $field['component_path'] = trim($this->request->input('component_path', ''));
        if (! $field['component_path']) {
            return response()->json($this->fail('请输入组件路径'));
        }

        if (DB::table('admin_menus')->where('id', $id)->update($field) === FALSE) {
            return response()->json($this->fail('编辑失败'));
        }

        $this->recordLog('编辑页面按钮级菜单，id='.$id);

        return response()->json($this->success([], '编辑成功'));
    }

    /**
    * 添加权限
    */
    public function addPermission()
    {
        //父级ID
        $parents = $this->request->input('parents', []);
        $count = count($parents);
        if ($count <= 0) {
            return response()->json($this->fail('参数有误'));
        }

        //默认上级
        $parent_id = $parents[0];
        if ($count == 2) {
            $parent_id = $parents[1];
        }
        //验证上级菜单有效性
        if (! DB::table('admin_menus')->where('id', $parent_id)->exists()) {
            return response()->json($this->fail('上级菜单不存在'));
        }

        $field['parent_id'] = $parent_id;
        $field['menu_type'] = 3;
        $field['sort'] = intval($this->request->input('sort', 1));

        //权限名称
        $field['title'] = trim($this->request->input('title', ''));
        if (! $field['title']) {
            return response()->json($this->fail('请输入权限名称'));
        }

        //权限标识
        $field['path'] = trim($this->request->input('path', ''));
        if (! $field['path']) {
            return response()->json($this->fail('请输入权限名称'));
        }
        if (DB::table('admin_menus')->where('path', $field['path'])->exists()) {
            return response()->json($this->fail('该权限已存在'));
        }

        $insertId = DB::table('admin_menus')->insertGetId($field);
        if ($insertId <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        $this->recordLog('添加权限：'.$field['title'].'，id='.$insertId);

        return response()->json($this->success([], '添加成功'));
    }

    /**
    * 编辑权限
    */
    public function editPermission()
    {
        $id = intval($this->request->input('id', 0));

        //父级ID
        $parents = $this->request->input('parents', []);
        $count = count($parents);
        if ($count <= 0) {
            return response()->json($this->fail('参数有误'));
        }

        //默认上级
        $parent_id = $parents[0];
        if ($count == 2) {
            $parent_id = $parents[1];
        }
        $where = [
            ['id','=',$parent_id]
        ];
        //验证上级菜单有效性
        if (! DB::table('admin_menus')->where($where)->exists()) {
            return response()->json($this->fail('上级菜单不存在'));
        }

        $field['parent_id'] = $parent_id;
        $field['sort'] = intval($this->request->input('sort', 1));

        //权限名称
        $field['title'] = trim($this->request->input('title', ''));
        if (! $field['title']) {
            return response()->json($this->fail('请输入权限名称'));
        }

        //权限标识
        $field['path'] = trim($this->request->input('path', ''));
        if (! $field['path']) {
            return response()->json($this->fail('请输入权限名称'));
        }
        $where = [
            ['id','<>',$id],
            ['path','=',$field['path']]
        ];
        if (DB::table('admin_menus')->where($where)->exists()) {
            return response()->json($this->fail('该权限已存在'));
        }

        if (DB::table('admin_menus')->where('id', $id)->update($field) === FALSE) {
            return response()->json($this->fail('编辑失败'));
        }

        $this->recordLog('编辑权限，id='.$id);

        return response()->json($this->success([], '编辑成功'));
    }

    /**
    * 权限、按钮级菜单操作前格式化菜单
    */
    public function getTwoLevelMenu()
    {
        $list = [];

        $q = DB::table('admin_menus')->where('parent_id', 0)->select('id','title')->orderBy('sort')->get()->toArray();
        if ($q) {
            foreach ($q as $k1 => $v1) {
                $list[$k1]['label'] = $v1->title;
                $list[$k1]['value'] = $v1->id;

                $where = [
                    ['menu_type','=',1],
                    ['parent_id','=',$v1->id]
                ];
                $child = DB::table('admin_menus')->where($where)->select('id','title')->orderBy('sort')->get()->toArray();
                if ($child) {
                    $c = [];
                    foreach ($child as $k2 => $v2) {
                        $c[$k2]['label'] = $v2->title;
                        $c[$k2]['value'] = $v2->id;
                    }
                    $list[$k1]['children'] = $c;
                }
            }
        }

        return response()->json($this->success(['list'=>$list]));
    }
}
