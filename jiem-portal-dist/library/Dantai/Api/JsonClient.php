<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Dantai\Api;

use Zend\Http\Client;
use Zend\Json\Encoder;
use Zend\Json\Decoder;

/**
 * Json client
 * A helper class to connect customers API system
 */
abstract class JsonClient
{

    const optionKeys = array(
        'timeout',
        'sslverifypeer',
        'sslallowselfsigned'
    );

    /**
     * A client helper to connect to API server through HTTP(S) protocol
     *
     * @var Client
     */
    protected $httpClient = null;

    /**
     * The single ton of class Library\Api
     *
     * @var JsonClient
     */
    protected static $_instance = null;

    /**
     * The single ton function to get one instance of Library\Api
     *
     * @return SELF
     */
    public static function getInstance()
    {
        if (! (static::$_instance instanceof static)) {
            static::$_instance = new static();
        }

        return static::$_instance;
    }

    public function __construct()
    {
        $config = array(
            'adapter'     => 'Zend\Http\Client\Adapter\Curl',
            'curloptions' => array(
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4
            ),
        );
        $this->httpClient = new Client(null, $config);
    }

    /**
     * Call to API function with the given parameters in general case
     *
     * @param string $functionName
     * @param array $config
     * @param array $functionParameters
     * @throws Library\Dantai\Api\Exception\InvalidArgumentException
     * @throws Library\Dantai\Api\Exception\RuntimeException
     * @return mixed
     */
    public function callFunction($functionName, array $config, array $functionParameters)
    {
        if (empty($config)) {
            throw new Exception\InvalidArgumentException('The given config should not be empty. At API function (' . $functionName . ')');
        }
        $functionName = ltrim($functionName, '/');
        $uri = $config['protocol'] . $config['end_point'] . '/' . $functionName;

        $options = array();
        foreach ($config as $cfgKey => $cfgValue) {
            if (in_array($cfgKey, static::optionKeys)) {
                $options[$cfgKey] = $cfgValue;
            }
        }
        if (! empty($options)) {
            $this->httpClient->setOptions($options);
        }
        $startTime = microtime(true);
        $this->httpClient->setUri($uri);
        $this->httpClient->setMethod('POST');
        $this->httpClient->setParameterPost($this->prepareParameters($functionParameters));
        $this->httpClient->send();
        $statusCode = $this->httpClient->getResponse()->getStatusCode();
        if ($statusCode == 200) {
            $responseData = $this->httpClient->getResponse()->getBody();
            //write log to trace issue if any
            //Function name like step-eir/EIR2A02
            $arrFuncName = explode('/', $functionName);
            $logPath = DATA_PATH . '/' . $arrFuncName[1] .'.'. date('Ymd') .'.txt';
            $stream = @fopen($logPath, 'a', false);
            if ($stream) {
                $writer = new \Zend\Log\Writer\Stream($logPath);
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $time = microtime(true) - $startTime;
                $logger->info('REQUEST: ' . \Zend\Json\Json::encode($functionParameters) 
                        . ' - RESPONSE: ' . $responseData
                        . ' - EXECUTE TIME: ' . (string)$time);
            }
            return \Zend\Json\Json::decode($responseData);
        } else {
            throw new Exception\RuntimeException(sprintf('API call got unexpected response code %s with reasion %s. At API function (%s)', $statusCode, $this->httpClient->getResponse()->getReasonPhrase(), $functionName));
        }
    }
    
    
    /**
     * Call to API function with the given parameters in general case
     *
     * @param string $functionName
     * @param array $config
     * @param array $functionParameters
     * @throws Library\Dantai\Api\Exception\InvalidArgumentException
     * @throws Library\Dantai\Api\Exception\RuntimeException
     * @return mixed
     */
    public function callFunctionEContext($functionName, array $config, array $functionParameters)
    {
        if (empty($config)) {
            throw new Exception\InvalidArgumentException('The given config should not be empty. At API function (' . $functionName . ')');
        }
        $functionName = ltrim($functionName, '/');
        $uri = $config['protocol'] . $config['end_point'] . '/' . $functionName;

        $options = array();
        foreach ($config as $cfgKey => $cfgValue) {
            if (in_array($cfgKey, static::optionKeys)) {
                $options[$cfgKey] = $cfgValue;
            }
        }
        if (! empty($options)) {
            $this->httpClient->setOptions($options);
        }

        $this->httpClient->setUri($uri);
        $this->httpClient->setMethod('POST');
        $this->httpClient->setParameterPost($functionParameters);
        $this->httpClient->send();
        $statusCode = $this->httpClient->getResponse()->getStatusCode();
        if ($statusCode == 200) {
            $responseData = $this->httpClient->getResponse()->getBody();
            //write log to trace issue if any
            $arrFuncName = explode('/', $functionName);
            $dataPath = getcwd() . '/data';
            $stream = @fopen($dataPath . '/' . end($arrFuncName) . '.txt', 'a', false);
            if ($stream) {
                $writer = new \Zend\Log\Writer\Stream($dataPath . '/' . end($arrFuncName) . '.txt');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                foreach($functionParameters as $key=>$value){
                    if($key == 'CVV2'){
                        $functionParameters[$key] = str_repeat("*", strlen($value));
                    }else if($key == 'econCardno'){
                        $functionParameters[$key] = $this->maskNumber($value);
                    }else if($key == 'cardExpdate'){
                        $functionParameters[$key] = str_repeat("*", strlen($value));
                    }else{
                        $functionParameters[$key] = \Dantai\Utility\CharsetConverter::shiftJisToUtf8($value);
                    }
                    
                }
                $logger->info('REQUEST: ' . \Zend\Json\Json::encode($functionParameters) . ' - RESPONSE: ' . $responseData);
            }

            $arrResponseData = explode(' ', $responseData);
            $response['status'] = isset($arrResponseData[0]) ? intval($arrResponseData[0]) : -1;
            $response['uri'] = isset($arrResponseData[1]) ? $arrResponseData[1] : '';
            $response['message'] = isset($arrResponseData[2]) ? $arrResponseData[2] : 'Error Data';
            return $response;
        } else {
            throw new Exception\RuntimeException(sprintf('API call got unexpected response code %s with reasion %s. At API function (%s)', $statusCode, $this->httpClient->getResponse()->getReasonPhrase(), $functionName));
        }
    }

    /**
     * Prepare API parameters structure before sending
     *
     * @param array $functionParameters
     * @return multitype:string
     */
    protected function prepareParameters(array $functionParameters)
    {
        $json = Encoder::encode($functionParameters);
        return array(
            'eikendata' => $json
        );
    }

    /**
     * This function specified the method to encrypt secret data before sending
     * authentication information
     *
     * @param array $config
     * @throws Exception\InvalidArgumentException
     */
    protected function prepareCryptKey(array $config)
    {
        if (! isset($config['fixed_key1']) || ! isset($config['data1']) || ! isset($config['fixed_key2']) || ! isset($config['data2']) || ! isset($config['fixed_key3']) || ! isset($config['cryptmethod'])) {
            throw new Exception\InvalidArgumentException('The given config should provide keys: fixed_key1, fixed_key2, fixed_key3, data1, data2, cryptmethod');
        }

        return hash($config['cryptmethod'], $config['fixed_key1'] . $config['data1'] . $config['fixed_key2'] . $config['data2'] . $config['fixed_key3'], false);
    }
    
    protected function maskNumber($number, $count = 4, $seperators = '-')
    {
        $masked = preg_replace('/\d/', 'x', $number);
        $last = preg_match(sprintf('/([%s]?\d){%d}$/', preg_quote($seperators),  $count), $number, $matches);
        if ($last) {
            list($clean) = $matches;
            $masked = substr($masked, 0, -strlen($clean)) . $clean;
        }
        return $masked;
    }
}