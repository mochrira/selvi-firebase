<?php 

namespace Selvi\Firebase\Controllers;
use Selvi\Firebase\Controller;
use Selvi\Firebase\Models\FcmToken;

class MessagingController extends Controller {

    function __construct() {
        parent::__construct();
        $this->validateRequest();
        $this->load(FcmToken::class, 'FcmToken');
    }

    function updateToken() {
        $uid = $this->penggunaAktif->uid;
        $data = json_decode($this->input->raw(), true);
        $row = $this->FcmToken->row([['uid', $uid], ['platform', $data['platform']]]);
        if($row !== null) {
            $this->FcmToken->update([['id', $row->id]], ['token' => $data['token']]);
        } else {
            $this->FcmToken->insert(['uid' => $uid, 'platform' => $data['platform'], 'token' => $data['token']]);
        }
        return response('', 204);
    }

}