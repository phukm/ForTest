<?php
$env = getenv('APP_ENV') ?  : 'production';
$config = array(
    'ConsoleInvitation' => array(
        'econtext_combini_ftp_config' => array(
            'host' => 'upload.econ.ne.jp',
            'port' => '22',
            'timeout' => 90,
            'username' => 'order078010',
            'password' => '5n.cJhA%',
            'ssl' => true
        ),
        'einavi_studygear_ftp_config' => array(
            'host' => '54.92.39.155',
            'port' => '22',
            'timeout' => 90,
            'username' => 'eikengp',
            'ssl' => true
        ),
        'econtext_combini_site_code' => '078010',
        'login_link' => 'https://teacher-support.eiken.or.jp/login',
    )
);

return $config;