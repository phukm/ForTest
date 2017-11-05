<?php
namespace Dantai\Aws;

class Config
{
    const AWS_SQS = 'sqs';
    const AWS_SES = 'ses';
    const AWS_SES_EXT = 'ses-ext';
    const AWS_S3 = 's3';


    protected static $config = array(
        'aws' => array(
            'ses' => array(
                'credentials' => array(
                    'key' => 'AKIAJOH4FLWVLV3F3XIA',
                    'secret' => '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'
                ),
                'region' => 'eu-west-1',
                'version' => '2010-12-01'
            ),
            'ses-ext' => array(
                'source-mail' => 'dantai@mail.eiken.or.jp'
            ),
            'sqs' => array(
                'credentials' => array(
                    'key' => 'AKIAJOH4FLWVLV3F3XIA',
                    'secret' => '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'
                ),
                'region' => 'ap-northeast-1',
                'version' => '2012-11-05'
            ),
            'sqs-staging' => array(
                'sqsUrl' => array(
                    'DantaiGenInvitation' => 'DantaiGenInvitation',
                    'DantaiGenCombini' => 'DantaiGenCombini',
                ),
            ),
            's3' => array(
                'credentials' => array(
                    'key' => 'AKIAJOH4FLWVLV3F3XIA',
                    'secret' => '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'
                ),
                'region' => 'ap-northeast-1',
                'version' => '2006-03-01'
            ),
        )
    );
    
    public static function setConfig($config){
        self::$config = $config;
    }
    
    public static function getConfig($type){
        return self::$config[$type];
    }
    
    public static function getConfigStaging($type){
        return self::$config[$type.'-staging'];
    }
}