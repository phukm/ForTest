<?php

namespace ConsoleInvitation\Helper;

class SqsHelper {
    
    protected $client;
    
    private $config;


    public function __construct($config) {
        $this->config = $config;
        $this->client = new \Aws\Sqs\SqsClient($config);
    }

    /**
     * @param string $queueName
     * @return string
     */
    public function getQueueLink($queueName){
        $result = $this->client->createQueue(
            array(
                'QueueName' => $queueName ,
                'Attributes'=> array(
                    'VisibilityTimeout' => 43200 // 12hour [maximum]
                ),
            )
        );
        return $result->get('QueueUrl');
    }
    
    /**
     * 
     * @param string $queueName
     * @param string $message
     * @return type
     */
    public function sendMessage($queueName,$message){
        return $this->client->sendMessage(array(
            'QueueUrl'    => $this->getQueueLink($queueName),
            'MessageBody' => $message,
        ));
    }
    
    /**
     * @param string $queueName
     * @return type
     */
    public function getMessage($queueName){
        return $this->client->receiveMessage(array(
            'QueueUrl' => $queueName,
        ));
    }
    
    /**
     * @param string $queueName
     * @param string $message
     * @return type
     */
    public function deleteMessage($queueName, $message){
        $msg = $message->getPath('Messages');
         return $this->client->deleteMessage(array(
            'QueueUrl'      => $this->getQueueLink($queueName),
            'ReceiptHandle' => $msg['ReceiptHandle']
        ));
    }
}