<?php
namespace Dantai\Aws;

use Aws\S3\S3Client;

class AwsS3Client
{
    const BUCKET_PREFIX = 'dantai';
    const S3_CONFIG_DATE_IMPORT_PATH = 'configDate/import';
    const S3_CONFIG_DATE_EXPORT_PATH = 'configDate/export';

    protected $s3Config;
    protected $s3Client;

    /**
     *
     * @var \Dantai\S3\AwsS3Client
     */
    protected static $_instance = null;

    /**
     * Singleton
     *
     * @return \Dantai\S3\AwsS3Client
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self(Config::getConfig(Config::AWS_S3));
        }

        return self::$_instance;
    }

    protected function __construct($config = null)
    {
        $this->s3Config = $config;
    }

    protected function getClient()
    {
        if (!$this->s3Client) {
            $this->s3Client = new \Aws\S3\S3Client($this->s3Config);
        }
        return $this->s3Client;
    }


    public function readObject($bucket, $key)
    {

        $result = array("status" => 1, "error" => "", "content" => "");

        $exitsBucket = $this->getClient()->doesBucketExist($bucket);
        if ($exitsBucket == false) {
            $result["status"] = 0;
            $result["error"] = "Don't Exist This Bucket";
        } else {
            $exitsObj = $this->getClient()->doesObjectExist($bucket, $key);

            if ($exitsObj == false) {
                $result["status"] = 404;
                $result["error"] = "Don't Exist This Object";
            } else {
                $resultObj = $this->getClient()->getObject(array(
                    'Bucket' => $bucket,
                    'Key' => $key,
                ));
                $result["content"] = $resultObj;
            }
        }

        return $result;
    }


    public function writeObject($bucket, $key, $content)
    {

        $result["status"] = 1;

        $exitsBucket = $this->getClient()->doesBucketExist($bucket);

        if ($exitsBucket == false) {
            try {

                $this->getClient()->createBucket(array('Bucket' => $bucket));

            } catch (\Aws\S3\Exception\S3Exception $e) {
                $result["status"] = 0;
                $result["statusCode"] = $e->getStatusCode();
                $result["error"] = $e->getAwsErrorCode() == 'BucketAlreadyExists' ? 'Bucket names in Amazon S3 reside in a global namespace' : '';
            }
        }

        if ($result["status"] == 1) {
            $resultObj = $this->getClient()->putObject(array(
                'Bucket' => $bucket,
                'Key' => $key,
                'Body' => $content
            ));
            $result["statusCode"] = $resultObj["@metadata"]["statusCode"];
            $result["ObjectURL"] = $resultObj["ObjectURL"];

        }

        return $result;
    }

    public function deleteObject($bucket, $key)
    {
        $result = array("status" => 1, "error" => "");

        $exitsBucket = $this->getClient()->doesBucketExist($bucket);
        if ($exitsBucket == false) {
            $result["status"] = 0;
            $result["error"] = "Don't Exist This Bucket";
        } else {
            $exitsObj = $this->getClient()->doesObjectExist($bucket, $key);

            if ($exitsObj == false) {
                $result["status"] = 404;
                $result["error"] = "Don't Exist This Object";
            } else {
                $a = $this->getClient()->deleteObject(array(
                    'Bucket' => $bucket,
                    'Key' => $key,
                ));
            }
        }

        return $result;
    }

    public function listObject($bucket, $prefix = '')
    {
        $result = array("status" => 1, "error" => "", 'data' => array());
        $existBucket = $this->getClient()->doesBucketExist($bucket);
        if (!$existBucket) {
            $result['error'] = 'Do Not Exist This Bucket: ' . $bucket;
            return $result;
        }
        $objects = $this->getClient()->getIterator('ListObjects', array(
            'Bucket' => $bucket,
            'Prefix' => $prefix
        ));
        foreach ($objects as $object) {
            $value['link'] = $object['Key'];
            $value['filename'] = basename($value['link']);
            $result['data'][] = $value;
        }
        return $result;
    }
}