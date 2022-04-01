<?php
/**
 * 加密应用
 */
namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;

class EncryptionController extends Controller
{
    /**
    * 获取RSA密钥对
    * https://www.php.net/manual/zh/function.openssl-pkey-new.php
    * @return json
    */
    public function rsa()
    {
        try {
            $config = [
                'digest_alg'=>'sha512',
                'private_key_bits'=>1024,
                'private_key_type'=>OPENSSL_KEYTYPE_RSA
            ];
            $res = openssl_pkey_new($config);
            $data['publickey'] = openssl_pkey_get_details($res)['key'];
            openssl_pkey_export($res, $privatekey);
            $data['privatekey'] = $privatekey;

            return response()->json($this->success($data));
        } catch (\Exception $e) {
            return response()->json($this->fail($this->errMessage));
        }
    }
}