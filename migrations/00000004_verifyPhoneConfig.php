<?php 

return function ($schema, $direction ) {
    
    if($direction == 'up') {
        $cek = $schema->where([['name', 'GATEWAY_API_URL']])->get('config')->row();
        if(!$cek) {
            $schema->insert('config', [
                'name' => 'GATEWAY_API_URL',
                'value' => 'https://api.gateway.wajek.id/1.1/public/send'
            ]);
        }

        $cek = $schema->where([['name', 'GATEWAY_API_KEY']])->get('config')->row();
        if(!$cek) {
            $schema->insert('config', [
                'name' => 'GATEWAY_API_KEY',
                'value' => 'dkc5am5YYmpVNkJXajZ3dnl2NXQ1WVJaV05JN05DM094cHJraWwvakphVUlYUGlxVkdyc2Vvd0JzcnVKZWVpVWV5SGkvOEFkdUpyYnFOK2k0WTBjL3BoVHpwK1lKZXJVNXVuRzFEZ3FjMGVPbVdZSHhoVHVYdGVXZGx4SlNyQ25XY3ZhUHBKZ01jL3duelBJdmtTWkdRPT0%3D'
            ]);
        }

        $cek = $schema->where([['name', 'TEMPLATE_VERIFIKASI']])->get('config')->row();
        if(!$cek) {
            $schema->insert('config', [
                'name' => 'TEMPLATE_VERIFIKASI',
                'value' => 'Masukkan kode {{code}} untuk memverifikasi nomor HP anda'
            ]);
        }
    }

    if($direction == 'down') {
        $schema->where([['name', 'GATEWAY_API_URL']])->delete('config');
        $schema->where([['name', 'GATEWAY_API_KEY']])->delete('config');
        $schema->where([['name', 'TEMPLATE_VERIFIKASI']])->delete('config');
    }

};