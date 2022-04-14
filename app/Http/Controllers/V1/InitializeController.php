<?php
/**
 * 初始化获取菜单和账户
 * 2022.4.14
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InitializeController extends Controller
{
    public function __construct(Request $request) {
        $this->request = $request;
    }

    //需要取出的字段
    // private $field = ['id','title','path','component_name','component_path','is_cache','icon'];

    /**
    * 初始化信息
    */
    public function index()
    {
        // token验证通过，断定管理员存在
        $data = DB::table('sys_administrators')->where('id', $this->request->adminId)->select('account', 'trueName')->first();
        $account = $data->trueName ? $data->trueName : $data->account;

        return response()->json($this->success(['menus'=>$this->_getRoleMenu(), 'account'=>$account]));
    }

    /**
    * 获取角色菜单
    */
    private function _getRoleMenu()
    {
        // 菜单、非超管菜单id
        $data = $ids = [];

        $where = [
            ['parentId', '=', 0],
            ['isShow', '=', 1],
            ['type', '<>', 3]
        ];

        if ($this->request->roleId === 1) {
            $data = DB::table('sys_menus')->where($where)->orderBy('sort')->get()->toArray();
        } else {
            $menu = DB::table('sys_role_permissions')->where('roleId', $this->request->roleId)->select('menuId')->get()->toArray();
            if ($menu) {
                $ids = array_map(function ($item) {
                    return $item->menuId;
                }, $menu);
                $data = DB::table('sys_menus')->whereIn('id', $ids)->where($where)->orderBy('sort')->get()->toArray();
            }
        }

        if ($data) {
            foreach ($data as $k => $v) {
                $data[$k]->pageMenu = $this->_getPageMenu($v->id);
                $data[$k]->children = $this->_getChildMenu($v->id, $ids);
            }
        }

        return $data;
        /*$where = [
            ['parent_id','=',0],
            ['is_show','=',1],
            ['menu_type','=',1]
        ];

        //一级菜单+所属权限菜单ID
        $data = $ids = [];

        if ($this->request->roleId == 1) {
            $data = DB::table('admin_menus')->where($where)->select($this->field)->orderBy('sort')->get()->toArray();
        } else {
            //非超管
            $menu = DB::table('admin_role_permissions')->where('role_id', $this->request->roleId)->select('menu_id')->get()->toArray();
            if ($menu) {
                foreach ($menu as $v) {
                    $ids[] = $v->menu_id;
                }
                $data = DB::table('admin_menus')->whereIn('id', $ids)->where($where)->select($this->field)->orderBy('sort')->get()->toArray();
            }
        }

        if ($data) {
            foreach ($data as $k => $v) {
                $data[$k]->children = $this->_getSecondMenu($v->id, $ids);
            }
        }

        return $data;*/
    }

    /**
    * 获取下级菜单，允许无限级
    * @param int $parent_id
    * @param array $ids
    * @return array
    */
    private function _getChildMenu($parentId = 0, $ids = [])
    {
        $where = [
            ['parentId', '=', $parentId],
            ['isShow', '=', 1],
            ['type', '=', 1]
        ];
        $data = DB::table('sys_menus')->where($where)->orderBy('sort')->get()->toArray();
        if ($data) {
            foreach ($data as $k => $v) {
                if ($ids && ! in_array($v->id, $ids)) {
                    unset($data[$k]);
                    continue;
                }
                $data[$k]->pageMenu = $this->_getPageMenu($v->id);
                $data[$k]->children = $this->_getChildMenu($v->id, $ids);
            }
        }

        return array_values($data);
        /*$where = [
            ['parent_id','=',$parent_id],
            ['is_show','=',1],
            ['menu_type','=',1]
        ];
        $data = DB::table('admin_menus')->where($where)->select($this->field)->orderBy('sort')->get()->toArray();
        if ($data) {
            foreach ($data as $k => $v) {
                if ($ids && ! in_array($v->id, $ids)) {
                    unset($data[$k]);
                    continue;
                }
                //页面按钮级菜单
                $data[$k]->children = $this->_getPageMenu($v->id);
            }
        }

        return array_values($data);*/
    }

    /**
    * 获取页面按钮级菜单
    * @param int $parent_id
    * @return json
    */
    private function _getPageMenu($parentId = 0)
    {
        $where = [
            ['parentId', '=', $parentId],
            ['isShow', '=', 1],
            ['type', '=', 2]
        ];
        return DB::table('sys_menus')->where($where)->orderBy('sort')->get();
    }
}
