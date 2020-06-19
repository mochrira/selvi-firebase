<?php 

namespace Selvi\Firebase\Controllers;

use Selvi\Firebase\Models\Undangan;
use Selvi\Firebase\Models\Config;
use Selvi\Firebase\Models\Akses;

use Selvi\Firebase\Resource;
use Selvi\Exception;

class UndanganController extends Resource {

    protected $modelClass = Undangan::class;
    protected $modelAlias = 'Undangan';

    function __construct() {
        parent::__construct(false);
        if($this->uri->segment(2) !== 'verify') {
            $this->validateRequest(); 
            if($this->aksesAktif->tipe !== 'OWNER') {
                Throw new Exception('Anda tidak memiliki akses ke fasilitas ini ', 'auth/invalid-akses', 403);
            }
        } else {
            $this->validateToken();
            $this->validatePengguna();
            $this->validatePhoneNumber();
        }

        $this->loadModel();
        $this->load(Config::class, 'Config');
        $this->load(Akses::class, 'Akses');
    }

    function verify() {
        $data = json_decode($this->input->raw(), true);
        if(!isset($data['code'])) {
            Throw new Exception('Kode undangan harus diisi', 'undangan/invalid-code', 400);
        }

        $undangan = $this->Undangan->row([['code', md5($data['code'])]]);
        if(!$undangan) {
            Throw new Exception('Kode undangan salah atau tidak ditemukan', 'undangan/invalid-code', 404);
        }
        if((time() - strtotime($undangan->iat)) > (3600 * 24)) {
            Throw new Exception('Undangan expired', 'undangan/expired', 404);
        }
        if($undangan->status == 'USED') {
            Throw new Exception('Undangan telah digunakan sebelumnya', 'undangan/used', 404);
        }

        $akses = $this->Akses->row([
            ['uid', $this->penggunaAktif->uid],
            ['tipe', $undangan->akses]
        ]);
        if($akses) {
            Throw new Exception('Pengguna telah terdaftar pada lembaga terpilih', 'undangan/invalid-state', 403);
        }

        if(!$this->Akses->insert([
            'uid' => $this->penggunaAktif->uid,
            'idLembaga' => $undangan->idLembaga,
            'tipe' => $undangan->akses,
            'idUndangan' => $undangan->id,
            'isDefault' => true
        ])) {
            Throw new Exception('Gagal menambahkan akses', 'undangan/failed-to-insert', 500);
        }

        if(!$this->Undangan->update([['id', $undangan->id]], [
            'status' => 'USED',
            'uat' => date('Y-m-d H:i:s')
        ])) {
            Throw new Exception('Gagal mengupdate undangan', 'undangan/failed-to-update', 500);
        }

        return response('', 204);
    }

    function validateData() {
        $data = json_decode($this->input->raw(), true);
        if(!isset($data['phoneNumber'])) {
            Throw new Exception('Nomor HP harus diisi', 'undangan/invalid-number', 400);
        }
        if(!isset($data['akses'])) {
            Throw new Exception('Akses harus diisi', 'undangan/invalid-akses', 400);
        }

        $code = $this->generateRandomString(4);
        $found = true;
        while($found == true) {
            $cek = $this->Undangan->row([['code', md5($code)]]);
            if(!$cek) {
                $found = false;
            }
        }

        $code = $this->generateRandomString(4);
        $pesan = urlencode(str_replace('{{code}}', $code, $this->Config->get('TEMPLATE_UNDANGAN')));
        $apiUrl = $this->Config->get('GATEWAY_API_URL');
        $apiKey = $this->Config->get('GATEWAY_API_KEY');

        $url = $apiUrl."?nomor=".$data['phoneNumber']."&pesan=$pesan&priority=high&apiKey=$apiKey";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        return [
            'phoneNumber' => $data['phoneNumber'],
            'code' => md5($code),
            'idLembaga' => $this->lembagaAktif->idLembaga,
            'akses' => $data['akses'],
            'iat' => date('Y-m-d H:i:s')
        ];
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}