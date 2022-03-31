<?php
/**
 * 部门管理
 * 2021.8.31
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
    * 树形数据
    */
    public function tree()
    {
        $parentId = intval($this->request->input('parent_id', 0));

        $where = [
            ['parent_id', '=', $parentId]
        ];
        if ($parentId <= 0) {
            $where = [
                ['id', '=', $this->request->departmentId]
            ];
        }

        $data = DB::table('admin_departments')
            ->where($where)
            ->select('id','name','sort','is_able')
            ->orderBy('sort')
            ->get()
            ->toArray();
        if ($data) {
            foreach ($data as $k => $v) {
                //默认为叶子节点
                $data[$k]->leaf = true;
                //直属部门数
                $data[$k]->cnum = 0;
                $count = DB::table('admin_departments')->where('parent_id', $v->id)->count();
                if ($count) {
                    $data[$k]->leaf = false;

                    if ($v->id > 1) {
                        $data[$k]->cnum = $count;
                    }
                }
            }
        }

        //部门数量
        $departmentNum = 0;
        if ($parentId <= 0) {
            $departmentNum = count($this->getDepartmentSubordinateId($this->request->departmentId));
        }

        return response()->json($this->success(['list'=>$data, 'departmentNum'=>$departmentNum, 'departmentId'=>$this->request->departmentId]));
    }

    /**
    * 添加
    */
    public function add()
    {
        $parentId = intval($this->request->input('parent_id', 0));
        if ($parentId <= 0) {
            return response()->json($this->fail('请选择上级部门'));
        }

        $name = trim($this->request->input('name', ''));
        if (! $name) {
            return response()->json($this->fail('请输入部门名称'));
        }

        //是否下级所属部门，含当前管理员所属部门
        $child = $this->getDepartmentSubordinateId($this->request->departmentId);
        if (! in_array($parentId, $child)) {
            $this->recordLog('非法操作！所选父级部门非下级所属parent_id:'.$parentId);
            return response()->json($this->fail('非法操作'));
        }

        $where = [
            ['parent_id','=',$parentId],
            ['name','=',$name]
        ];
        if (DB::table('admin_departments')->where($where)->exists()) {
            return response()->json($this->fail('同一上级下该部门已存在'));
        }

        $field = [
            'parent_id'=>$parentId,
            'name'=>$name,
            'sort'=>intval($this->request->input('sort', 1)),
            'is_able'=>intval($this->request->input('is_able', 1)),
        ];
        $field['created_at'] = $field['updated_at'] = date('Y-m-d H:i:s');
        $insertId = DB::table('admin_departments')->insertGetId($field);
        if ($insertId <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        $this->recordLog('添加部门：'.$name);

        return response()->json($this->success([], '添加成功'));
    }

    /**
    * 编辑
    */
    public function edit()
    {
        $parentId = intval($this->request->input('parent_id', 0));
        if ($parentId <= 0) {
            return response()->json($this->fail('未知的上级部门'));
        }

        $id = intval($this->request->input('id', 0));
        if ($id <= 0) {
            return response()->json($this->fail('未知部门'));
        }

        //不能修改自己的部门信息
        if ($id == $this->request->departmentId) {
            $this->recordLog('操作异常！试图更换所属部门');
            return response()->json($this->fail('无权操作'));
        }

        $name = trim($this->request->input('name', ''));
        if (! $name) {
            return response()->json($this->fail('请输入部门名称'));
        }

        //是否下级所属部门
        $child = $this->getDepartmentSubordinateId($this->request->departmentId);
        if (! in_array($id, $child)) {
            $this->recordLog('非法操作！编辑的部门非下级所属');
            return response()->json($this->fail('非法操作'));
        }

        $where = [
            ['id','<>',$id],
            ['parent_id','=',$parentId],
            ['name','=',$name]
        ];
        if (DB::table('admin_departments')->where($where)->exists()) {
            return response()->json($this->fail('同一上级下该部门已存在'));
        }

        $field = [
            'name'=>$name,
            'sort'=>intval($this->request->input('sort', 1)),
            'is_able'=>intval($this->request->input('is_able', 1)),
            'updated_at'=>date('Y-m-d H:i:s')
        ];
        if (DB::table('admin_departments')->where('id', $id)->update($field) === FALSE) {
            return response()->json($this->fail('编辑失败'));
        }

        $this->recordLog('编辑部门id:'.$id);

        return response()->json($this->success([], '编辑成功'));
    }

    /**
    * 删除
    */
    public function remove()
    {
        $id = intval($this->request->input('id', 0));
        if ($id <= 0) {
            return response()->json($this->fail('请选择要删除的部门'));
        }

        //禁止删除自己所属部门
        if ($id == $this->request->departmentId) {
            $this->recordLog('试图删除所属部门');
            return response()->json($this->fail('非法操作'));
        }

        $data = DB::table('admin_departments')->where('id', $id)->select('name')->first();
        if (! $data) {
            return response()->json($this->fail('该部门不存在'));
        }

        //删除的部门只能是下级所属部门
        $selfChild = $this->getDepartmentSubordinateId($this->request->departmentId);
        //安全起见，排除自身部门
        $self = [];
        foreach ($selfChild as $v) {
            if ($v != $this->request->departmentId) {
                $self[] = $v;
            }
        }

        if (! in_array($id, $self)) {
            $this->recordLog('试图删除非所属部门,id:'.$id);
            return response()->json($this->fail('非法操作'));
        }

        //需要递归删除子部门
        $ids = $this->getDepartmentSubordinateId($id);

        try {
            DB::beginTransaction();
            DB::table('admin_departments')->whereIn('id', $ids)->delete();
            //写入日志
            $this->recordLog('删除部门：'.$data->name.'，累计删除'.count($ids).'个部门');
            //部门数
            $departmentNum = count($this->getDepartmentSubordinateId($this->request->departmentId));

            DB::commit();
            return response()->json($this->success(['departmentNum'=>$departmentNum], '删除成功'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->fail($this->errMessage));
        }

        return response()->json($this->success([], '删除成功'));
    }

    /**
    * 递归获取下级
    */
    private function _getSubordinate($id = 0, $ids = [])
    {
        if ($id <= 0) {
            return $ids;
        }

        $ids[] = $id;

        $data = DB::table('admin_departments')->where([['parent_id','=',$id], ['is_able','=',1]])->select('id')->get()->toArray();
        if ($data) {
            foreach ($data as $v) {
                $ids = $this->_getSubordinate($v->id, $ids);
            }
        }

        return $ids;
    }
}