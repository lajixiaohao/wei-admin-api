<?php
/**
 * 验证码管理
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
	        // 图像宽度
	        $width = 120;
	        // 图像高度
			$height = 40;

	        // 创建图像
	        $img = imagecreatetruecolor($width, $height);
	        // 背景色
			$colorBg = imagecolorallocate($img, 255, 255, 255);
			// 填充颜色
			imagefill($img, 0, 0, $colorBg);

			// 设置干扰点
			for ($i = 0; $i < 100; $i++) {
				$pixColor = imagecolorallocate($img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
				imagesetpixel($img, mt_rand(0, $width), mt_rand(0, $height), $pixColor);
			}

			// 设置干扰线
			// for ($i = 0; $i < 2; $i++) {
			// 	$lineColor = imagecolorallocate($img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
			// 	imageline($img, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $lineColor);
			// }

			// 定义运算符
			$operator = ['+', '-', '×'];
			$operatorLen = count($operator);

			// 定义运算取值范围的数组
			$num = range(1, 9);
			$numLen = count($num);

			// 存放验证码
			$code = [];
			for ($i = 0; $i < $operatorLen; $i++) { 
				if ($i == 1) {
					// 取运算符
					$code[] = $operator[mt_rand(0, $operatorLen - 1)];
				} else {
					// 取计算数值
					$code[] = $num[mt_rand(0, $numLen - 1)];
				}
			}
			// 插入运算提示
			array_push($code, '=', '?');

			// 文字大小
			$fontSize = 14;
			// 文字起始x坐标
			$x = 24;

			// 写入图片
			for ($i = 0; $i < 5; $i++) { 
				// 字体颜色
				$fontColor = imagecolorallocate($img, mt_rand(0, 200), mt_rand(0, 200), mt_rand(0, 200));
				imagettftext($img, $fontSize, mt_rand(-5, 5), $x * $i + mt_rand(0, 5), 30, $fontColor, storage_path('font/elephant.ttf'), $code[$i]);
			}

			// 获取图片流
			ob_start();
			imagejpeg($img);
			$imgData = ob_get_contents();
			ob_end_clean();

			// 构造图片base64
			$imgBase64 = 'data:image/jpeg;base64,'.chunk_split(base64_encode($imgData));

			// 验证码标识
			$cid = uniqid();

			// 使用redis存储结果会话
			Redis::setex('cid_'.$cid, 180, $this->_getRes($code));

			return response()->json($this->success(['cid'=>$cid, 'img'=>$imgBase64]));
    	} catch (\Exception $e) {
    		return response()->json($this->fail($e->getMessage()));
    	}
    }

    /**
     * 获取运算结果
     * @param array $code
     * @return int
     * */
    private function _getRes($code = []) 
    {
    	$res = '';
    	switch ($code[1]) {
    		case '+':
    			$res = $code[0] + $code[2];
    		break;

    		case '-':
    			$res = $code[0] - $code[2];
    		break;

    		default:
    			$res = $code[0] * $code[2];
    	}
    	return $res;
    }
}