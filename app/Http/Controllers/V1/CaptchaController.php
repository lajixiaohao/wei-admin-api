<?php
/**
 * 验证码管理
 * 2021.9.8
 */
namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class CaptchaController extends Controller
{
	public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
    * 获取验证码，数字运算验证码
    * @return json
    */
    public function get()
    {
    	try {
    		//appkey
	        $appkey = $this->request->input('appkey', '');
	        if ($appkey != env('APP_KEY')) {
	            return response()->json($this->fail('安全验证失败'));
	        }

	        //设置验证码图像的宽和高
			$width = 120;
			$height = 40;

			//定义运算数字
			$a = mt_rand(1,10);
			$b = mt_rand(1,10);

			$res = $str = '';
			$r = mt_rand(1, 3);
			if ($r == 1) {
				$res = $a + $b;
				$str = $a.' + '.$b.' = ?';
			}
			if ($r == 2) {
				$res = $a - $b;
				$str = $a.' - '.$b.' = ?';
			}
			if ($r == 3) {
				$res = $a * $b;
				$str = $a.' x '.$b.' = ?';
			}

			//创建图像
			$img = imagecreatetruecolor($width, $height);
			//背景色
			$colorBg = imagecolorallocate($img, 255, 255, 255);
			//字体颜色
			$colorString = imagecolorallocate($img, mt_rand(0, 200), mt_rand(0, 200), mt_rand(0, 200));
			//填充背景色
			imagefill($img, 0, 0, $colorBg);

			//设置像素点
			for ($i = 0; $i < 100; $i++) {
				imagesetpixel($img, mt_rand(0, $width - 1), mt_rand(0, $height - 1), imagecolorallocate($img, mt_rand(100, 200), mt_rand(100, 200), mt_rand(100, 200)));
			}

			//设置字体
			imagettftext($img, 16, 0, mt_rand(5, 15), mt_rand(20, 35), $colorString, storage_path('font/SigmarOne.ttf'), $str);

			ob_start();
			imagejpeg($img);
			$imgData = ob_get_contents();
			ob_end_clean();
			//图片base64
			$imgBase64 = 'data:image/jpeg;base64,'.chunk_split(base64_encode($imgData));


			//唯一id
			$uid = uniqid();
			
			//使用redis存储结果会话
			Redis::setex('captcha_'.$uid, 180, $res);

			return response()->json($this->success(['uid'=>$uid, 'img'=>$imgBase64]));
    	} catch (\Exception $e) {
    		return response()->json($this->fail($e->getMessage()));
    	}
    }
}