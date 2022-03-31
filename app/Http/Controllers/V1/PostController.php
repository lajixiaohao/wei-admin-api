<?php
/**
 * 岗位管理
 * 2021.8.24
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
    * 列表数据
    */
    public function list()
    {
        $page = $this->request->input('page', 1);
        $size = $this->request->input('size', 10);
        $offset = (($page * $size) - $size);

        $where = [];

        //岗位搜索
        $name = trim($this->request->input('name', ''));
        if ($name) {
            $where[] = ['name', 'like', '%'.$name.'%'];
        }

        $list = DB::table('admin_posts')
          ->where($where)
          ->offset($offset)
          ->limit($size)
          ->orderBy('sort')
          ->get()->toArray();
        $count = DB::table('admin_posts')
          ->where($where)
        ->count();
        
        return response()->json($this->success(['list'=>$list, 'count'=>$count]));
    }

    /**
    * 添加
    */
    public function add()
    {
        $name = trim($this->request->input('name', ''));
        if (! $name) {
            return response()->json($this->fail('请输入岗位名称'));
        }

        if (DB::table('admin_posts')->where('name', $name)->exists()) {
            return response()->json($this->fail('该岗位已存在'));
        }

        $field = [
            'name'=>$name,
            'sort'=>intval($this->request->input('sort', 1)),
            'is_able'=>intval($this->request->input('is_able', 1))
        ];
        $field['created_at'] = $field['updated_at'] = date('Y-m-d H:i:s');
        $insertId = DB::table('admin_posts')->insertGetId($field);
        if ($insertId <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        $this->recordLog('添加岗位：'.$name);

        return response()->json($this->success([], '添加成功'));
    }

    /**
    * 编辑
    */
    public function edit()
    {
        $id = intval($this->request->input('id', 0));

        $name = trim($this->request->input('name', ''));
        if (! $name) {
            return response()->json($this->fail('请输入岗位名称'));
        }

        $where = [
            ['id', '<>', $id],
            ['name', '=', $name]
        ];
        if (DB::table('admin_posts')->where($where)->exists()) {
            return response()->json($this->fail('该岗位已存在'));
        }

        $field = [
            'name'=>$name,
            'sort'=>intval($this->request->input('sort', 1)),
            'is_able'=>intval($this->request->input('is_able', 1)),
            'updated_at'=>date('Y-m-d H:i:s')
        ];
        if (DB::table('admin_posts')->where('id', $id)->update($field) === FALSE) {
            return response()->json($this->fail('编辑失败'));
        }

        $this->recordLog('编辑岗位：，id='.$id);

        return response()->json($this->success([], '编辑成功'));
    }

    /**
    * 删除
    */
    public function remove()
    {
        $id = intval($this->request->input('id', 0));

        $data = DB::table('admin_posts')->where('id', $id)->select('name')->first();
        if (! $data) {
            return response()->json($this->fail('该岗位不存在'));
        }

        DB::beginTransaction();
        try {
            DB::table('admin_posts')->where('id', $id)->delete();
            DB::table('admin_users')->where('post_id', $id)->update(['post_id'=>0, 'updated_at'=>date('Y-m-d H:i:s')]);
            //写入日志
            $this->recordLog('删除岗位：'.$data->name);

            DB::commit();
            return response()->json($this->success([], '删除成功'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($this->fail($this->errMessage.'('.__LINE__.')'));
        }

        return response()->json($this->fail('删除失败'));
    }
}