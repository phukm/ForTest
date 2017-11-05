<?php
$env = getenv('APP_ENV') ?  : 'production';
$config = array(
    'ConsoleInvitation' => array(
        'econtext_combini_ftp_config' => array(
            'host' => 'test.econ.ne.jp',
            'port' => '22',
            'timeout' => 90,
            'username' => 'order078010',
            'password' => 'X5ost=Wd',
            'ssl' => true
        ),
        'einavi_studygear_ftp_config' => array(
            'host' => '54.65.212.25',
            'port' => '22',
            'timeout' => 90,
            'username' => 'eikengp',
            'ssl' => true
        ),
        'econtext_combini_site_code' => '078010',
        'invitation_generrate_link' => 'https://teacher-support.eiken.or.jp/invitation/generate/index',
        'login_link' => 'https://teacher-support.eiken.or.jp/login',
    )
);

return $config;