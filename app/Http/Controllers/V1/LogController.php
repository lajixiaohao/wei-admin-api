<?php
/**
 * 日志管理
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
    * 操作日志列表
    */
    public function operation()
    {
        $page = $this->request->input('page', 1);
        $size = $this->request->input('size', 10);
        $offset = ($page * $size) - $size;

        $where = [];

        // 非超管只能看到自己的记录
        if ($this->request->roleId != 1) {
            $where[] = ['a.adminId', '=', $this->request->adminId];
        }

        // 账号搜索
        $account = trim($this->request->input('account', ''));
        if ($account) {
            $where[] = ['b.account', 'like', '%'.$account.'%'];
        }

        $list = DB::table('admin_operation_logs as a')
        	->join('admin_users as b','a.adminId','=','b.id')
            ->where($where)
        	->select('a.id','a.api','a.describe','a.createdAt','a.ip','a.device','b.account')
        	->offset($offset)
        	->limit($size)
        	->orderBy('a.id', 'desc')
            ->get()
            ->toArray();
        if ($list) {
            $list = array_map(function ($item) {
                $item->ip = inet_ntop($item->ip);
                return $item;
            }, $list);
        }
        $count = DB::table('admin_operation_logs as a')
            ->join('admin_users as b','a.adminId','=','b.id')
            ->where($where)
            ->count();

        return response()->json($this->success(['list'=>$list, 'count'=>$count]));
    }

    /**
    * 登录日志列表
    */
    public function login()
    {
        $page = $this->request->input('page', 1);
        $size = $this->request->input('size', 10);
        $offset = ($page * $size) - $size;

        $where = [];

        // 非超管只能看到自己的记录
        if ($this->request->roleId != 1) {
            $where[] = ['a.adminId', '=', $this->request->adminId];
        }

        // 账号搜索
        $account = trim($this->request->input('account', ''));
        if ($account) {
            $where[] = ['b.account', 'like', '%'.$account.'%'];
        }

        $list = DB::table('admin_login_logs as a')
            ->join('admin_users as b','a.adminId','=','b.id')
            ->where($where)
            ->select('a.id','a.loginAt','a.logoutAt','a.ip','a.device','b.account')
            ->offset($offset)
            ->limit($size)
            ->orderBy('a.id', 'desc')
            ->get()->toArray();
        if ($list) {
            foreach ($list as $key => $value) {
                $list[$key]->ip = inet_ntop($value->ip);
                $list[$key]->duration = '';
                // 备注类型：1->当前在线，2->正常退出，3->其他设备登录，4->异常
                $list[$key]->remarkType = 1;
                if ($value->logoutAt) {
                    $list[$key]->remarkType = 2;
                    $list[$key]->duration = $this->_calculationDuration($value->loginAt, $value->logoutAt);
                }

                if ($value->id != $this->request->loginId && empty($value->logoutAt)) {
                    // 是否其他设备登录,token有效期1小时
                    if ((time() - strtotime($value->loginAt)) < env('TOKEN_EXPIRE', 3600)) {
                        $list[$key]->remarkType = 3;
                    } else {
                        $list[$key]->remarkType = 4;
                    }
                }
            }
        }
        $count = DB::table('admin_login_logs as a')
            ->join('admin_users as b','a.adminId','=','b.id')
            ->where($where)
            ->count();

        return response()->json($this->success(['list'=>$list, 'count'=>$count]));
    }

    /**
    * 计算在线时长，1小时以内
    * @param string $start
    * @param string $end
    */
    private function _calculationDuration($start = '', $end = '')
    {
        $duration = (strtotime($end) - strtotime($start));
        if ($duration < 60) {
            return $duration.'s';
        }

        $minute = intval($duration / 60);

        $second = ($duration - ($minute * 60));
        if ($second != 0) {
            return $minute.'分'.$second.'秒';
        }

        return $minute.'分钟';
    }
}
