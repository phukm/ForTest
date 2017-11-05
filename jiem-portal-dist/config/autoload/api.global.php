<?php
$env = getenv('APP_ENV') ?  : 'production';
$config = array(
    'eiken_config' => array(
        'api' => array(
            'protocol' => 'http://',
            'end_point' => '101.110.19.200',
            'function' => '',
            'sslverifypeer' => ($env == 'production'),
            'timeout' => 30,
            'fixed_key1' => 'yLJdqm',
            'fixed_key2' => '5FPgNi4dO',
            'fixed_key3' => '6YS5o',
            'cryptmethod' => 'md5'
        ),
    ),
    'invitationmnt_config' => array(
        'api_userauthen' => array(
            'protocol' => 'https://',
            'end_point' => 'dev-bke.ei-navi.jp',
            'function' => '',
            'sslverifypeer' => ($env == 'production'),
            'sslallowselfsigned' => ($env != 'production'),
            'timeout' => 30,
            'fixed_key1' => 'SN3ubspi3TjJ',
            'fixed_key2' => '98dDVjXdpuAi',
            'fixed_key3' => 'bmC4xgMw7RJb',
            'cryptmethod' => 'sha512',
            'owner_id' => 'EKGP',
            'app_id' => 'EKGP',
            'authentication_type' => 1,
            'addressredirect' => 'http://www.ei-navi.jp'
        )
    ),
    'creditcard_config' => array(
        'api' => array(
            'protocol' => 'https://',
            'end_point' => 'test.econ.ne.jp',
            'function' => '/econtest/exe/rcv/n_econ_rcv_odr.exe',
            'sslverifypeer' => ($env == 'production'),
            'timeout' => 30,
        ),
        'api_send_order' => array(
            'protocol' => 'https://',
            'end_point' => 'test.econ.ne.jp',
            'function' => '/econtest/exe/rcv/econ_rcv_end.exe',
            'sslverifypeer' => ($env == 'production'),
            'timeout' => 30,
        ),
        'api_cancel_order' => array(
            'protocol' => 'https://',
            'end_point' => 'test.econ.ne.jp',
            'function' => '/econ/odr/cancel/econ_ccl_odr.aspx',
            'chkCode' => '078013000000',
            'sslverifypeer' => ($env == 'production'),
            'timeout' => 30,
        ),
        'site_code' => '078013',
        'email' => 'kojin@mail.eiken.or.jp',
    )
);
$config['iba_config'] = $config['eiken_config'];
$config['orgmnt_config'] = $config['eiken_config'];
$config['ApplicationModuleConfig'] = $config['eiken_config'];

$config['satellite_config']['api_userauthen'] = $config['invitationmnt_config']['api_userauthen'];
$config['goalsetting_config']['api_userauthen'] = $config['invitationmnt_config']['api_userauthen'];
$config['invitationmnt_config']['api_einavi'] = $config['invitationmnt_config']['api_userauthen'];

return $config;