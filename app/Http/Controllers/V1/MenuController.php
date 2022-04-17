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
}