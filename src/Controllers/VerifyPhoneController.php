<?php 

namespace Selvi\Firebase\Controllers;
use Selvi\Firebase\Controller;
use Selvi\Firebase\Libraries\OpenSSL;
use Selvi\Firebase\Models\Pengguna;
use Selvi\Firebase\Models\Config;
use Selvi\Exception;
use Selvi\Firebase\VerifyPhone;

class VerifyPhoneController extends Controller {

    function __construct() {
        parent::__construct(false);
        $this->validateToken();
        $this->validatePengguna();
        $this->load(Pengguna::class, 'Pengguna');
        $this->load(Config::class, 'Config');
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function getToken() {
        $data = json_decode($this->input->raw(), true);
        if(!isset($data['phoneNumber'])) {
            Throw new Exception('Nomor harus diisi', 'verify-phone/invalid-phone', 400);
        }

        $code = $this->generateRandomString(4);
        $pesan = urlencode(str_replace('{{code}}', $code, $this->Config->get('TEMPLATE_VERIFIKASI')));
        $apiUrl = $this->Config->get('GATEWAY_API_URL');
        $apiKey = $this->Config->get('GATEWAY_API_KEY');

        $url = $apiUrl."?nomor=".$data['phoneNumber']."&pesan=$pesan&priority=high&apiKey=$apiKey";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        try {
            $result = json_decode($result, true);
        } catch(\Exception $e) {
            Throw new Exception('Unable to send code', 'verify-phone/unable-to-send', 500);
        }

        return jsonResponse([
            'token' => OpenSSL::encrypt(json_encode([
                    'code' => $code,
                    'phoneNumber' => $data['phoneNumber']
                ]), VerifyPhone::$key, VerifyPhone::$method, VerifyPhone::$iv
            )
        ]);
    }

    function verifyToken() {
        $data = json_decode($this->input->raw(), true);
        if(!isset($data['code'])) {
            Throw new Exception('Kode harus diisi', 'verify-phone/missing-code', 400);
        }
        if(!isset($data['token'])) {
            Throw new Exception('Missing token', 'verify-phone/missing-token', 400);
        }

        $token = json_decode(OpenSSL::decrypt(
            $data['token'], 
            VerifyPhone::$key, VerifyPhone::$method, VerifyPhone::$iv
        ), true);
        if($data['code'] !== $token['code']) {
            Throw new Exception('Kode tidak valid', 'verify-phone/invalid-code', 400);
        }

        if(!$this->Pengguna->update([['uid', $this->penggunaAktif->uid]], ['phoneNumber' => $token['phoneNumber']])) {
            Throw new Exception('Gagal update nomor', 'verify-phone/failed-to-update', 500);
        }
        return response('', 204);
    }

}