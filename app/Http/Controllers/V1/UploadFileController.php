<?php
/**
 * 文件上传
 * nginx应配置好client_max_body_size
 * php.ini应配置好upload_max_filesize,post_max_size
 * 2021.7.30
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadFileController extends Controller
{
    // 允许图片上传的类型
    private $allowImageExtension = ['jpg', 'jpeg', 'gif', 'png'];

    // 允许图片上传的大小5M
    private $allowImageSize = 5 * 1024 * 1024;

    // 允许视频上传的类型
    private $allowVideoExtension = ['mp4'];

    // 允许视频上传的大小50M
    private $allowVideoSize = 50 * 1024 * 1024;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
    * 上传单张图片
    */
    public function image()
    {
        //wangeditor编辑器返回格式
        $ret = ['errno'=>0, 'data'=>[], 'relative_path'=>''];

        try {
            $filename = 'weifile';
            if ($this->request->hasFile($filename) && $this->request->file($filename)->isValid()) {
                $extension = $this->request->file($filename)->extension();
                if (! in_array($extension, $this->allowImageExtension)) {
                    $ret['msg'] = '不支持的上传类型';
                    return response()->json($ret);
                }

                $size = $this->request->file($filename)->getSize();
                if ($size > $this->allowImageSize) {
                    $ret['msg'] = '文件超出预设大小:'.$this->allowImageSize;
                    return response()->json($ret);
                }

                $dir = 'images/'.date('Y').'/'.date('m');
                $path = Storage::disk(env('IMAGE_DISK', 'local'))->putFile($dir, $this->request->file($filename));
                if ($path) {
                    $imgUrl = env('RESOURCE_URL', '').$path;
                    $this->recordLog('上传图片，图片地址：'.$imgUrl);
                    $ret['data'][] = $imgUrl;
                    $ret['relative_path'] = $path;
                }
            }
        } catch (\Exception $e) {
            $ret['msg'] = $e->getMessage();
        }

        return response()->json($ret);
    }

    /**
    * 上传单个视频
    */
    public function video()
    {
        //wangeditor编辑器返回格式
        $ret = ['errno'=>0, 'data'=>['url'=>''], 'relative_path'=>''];

        try {
            $filename = 'weivideo';
            if ($this->request->hasFile($filename) && $this->request->file($filename)->isValid()) {
                $extension = $this->request->file($filename)->extension();
                if (! in_array($extension, $this->allowVideoExtension)) {
                    $ret['msg'] = '不支持的上传类型';
                    return response()->json($ret);
                }

                $size = $this->request->file($filename)->getSize();
                if ($size > $this->allowVideoSize) {
                    $ret['msg'] = '文件超出预设大小:'.$this->allowVideoSize;
                    return response()->json($ret);
                }

                $dir = 'videos/'.date('Y').'/'.date('m');
                $path = Storage::disk(env('VIDEO_DISK', 'local'))->putFile($dir, $this->request->file($filename));
                if ($path) {
                    $url = env('RESOURCE_URL', '').$path;
                    $this->recordLog('上传视频，视频地址：'.$url);
                    $ret['data']['url'] = $url;
                    $ret['relative_path'] = $path;
                }
            }
        } catch (\Exception $e) {
            $ret['msg'] = $e->getMessage();
        }

        return response()->json($ret);
    }
}