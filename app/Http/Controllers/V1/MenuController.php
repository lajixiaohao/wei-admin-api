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
        // 父级菜单ID
    	$parentId = intval($this->request->input('parentId', 0));

        $data = DB::table('sys_menus')
            ->where('parentId', $parentId)
            ->orderBy('sort')
            ->get()
            ->toArray();
        // 对下一级判断
        if ($data) {
            foreach ($data as $k => $v) {
                // 默认为叶子节点，即没有下一级
                $data[$k]->leaf = true;
                if (DB::table('sys_menus')->where('parentId', $v->id)->count()) {
                    $data[$k]->leaf = false;
                }
            }
        }

    	return response()->json($this->success(['menu'=>$data]));
    }

    /**
    * 添加
    */
    public function add()
    {
        // 初始化，获取所有显示菜单
        $init = intval($this->request->input('init', 0));
        if ($init === 1) {
            $data = $this->_getMenus();
            return response()->json($this->success($data));
        }

        // 菜单类型
        $type = intval($this->request->input('type', 0));
        if (! in_array($type, [1, 2])) {
            return response()->json($this->fail('未知菜单类型'));
        }

        // 上级菜单
        $parentIds = $this->request->input('parentId', []);
        $parentIdsCount = count($parentIds);
        if ($parentIdsCount > 3) {
            return response()->json($this->fail('未知的上级菜单'));
        }

        // 上级菜单ID
        $parentId = $parentIdsCount > 0 ? $parentIds[$parentIdsCount - 1] : 0;

        // 顶级菜单不能为隐式菜单
        if ($parentIdsCount === 0 && $type === 2) {
            return response()->json($this->fail('顶级菜单不能为隐式菜单'));
        }

        // 显示菜单最多只支持到3级
        if ($parentIdsCount === 3 && $type === 1) {
            return response()->json($this->fail('显示菜单最多只支持到三级'));
        }

        // 菜单名称
        $title = trim($this->request->input('title', ''));
        if (! $title) {
            return response()->json($this->fail('请输入菜单名称'));
        }

        // 同级下菜单名称不能重复
        $where = [
            ['parentId', '=', $parentId],
            ['title', '=', $title]
        ];
        if (DB::table('sys_menus')->where($where)->exists()) {
            return response()->json($this->fail('同级下该菜单已存在'));
        }

        // 上级菜单有效性验证
        if ($parentId > 0 && ! DB::table('sys_menus')->where('id', $parentId)->exists()) {
            return response()->json($this->fail('上级菜单无效'));
        }

        $field = [
            'parentId'=>$parentId,
            'title'=>$title,
            'path'=>trim($this->request->input('path', '')),
            'componentName'=>trim($this->request->input('componentName', '')),
            'componentPath'=>trim($this->request->input('componentPath', '')),
            'isCache'=>intval($this->request->input('isCache', 0)),
            'icon'=>trim($this->request->input('icon', '')),
            'sort'=>intval($this->request->input('sort', 1)),
            'isShow'=>intval($this->request->input('isShow', 0)),
            'type'=>$type
        ];

        $insertId = DB::table('sys_menus')->insertGetId($field);
        if ($insertId <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        $this->recordLog('添加菜单：'.$title);

        return response()->json($this->success([], '添加成功'));
    }

    /**
    * 编辑
    * 编辑除父级ID、菜单类型以外的数据
    */
    public function edit()
    {
        // 菜单ID
        $id = intval($this->request->input('id', 0));
        if ($id <= 0) {
            return response()->json($this->fail('无效菜单'));
        }

        // 初始化，获取菜单基本数据
        $init = intval($this->request->input('init', 0));
        if ($init === 1) {
            $data = DB::table('sys_menus')->where('id', $id)->first();
            return response()->json($this->success($data));
        }

        // 父级ID
        $parentId = intval($this->request->input('parentId', 0));

        // 菜单名称
        $title = trim($this->request->input('title', ''));
        if (! $title) {
            return response()->json($this->fail('请输入菜单名称'));
        }

        // 同级下菜单名称不能重复
        $where = [
            ['id', '<>', $id],
            ['parentId', '=', $parentId],
            ['title', '=', $title]
        ];
        if (DB::table('sys_menus')->where($where)->exists()) {
            return response()->json($this->fail('同级下该菜单已存在'));
        }

        $field = [
            'title'=>$title,
            'path'=>trim($this->request->input('path', '')),
            'componentName'=>trim($this->request->input('componentName', '')),
            'componentPath'=>trim($this->request->input('componentPath', '')),
            'isCache'=>intval($this->request->input('isCache', 0)),
            'icon'=>trim($this->request->input('icon', '')),
            'sort'=>intval($this->request->input('sort', 1)),
            'isShow'=>intval($this->request->input('isShow', 0))
        ];
        if (DB::table('sys_menus')->where('id', $id)->update($field) === false) {
            return response()->json($this->fail('编辑失败'));
        }

        $this->recordLog('编辑菜单，ID='.$id);

        return response()->json($this->success([], '编辑成功'));
    }

    /**
    * 添加权限
    */
    public function addPermission()
    {
        // 初始化，获取所有显示菜单
        $init = intval($this->request->input('init', 0));
        if ($init === 1) {
            $data = $this->_getMenus();
            return response()->json($this->success($data));
        }
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
        $data = DB::table('sys_menus')->where($where)->select('id','title')->orderBy('sort')->get()->toArray();
        if ($data) {
            foreach ($data as $k => $v) {
                // 是否还有下级
                $where = [
                    ['parentId', '=', $v->id],
                    ['type', '=', 1]
                ];
                if (DB::table('sys_menus')->where($where)->exists()) {
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
        $data = DB::table('sys_menus')->where('parentId', $parentId)->select('id')->get()->toArray();
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

        $title = DB::table('sys_menus')->where('id', $id)->value('title');
        if (! $title) {
            return response()->json($this->fail('该菜单或权限不存在'));
        }

        // 所有下级ID
        $ids = $this->_getChildrenMenuId($id);
        $ids[] = $id;

        if (! DB::table('sys_menus')->whereIn('id', $ids)->delete()) {
            return response()->json($this->fail('删除失败'));
        }

        $this->recordLog('删除菜单或权限：'.$title.'，累计删除：'.count($ids).'个');

        return response()->json($this->success([], '删除成功'));
    }
}