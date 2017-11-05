<?php

$listIp = array(
    '1.55.242.165',
    '203.162.169.35',
    '183.81.81.189',
    '118.70.81.83',
    '113.190.252.57',
    '117.6.162.8',
    '118.71.152.22',
    '118.70.199.1',
    '203.162.169.70',
    '118.70.197.132',
    '203.162.169.196',
    '113.23.11.155',
    '118.70.199.49',
    '118.70.178.53',
    //'203.116.81.11',
    '42.113.50.148',
    '39.110.201.81',
    '153.162.248.45',
    '122.18.149.102',
    '118.103.64.97',
    '61.196.201.25',
    '153.228.79.132',
    '218.186.115.56',
    '118.70.135.33'
);


function get_real_ip()
{

    if (isset($_SERVER["HTTP_CLIENT_IP"]))
    {
        return $_SERVER["HTTP_CLIENT_IP"];
    }
    elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
    {
        return $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    elseif (isset($_SERVER["HTTP_X_FORWARDED"]))
    {
        return $_SERVER["HTTP_X_FORWARDED"];
    }
    elseif (isset($_SERVER["HTTP_FORWARDED_FOR"]))
    {
        return $_SERVER["HTTP_FORWARDED_FOR"];
    }
    elseif (isset($_SERVER["HTTP_FORWARDED"]))
    {
        return $_SERVER["HTTP_FORWARDED"];
    }
    else
    {
        return $_SERVER["REMOTE_ADDR"];
    }
}
$ipAddress = get_real_ip();
$uri = $_SERVER["REQUEST_URI"];
if($uri != "/api/payment/update-status"){
    if (!in_array($ipAddress, $listIp)) {
        header('Location: https://teacher-support.eiken.or.jp/index.html');
        exit;
    }
}
