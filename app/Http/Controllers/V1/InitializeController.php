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
     // 获取菜单需要取出的字段
    private $field = ['id','title','path','componentName','componentPath','isCache','icon'];

    public function __construct(Request $request) {
        $this->request = $request;
    }

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
    * @return array
    */
    private function _getRoleMenu()
    {
        // 菜单、非超管菜单id
        $data = $ids = [];

        $where = [
            ['parentId', '=', 0],
            ['isShow', '=', 1],
            ['type', '=', 1]
        ];

        if ($this->request->roleId === 1) {
            $data = DB::table('sys_menus')->where($where)->select($this->field)->orderBy('sort')->get()->toArray();
        } else {
            $menu = DB::table('sys_role_permissions')->where('roleId', $this->request->roleId)->select('menuId')->get()->toArray();
            if ($menu) {
                $ids = array_map(function ($item) {
                    return $item->menuId;
                }, $menu);
                $data = DB::table('sys_menus')->whereIn('id', $ids)->where($where)->select($this->field)->orderBy('sort')->get()->toArray();
            }
        }

        if ($data) {
            foreach ($data as $k => $v) {
                $data[$k]->pageMenu = $this->_getPageMenu($v->id);
                $data[$k]->children = $this->_getChildMenu($v->id, $ids);
            }
        }

        return $data;
    }

    /**
    * 获取下级菜单
    * @param int $parentId
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
        $data = DB::table('sys_menus')->select($this->field)->where($where)->orderBy('sort')->get()->toArray();
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
    }

    /**
    * 获取页面按钮级菜单
    * @param int $parentId
    * @return json
    */
    private function _getPageMenu($parentId = 0)
    {
        $where = [
            ['parentId', '=', $parentId],
            ['isShow', '=', 1],
            ['type', '=', 2]
        ];
        return DB::table('sys_menus')->where($where)->select($this->field)->orderBy('sort')->get();
    }
}
