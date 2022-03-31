<?php
/**
 * 加密应用
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use phpseclib\Crypt\RSA;

class EncryptionController extends Controller
{
    /**
    * 获取非对称加密密钥RSA
    * @return json
    */
    public function rsa()
    {
        try {
            $rsa = new RSA();
            $res = $rsa->createKey();
            unset($res['partialkey']);
            return response()->json($this->success($res));
        } catch (\Exception $e) {
            return response()->json($this->fail($e->getMessage()));
        }
    }
}