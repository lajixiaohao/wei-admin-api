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

        return response()->json($this->success([], '添加成功'));
    }

    /**
     * 获取所有菜单
     * @param int $parentId
     * @param bool $isDisplayMenu 是否获取的是显示菜单
     * @return array
     * */
    private function _getMenus($parentId = 0, $isDisplayMenu = true, $level = 0)
    {
        $where = [
            ['parentId', '=', $parentId]
        ];
        if ($isDisplayMenu) {
            $where[] = ['type', '=', 1];
        } else {
            $where[] = ['type', '<>', 3];
        }

        $data = DB::table('sys_menus')->where($where)->select('id','title')->orderBy('sort')->get()->toArray();
        if ($data) {
            $level++;
            foreach ($data as $k => $v) {
                $data[$k]->level = $level;
                $data[$k]->children = $this->_getMenus($v->id, $isDisplayMenu, $level);
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