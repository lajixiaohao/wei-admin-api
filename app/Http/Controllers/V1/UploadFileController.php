<?php
/**
 * 文件上传
 * nginx应配置好client_max_body_size
 * php.ini应配置好upload_max_filesize,post_max_size
 * 使用extension获取扩展名更安全，但可能会和客户端提供的扩展名不同，见
 * https://learnku.com/docs/laravel/9.x/requests/12213#def622
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;

class UploadFileController extends Controller
{
    // 允许图片上传的类型，请自行扩展
    private $allowImageExtension = ['jpg'];

    // 允许图片上传的大小5M
    private $allowImageSize = 5 * 1024 * 1024;

    // 允许视频上传的类型，请自行扩展
    private $allowVideoExtension = ['mp4'];

    // 允许视频上传的大小50M
    private $allowVideoSize = 50 * 1024 * 1024;

    // 允许附件上传的类型，请自行扩展
    private $allowAttachmentExtension = ['zip'];

    // 允许附件上传的大小5M
    private $allowAttachmentSize = 5 * 1024 * 1024;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
    * 上传单张图片
    */
    public function image()
    {
        // 上传文件名
        $file = 'file';

        // 上传文件是否有效
        if (! $this->request->file($file)->isValid()) {
            return response()->json($this->fail('上传文件无效'));
        }

        // 支持的扩展类型
        if (! in_array($this->request->file($file)->extension(), $this->allowImageExtension)) {
            return response()->json($this->fail('不支持的图片类型'));
        }

        // 图片大小
        if ($this->request->file($file)->getSize() > $this->allowImageSize) {
            return response()->json($this->fail('图片大小不能超过5M'));
        }

        try {
            // 存储目录
            $dir = 'images/' . date('Y') . '/' . date('m');
            $path = $this->request->file($file)->store($dir, env('IMAGE_DISK', 'local'));
            $this->recordLog('上传图片：' . env('RESOURCE_URL', '') . $path);
            return response()->json($this->success(['path'=>$path]));
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
        // 上传文件名
        $file = 'file';

        // 上传文件是否有效
        if (! $this->request->file($file)->isValid()) {
            return response()->json($this->fail('上传文件无效'));
        }

        // 支持的扩展类型
        if (! in_array($this->request->file($file)->extension(), $this->allowVideoExtension)) {
            return response()->json($this->fail('不支持的视频类型'));
        }

        // 视频大小
        if ($this->request->file($file)->getSize() > $this->allowVideoSize) {
            return response()->json($this->fail('视频大小不能超过50M'));
        }

        try {
            // 存储目录
            $dir = 'videos/' . date('Y') . '/' . date('m');
            $path = $this->request->file($file)->store($dir, env('VIDEO_DISK', 'local'));
            $this->recordLog('上传视频：' . env('RESOURCE_URL', '') . $path);
            return response()->json($this->success(['path'=>$path]));
        } catch (\Exception $e) {
            return response()->json($this->fail($this->errMessage));
        }

        return response()->json($this->fail());
    }

    /**
     * 上传附件‘
     */
    public function attachment()
    {
        // 上传文件名
        $file = 'file';

        // 上传文件是否有效
        if (! $this->request->file($file)->isValid()) {
            return response()->json($this->fail('上传文件无效'));
        }

        // 支持的扩展类型
        if (! in_array($this->request->file($file)->extension(), $this->allowAttachmentExtension)) {
            return response()->json($this->fail('不支持的附件类型'));
        }

        // 附件大小
        if ($this->request->file($file)->getSize() > $this->allowAttachmentSize) {
            return response()->json($this->fail('附件大小不能超过5M'));
        }

        try {
            // 存储目录
            $dir = 'attachments/' . date('Y') . '/' . date('m');
            $path = $this->request->file($file)->storeAs($dir, $this->request->file($file)->getClientOriginalName(), env('ATTACHMENT_DISK', 'local'));
            $this->recordLog('上传附件：' . env('RESOURCE_URL', '') . $path);
            return response()->json($this->success(['path'=>$path]));
        } catch (\Exception $e) {
            return response()->json($this->fail($e->getMessage()));
        }

        return response()->json($this->fail());
    }
}