<?php
$env = getenv('APP_ENV') ?  : 'production';
$config = array(
    'ConsoleInvitation' => array(
        'econtext_combini_ftp_config' => array(
            'host' => 'test.econ.ne.jp',
            'port' => '22',
            'timeout' => 90,
            'username' => 'order078012',
            'password' => 'Ao\XFpH3',
            'ssl' => true
        ),
        'einavi_studygear_ftp_config' => array(
            'host' => '54.65.212.25',
            'port' => '22',
            'timeout' => 90,
            'username' => 'eikengp',
            'ssl' => true
        ),
        'econtext_combini_site_code' => '078012',
        'login_link' => 'https://demo.teacher-support.eiken.or.jp/login',
    )
);

return $config;