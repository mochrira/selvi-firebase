<?php 

namespace Selvi\Firebase\Libraries;

class OpenSSL {

    public static function encrypt($str, $key, $method = 'AES-256-CBC', $iv = "") {
        return openssl_encrypt($str, $method, $key, 0, $iv);
    }

    public static function decrypt($str, $key, $method = 'AES-256-CBC', $iv = "") {
        return openssl_decrypt($str, $method, $key, 0, $iv);
    }

}