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
    * 部门树
    */
    public function tree()
    {
        $parentId = intval($this->request->input('parentId', 0));

        $depts = DB::table('admin_depts')
            ->where('parentId', $parentId)
            ->orderBy('sort')
            ->get()
            ->toArray();
        // 对下一级判断
        if ($depts) {
            foreach ($depts as $k => $v) {
                //默认为叶子节点，即没有下一级
                $depts[$k]->leaf = true;
                //直属部门数
                $depts[$k]->cnum = 0;
                $count = DB::table('admin_depts')->where('parentId', $v->id)->count();
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
        	$deptNum = DB::table('admin_depts')->count();
        }
        $data = [
        	'dept'=>$depts,
        	'deptNum'=>$deptNum
        ];

    	return response()->json($this->success($data));
    }

    /**
     * 获取下级所有部门的ID
     * @param int $parentId
     * @param array $ids
     * @return array
     * */
    private function _getChildrenDeptId($parentId = 0, &$ids = [])
    {
        $data = DB::table('admin_depts')->where('parentId', $parentId)->select('id')->get()->toArray();
        if ($data) {
            foreach ($data as $v) {
                $ids[] = $v->id;
                $this->_getChildrenDeptId($v->id, $ids);
            }
        }

        return $ids;
    }

    /**
     * 添加
     * */
    public function add()
    {
        // 部门名称
        $field['deptName'] = trim($this->request->input('deptName', ''));
        if (empty($field['deptName'])) {
            return response()->json($this->fail('请输入部门名称'));
        }

        // 上级ID
        $field['parentId'] = intval($this->request->input('parentId', 0));
        if ($field['parentId'] <= 0) {
            return response()->json($this->fail('请选择上级部门'));
        }

        // 同级部门名称不能重复
        $where = [
            ['parentId', '=', $field['parentId']],
            ['deptName', '=', $field['deptName']]
        ];
        if (DB::table('admin_depts')->where($where)->exists()) {
            return response()->json($this->fail('同级下部门名称不能重复'));
        }

        $field['sort'] = intval($this->request->input('sort', 1));
        $field['deptIntroduce'] = trim($this->request->input('deptIntroduce', ''));
        $field['createdAt'] = $field['updatedAt'] = date('Y-m-d H:i:s');

        $insertId = DB::table('admin_depts')->insertGetId($field);
        if ($insertId <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        $this->recordLog('添加部门：'.$field['deptName']);

        return response()->json($this->success([], '添加成功'));
    }

    /**
     * 编辑
     * */
    public function edit()
    {
        // 部门ID
        $id = intval($this->request->input('id', 0));

        // 部门名称
        $field['deptName'] = trim($this->request->input('deptName', ''));
        if (empty($field['deptName'])) {
            return response()->json($this->fail('请输入部门名称'));
        }

        // 上级ID
        $parentId = intval($this->request->input('parentId', 0));
        if ($parentId <= 0) {
            return response()->json($this->fail('请选择上级部门'));
        }

        // 部门是否存在
        if (! DB::table('admin_depts')->where('id', $id)->exists()) {
            return response()->json($this->fail('部门不存在'));
        }

        // 同级部门名称不能重复
        $where = [
            ['id', '<>', $id],
            ['parentId', '=', $parentId],
            ['deptName', '=', $field['deptName']]
        ];
        if (DB::table('admin_depts')->where($where)->exists()) {
            return response()->json($this->fail('同级下部门名称已存在'));
        }

        $field['sort'] = intval($this->request->input('sort', 1));
        $field['deptIntroduce'] = trim($this->request->input('deptIntroduce', ''));
        $field['updatedAt'] = date('Y-m-d H:i:s');

        if (! DB::table('admin_depts')->where('id', $id)->update($field)) {
            return response()->json($this->fail('编辑失败'));
        }

        $this->recordLog('编辑部门，id:'.$id);

        return response()->json($this->success([], '编辑成功'));
    }

    /**
     * 删除
     * */
    public function remove()
    {
        // 部门ID
        $id = intval($this->request->input('id', 0));
        if ($id <= 0) {
            return response()->json($this->fail('非法操作'));
        }

        // 部门有效性以及禁止删除根部门
        $where = [
            ['id', '=', $id],
            ['parentId', '>', 0]
        ];
        $deptName = DB::table('admin_depts')->where($where)->value('deptName');
        if (! $deptName) {
            return response()->json($this->fail('该部门不存在'));
        }

        // 用于同时删除所有下级部门
        $ids = $this->_getChildrenDeptId($id);
        // 包含当前要删除的部门ID
        $ids[] = $id;

        if (! DB::table('admin_depts')->whereIn('id', $ids)->delete()) {
            return response()->json($this->fail('删除失败'));
        }

        // 累计删除
        $deleteNum = count($ids);

        $this->recordLog('删除部门：'.$deptName.'，累计删除：'.$deleteNum.'个');

        return response()->json($this->success(['deleteNum'=>$deleteNum], '删除成功'));
    }
}