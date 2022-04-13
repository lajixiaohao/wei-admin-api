<?php
/**
 * 文件上传
 * nginx应配置好client_max_body_size
 * php.ini应配置好upload_max_filesize,post_max_size
 * 2022.4.13
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
        try {
            $filename = 'file';
            if ($this->request->hasFile($filename) && $this->request->file($filename)->isValid()) {
                $extension = $this->request->file($filename)->extension();
                if (! in_array($extension, $this->allowImageExtension)) {
                    return response()->json($this->fail('不支持的图片类型'));
                }

                $size = $this->request->file($filename)->getSize();
                if ($size > $this->allowImageSize) {
                    // 提示文案，应保持与$allowImageSize对应
                    return response()->json($this->fail('图片不能超过5M'));
                }

                $dir = 'images/'.date('Y').'/'.date('m');
                $path = Storage::disk(env('IMAGE_DISK', 'local'))->putFile($dir, $this->request->file($filename));
                if ($path) {
                    $url = env('RESOURCE_URL', '').$path;
                    $this->recordLog('上传图片：'.$url);
                    return response()->json($this->success(['url'=>$url]));
                }
            }
        } catch (\Exception $e) {
            return response()->json($this->fail($this->errMessage));
        }

        return response()->json($this->fail());
    }

    /**
    * 上传单个视频
    */
    public function video()
    {
        try {
            $filename = 'file';
            if ($this->request->hasFile($filename) && $this->request->file($filename)->isValid()) {
                $extension = $this->request->file($filename)->extension();
                if (! in_array($extension, $this->allowVideoExtension)) {
                    return response()->json($this->fail('不支持的视频类型'));
                }

                $size = $this->request->file($filename)->getSize();
                if ($size > $this->allowVideoSize) {
                    // 提示文案，应保持与$allowVideoSize对应
                    return response()->json($this->fail('图片不能超过50M'));
                }

                $dir = 'videos/'.date('Y').'/'.date('m');
                $path = Storage::disk(env('VIDEO_DISK', 'local'))->putFile($dir, $this->request->file($filename));
                if ($path) {
                    $url = env('RESOURCE_URL', '').$path;
                    $this->recordLog('上传视频：'.$url);
                    return response()->json($this->success(['url'=>$url]));
                }
            }
        } catch (\Exception $e) {
            return response()->json($this->fail($this->errMessage));
        }

        return response()->json($this->fail());
    }
}