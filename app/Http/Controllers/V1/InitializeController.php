<?php
/**
 * 初始化
 * 2021.7.20
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
    private $field = ['id','title','path','component_name','component_path','is_cache','icon'];

    /**
    * 初始化信息
    */
    public function index()
    {
        $account = '';
        $data = DB::table('admin_users')->where('id', $this->request->adminId)->select('account', 'true_name')->first();
        if ($data) {
            $account = $data->true_name ? $data->true_name : $data->account;
        }

        return response()->json($this->success(['menus'=>$this->_getRoleMenu(), 'account'=>$account]));
    }

    /**
    * 获取角色菜单
    */
    private function _getRoleMenu()
    {
        $where = [
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

        return $data;
    }

    /**
    * 获取二级菜单
    * @param int $parent_id
    * @param array $ids
    * @return array
    */
    private function _getSecondMenu($parent_id = 0, $ids = [])
    {
        $where = [
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

        return array_values($data);
    }

    /**
    * 获取页面按钮级菜单
    * @param int $parent_id
    * @return json
    */
    private function _getPageMenu($parent_id = 0)
    {
        $where = [
            ['parent_id','=',$parent_id],
            ['is_show','=',1],
            ['menu_type','=',2]
        ];
        return DB::table('admin_menus')->where($where)->select($this->field)->orderBy('sort')->get();
    }
}
