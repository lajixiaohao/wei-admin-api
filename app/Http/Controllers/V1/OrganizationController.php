<?php
/**
 * 组织机构管理
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationController extends Controller
{
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
    * 列表
    */
    public function list()
    {
    	$page = $this->request->input('page', 1);
        $size = $this->request->input('size', 10);
        $offset = ($page * $size) - $size;

        $where = [
            ['is_deleted', '=', 0]
        ];

        // 名称搜索
        $name = trim($this->request->input('name', ''));
        if ($name) {
            $where[] = ['name', 'like', '%'.$name.'%'];
        }

        $data['list'] = DB::table('admin_organizations')
          ->where($where)
          ->offset($offset)
          ->limit($size)
          ->orderBy('id', 'desc')
          ->get();
        $data['count'] = DB::table('admin_organizations')->where($where)->count();
        
        return response()->json($this->success($data));
    }

    /**
    * 添加
    */
    public function add()
    {
    	// 组织名称
    	$name = trim($this->request->input('name', ''));
    	if (! $name) {
    		return response()->json($this->fail('请输入组织机构名称'));
    	}

    	// 组织重复验证
    	$where = [
    		['name', '=', $name],
    		['is_deleted', '=', 0]
    	];
    	if (DB::table('admin_organizations')->where($where)->exists()) {
    		return response()->json($this->fail('该组织机构已存在'));
    	}

    	$field = [
    		'name'=>$name,
    		'abbreviation'=>trim($this->request->input('abbreviation', '')),
    		'introduction'=>trim($this->request->input('introduction', '')),
    		'logo'=>trim($this->request->input('logo', '')),
    		'is_able'=>intval($this->request->input('is_able', 1))
    	];
    	$field['created_at'] = $field['updated_at'] = date('Y-m-d H:i:s');
        $insertId = DB::table('admin_organizations')->insertGetId($field);
        if ($insertId <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        $this->recordLog('添加组织机构：'.$name.'，id='.$insertId);

        return response()->json($this->success([], '添加成功'));
    }

    /**
    * 编辑
    */
    public function edit()
    {
    	// 组织id
    	$id = intval($this->request->input('id', 0));
    	if ($id <= 0) {
    		return response()->json($this->fail('缺少组织机构参数'));
    	}

    	// 组织名称
    	$name = trim($this->request->input('name', ''));
    	if (! $name) {
    		return response()->json($this->fail('请输入组织机构名称'));
    	}

    	// 组织重复验证
    	$where = [
    		['id', '<>', $id],
    		['name', '=', $name],
    		['is_deleted', '=', 0]
    	];
    	if (DB::table('admin_organizations')->where($where)->exists()) {
    		return response()->json($this->fail('该组织机构已存在'));
    	}

    	$field = [
    		'name'=>$name,
    		'abbreviation'=>trim($this->request->input('abbreviation', '')),
    		'introduction'=>trim($this->request->input('introduction', '')),
    		'logo'=>trim($this->request->input('logo', '')),
    		'is_able'=>intval($this->request->input('is_able', 1)),
    		'updated_at'=>date('Y-m-d H:i:s')
    	];
    	if (DB::table('admin_organizations')->where('id', $id)->update($field) === FALSE) {
            return response()->json($this->fail('编辑失败'));
        }

        $this->recordLog('编辑组织机构,id='.$id);

        return response()->json($this->success([], '编辑成功'));
    }

    /**
    * 删除
    */
    public function remove()
    {
    	// 组织id
        $id = intval($this->request->input('id', 0));

        // 组织信息
        $where = [
    		['id', '=', $id],
    		['is_deleted', '=', 0]
    	];
        $data = DB::table('admin_organizations')->where($where)->select('name')->first();
        if (! $data) {
            return response()->json($this->fail('该组织机构不存在'));
        }

        // 软删除组织
        if (DB::table('admin_organizations')->where('id', $id)->update(['is_able'=>0, 'is_deleted'=>1, 'updated_at'=>date('Y-m-d H:i:s')]) === false) {
        	return response()->json($this->fail('删除失败'));
        }

        $this->recordLog('删除岗位：'.$data->name);

        return response()->json($this->success([], '删除成功'));
    }
}