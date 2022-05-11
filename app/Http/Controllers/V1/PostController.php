<?php
/**
 * 岗位管理
 * 2022.5.11
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
        $offset = ($page * $size) - $size;

        $where = [];

        //岗位搜索
        $postName = trim($this->request->input('postName', ''));
        if ($postName) {
            $where[] = ['postName', 'like', '%'.$postName.'%'];
        }

        $data['list'] = DB::table('admin_posts')
          ->where($where)
          ->offset($offset)
          ->limit($size)
          ->orderBy('sort')
          ->get();
        $data['count'] = DB::table('admin_posts')->where($where)->count();
        
        return response()->json($this->success($data));
    }

    /**
    * 添加
    */
    public function add()
    {
        $postName = trim($this->request->input('postName', ''));
        if (! $postName) {
            return response()->json($this->fail('请输入岗位名称'));
        }

        if (DB::table('admin_posts')->where('postName', $postName)->exists()) {
            return response()->json($this->fail('该岗位已存在'));
        }

        $field = [
            'postName'=>$postName,
            'postIntroduce'=>trim($this->request->input('postIntroduce', '')),
            'sort'=>intval($this->request->input('sort', 1)),
            'isAble'=>intval($this->request->input('isAble', 1))
        ];
        $field['createdAt'] = $field['updatedAt'] = date('Y-m-d H:i:s');
        $insertId = DB::table('admin_posts')->insertGetId($field);
        if ($insertId <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        $this->recordLog('添加岗位：'.$postName);

        return response()->json($this->success([], '添加成功'));
    }

    /**
    * 编辑
    */
    public function edit()
    {
        $id = intval($this->request->input('id', 0));

        $postName = trim($this->request->input('postName', ''));
        if (! $postName) {
            return response()->json($this->fail('请输入岗位名称'));
        }

        $where = [
            ['id', '<>', $id],
            ['postName', '=', $postName]
        ];
        if (DB::table('admin_posts')->where($where)->exists()) {
            return response()->json($this->fail('该岗位已存在'));
        }

        $field = [
            'postName'=>$postName,
            'postIntroduce'=>trim($this->request->input('postIntroduce', '')),
            'sort'=>intval($this->request->input('sort', 1)),
            'isAble'=>intval($this->request->input('isAble', 1)),
            'updatedAt'=>date('Y-m-d H:i:s')
        ];
        if (DB::table('admin_posts')->where('id', $id)->update($field) === FALSE) {
            return response()->json($this->fail('编辑失败'));
        }

        $this->recordLog('编辑岗位id='.$id);

        return response()->json($this->success([], '编辑成功'));
    }

    /**
    * 删除
    */
    public function remove()
    {
        $id = intval($this->request->input('id', 0));

        $postName = DB::table('admin_posts')->where('id', $id)->value('postName');
        if (! $postName) {
            return response()->json($this->fail('该岗位不存在'));
        }

        if (! DB::table('admin_posts')->where('id', $id)->delete()) {
            return response()->json($this->fail('删除失败'));
        }

        $this->recordLog('删除岗位：'.$postName);

        return response()->json($this->success([], '删除成功'));
    }
}