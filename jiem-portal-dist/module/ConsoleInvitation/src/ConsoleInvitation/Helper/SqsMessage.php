<?php

namespace ConsoleInvitation\Helper;

class SqsMessage {
    
    protected $client;
    
    protected $typeSQS;
    
    protected $message;
    
    protected $receiptHandle;


    private $orgId;
    
    private $scheduleId;
    
    private $classId;
    
    private $priceLevels;


    public function __construct($typeSQS) {
        $this->typeSQS = $typeSQS;
        $this->client = \Dantai\Aws\AwsSqsClient::getInstance();
        $this->message = $this->client->receiveMessage($this->typeSQS);
        $this->fillProperty();
    }
    
    private function fillProperty(){
        if($this->isEmpty()){
            return;
        }
        $message = \Zend\Json\Json::decode($this->message['Body'], \Zend\Json\Json::TYPE_ARRAY);
        $this->receiptHandle = $this->message['ReceiptHandle'];
        $this->orgId = array_key_exists('orgId', $message) ? $message['orgId']: NULL;
        $this->scheduleId = array_key_exists('scheduleId', $message) ? $message['scheduleId']: NULL;
        $this->classId = array_key_exists('classId', $message) ? $message['classId']: NULL;
        $this->priceLevels = array_key_exists('priceLevels', $message) ? $message['priceLevels']: array();
    }
    
    private $isDeleted = false;
    public function delete(){
        if($this->isEmpty()){
            return;
        }
        $this->isDeleted = true;
        return $this->client->deleteMessage($this->typeSQS, $this->receiptHandle);
    }
    
    public function recycleMessage(){
        if($this->isEmpty()){
            return;
        }
        if(!$this->isDeleted){
            $this->delete();
        }
        $this->client->sendMessage(array(
            'QueueUrl' => $this->typeSQS,
            'MessageBody' => $this->message['Body']
        ));
    }

    public function isEmpty(){
        return empty($this->message);
    }
    
    public function getOrgId() {
        return $this->orgId;
    }

    public function getScheduleId() {
        return $this->scheduleId;
    }

    public function getClassId() {
        return $this->classId;
    }
    
    public function getPriceLevels(){
        return $this->priceLevels;
    }

}