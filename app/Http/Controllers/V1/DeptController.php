<?php
/**
 * 部门管理
 * 2022.4.15
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeptController extends Controller
{
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
    * 获取部门数据
    * 主要以树形展开
    */
    public function index()
    {
    	$parentId = intval($this->request->input('parentId', 0));

        $depts = DB::table('sys_depts')
            ->where('parentId', $parentId)
            ->select('id', 'deptName')
            ->orderBy('sort')
            ->get()
            ->toArray();
        // 对下一级判断
        if ($depts) {
            foreach ($depts as $k => $v) {
                //默认为叶子节点
                $depts[$k]->leaf = true;
                //直属部门数
                $depts[$k]->cnum = 0;
                $count = DB::table('sys_depts')->where('parentId', $v->id)->count();
                if ($count) {
                    $depts[$k]->leaf = false;
                    // 根部门不显示数量
                    if ($v->id > 1) {
                    	$depts[$k]->cnum = $count;
                    }
                }
            }
        }

        // 部门总数，只在初始化时获取
        $deptNum = 0;
        if ($parentId <= 0) {
        	$deptNum = DB::table('sys_depts')->where([['parentId', '>', 0]])->count();
        }
        $data = [
        	'dept'=>$depts,
        	'deptNum'=>$deptNum
        ];

    	return response()->json($this->success($data));
    }
}