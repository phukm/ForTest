<?php
/**
 * Dantai Portal (http://dantai.com.jp/)
 *
 * @link      https://fhn-svn.fsoft.com.vn/svn/FSU1.GNC.JIEM-Portal/trunk/Development/SourceCode for the source repository
 * @copyright Copyright (c) 2015 FPT-Software. (http://www.fpt-software.com)
 */
namespace Dantai\Utility;

/**
 * Charset converter helper
 * A helper class to connect convert
 */
class JsonModelHelper
{

    /**
     * @var boolean
     */
    protected $success = true;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * 
     * @var array
     */
    protected $messages = array();

    /**
     * 
     * @var array<\Dantai\Utility\JsonModelHelper>
     */
    protected static $_instances = array();

    /**
     * 
     * @param string $instanceName
     * @return JsonModelHelper
     */
    public static function getInstance($instanceName = 'default')
    {
        if (! isset(static::$_instances[$instanceName])) {
            static::$_instances[$instanceName] = new static();
        }
        return static::$_instances[$instanceName];
    }

    /**
     * Set return status flag to success
     * 
     * @return \Dantai\Utility\JsonModelHelper
     */
    public function setSuccess()
    {
        $this->success = true;
        return $this;
    }

    /**
     * Get current success status
     * 
     * @return boolean
     */
    public function isSuccess()
    {
        return $this->success;
    }
    
    /**
     * Set return status flag to failed
     * 
     * @return \Dantai\Utility\JsonModelHelper
     */
    public function setFail()
    {
        $this->success = false;
        return $this;
    }

    /**
     * Set return attached data
     * 
     * @param array $data
     * @return \Dantai\Utility\JsonModelHelper
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Set return message list
     * 
     * @param array $messages
     * @return \Dantai\Utility\JsonModelHelper
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * Get current returning message list
     * 
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
    
    /**
     * Append a $message to current return message list
     * 
     * @param string $message
     * @return \Dantai\Utility\JsonModelHelper
     */
    public function addMessage($message)
    {
        $this->messages[] = $message;
        return $this;
    }
    
    /**
     * clear all $message of current return message list
     * 
     * @param string $message
     * @return \Dantai\Utility\JsonModelHelper
     */
    public function clearMessage()
    {
        $this->messages = array();
        return $this;
    }
    

    /**
     * Serialize the DateTimeResult as an ISO 8601 date string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->jsonSerialize();
    }

    /**
     * Build current return messages, data, status flag into JSON encoded string
     * 
     * @return string the JSON encoded string
     */
    public function jsonSerialize()
    {
        return \Zend\Json\Encoder::encode($this->toArray());
    }
    
    public function toArray()
    {
        return array(
            'success' => $this->success,
            'messages' => $this->messages,
            'data' => $this->data
        );
    }
}