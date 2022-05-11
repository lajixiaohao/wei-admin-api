<?php
/**
 * 测试使用
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function index()
    {
        // $ids = $this->_getChildrenDeptId(1);
        // dump($ids);
        $page = $this->request->input('page', 1);
        $size = $this->request->input('size', 10);
        $offset = ($page * $size) - $size;

        // $where = [
        //     ['a.parentId','=',1]
        // ];

        // 关键字搜索
        // $orWhere = [];
        // $keyword = trim($this->request->input('keyword', ''));
        // if ($keyword) {
        //     $orWhere[] = ['a.account', 'like', '%'.$keyword.'%'];
        //     // $orWhere[] = ['a.trueName', 'like', '%'.$keyword.'%'];
        // }

        DB::table('admin_users as a')
          ->leftJoin('admin_roles as b', 'b.id', '=', 'a.roleId')
          ->leftJoin('admin_depts as c', 'c.id', '=', 'a.deptId')
          ->leftJoin('admin_posts as d', 'd.id', '=', 'a.postId')
          ->whereRaw('a.parentId = ? AND (a.account like ? OR a.trueName like ?)', [1, '%韦凤喜%', '%韦凤喜%'])
          // ->orWhere($orWhere)
          ->select('a.id','a.account','a.trueName','a.isAble','a.createdAt','b.roleName','c.deptName','d.postName')
          ->offset($offset)
          ->limit($size)
          ->orderBy('a.id', 'desc')
        ->dd();
    }

    private function _getChildrenDeptId($parentId = 0, &$ids = [])
    {
        $data = DB::table('sys_depts')->where('parentId', $parentId)->select('id')->get()->toArray();
        if ($data) {
            foreach ($data as $v) {
                $ids[] = $v->id;
                $this->_getChildrenDeptId($v->id, $ids);
            }
        }

        return $ids;
    }
}