<?php
/**
 * api返回头相关助手封装
 * */
namespace App\Helps;

trait ApiResponse
{
	// try catch发生错误统一返回的提示
	protected $errMessage = '服务器发生错误';

	/**
    * 返回成功code=0
    * @param array $data
    * @param string $msg
    * @return array
    */
    protected function success($data = [], $msg = 'success')
    {
        $ret = ['code'=>0, 'msg'=>$msg];

        if ($data) {
            $ret['data'] = $data;
        }

        return $ret;
    }

	/**
    * 返回失败code=1
    * @param string $msg
    * @return array
    */
    protected function fail($msg = 'error')
    {
        return ['code'=>1, 'msg'=>$msg];
    }
}