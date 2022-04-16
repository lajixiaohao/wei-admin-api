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
        $ids = $this->_getChildrenDeptId(1);
        dump($ids);
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