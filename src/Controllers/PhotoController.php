<?php 

namespace Selvi\Firebase\Controllers;
use Selvi\Firebase\Controller;
use Selvi\Firebase\Photo;

class PhotoController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->validateRequest();
    }

    public function upload() {
        $base64 = $this->input->raw();
        $data = base64_decode($base64);
        $path = Photo::$path.'/'.$this->penggunaAktif->uid;

        try {
            $image = imagecreatefromstring($data);
            $d = array(imagesx($image), imagesy($image));
            $p = 150 / min($d);
            $new_d = array(round($d[0] * $p), round($d[1] * $p));
            $image_p = imagecreatetruecolor($new_d[0], $new_d[1]);
            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_d[0], $new_d[1], $d[0], $d[1]);
            if(!is_dir($path)){
                mkdir($path, 0777, true);
            }
            $filename = md5(time()).'.jpg';
            imagejpeg($image_p, $path.'/'.$filename);
            imagedestroy($image_p);
            return jsonResponse(array(
                'photoUrl' => base_url().'/'.$this->penggunaAktif->uid.'/'.$filename
            ));
        } catch (\Exception $e){
            Throw $e;
        }
    }

    public function get() {
        if($this->uri->segment(2) !== $this->penggunaAktif->uid) {
            return response('', 403);
        }
        $file = Photo::$path.'/'.$this->penggunaAktif->uid.'/'.$this->uri->segment(3);
        if(!is_file($file)) {
            return response('', 404);
        }
        $contentType = mime_content_type($file);
        $size = filesize($file);

        header("Content-Type: ".$contentType);
        header("Content-Length: ".$size);

        $filePointer = fopen($file, 'rb');
        fpassthru($filePointer);
        die();
    }

}