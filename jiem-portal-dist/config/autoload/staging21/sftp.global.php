<?php
$env = getenv('APP_ENV') ?  : 'production';
$config = array(
    'ConsoleInvitation' => array(
        'econtext_combini_ftp_config' => array(
            'host' => 'test.econ.ne.jp',
            'port' => '22',
            'timeout' => 90,
            'username' => 'order078015',
            'password' => 'J9Et&y#?',
            'ssl' => true
        ),
        'einavi_studygear_ftp_config' => array(
            'host' => '54.65.212.25',
            'port' => '22',
            'timeout' => 90,
            'username' => 'eikengp',
            'ssl' => true
        ),
        'econtext_combini_site_code' => '078015',
        'login_link' => 'https://teacher-support.staging-21.fsoft-demo.com/login',
    )
);

return $config;