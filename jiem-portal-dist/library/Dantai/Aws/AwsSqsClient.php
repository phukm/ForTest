<?php
namespace Dantai\Aws;

use Aws\Sqs\SqsClient;
use Prophecy\Exception\Exception;

class AwsSqsClient
{

    const QUEUE_GEN_INVITATION = 'DantaiGenInvitation';

    const QUEUE_GEN_COMBIBI = 'DantaiGenCombini';

    const QUEUE_ANALYSEACHIEVEMENT_COMBIBI = 'DantaiAnalyseAchievement';
    
    const QUEUE_AUTORUN_MAPPING = 'DantaiAutoRunMapping';

    protected $queueUrls = array();

    /**
     * Client for AmazonSes
     *
     * @var \Aws\Sqs\SqsClient
     */
    protected $sqsClient;

    protected $sqsConfig;
    
    /**
     *
     * @var \Dantai\Aws\AwsSqsClient
     */
    protected static $_instance = null;

    /**
     * Singleton
     *
     * @return \Dantai\Aws\AwsSqsClient
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }

    protected function __construct()
    {
        $this->sqsConfig = Config::getConfig(Config::AWS_SQS);
        $this->queueUrls;
        $configStaging = Config::getConfigStaging(Config::AWS_SQS);
        foreach($configStaging['sqsUrl'] as $url){
            $this->queueUrls[$url] = '';
        }
        $this->getClient();
    }

    public function getClient()
    {
        if (!$this->sqsClient) {
            $this->sqsClient = new \Aws\Sqs\SqsClient($this->sqsConfig);
            $queueUrls = $this->sqsClient->listQueues(array(
                'QueueNamePrefix' => 'Dantai'
            ));
            
            $aryQueues = $queueUrls->toArray()['QueueUrls'];
            $urlCount = count($aryQueues);
            $urls = array();
            for ($i = 0; $i < $urlCount; $i ++) {
                $queueName = explode('/', $aryQueues[$i]);
                $queueName = end($queueName);
                $urls[$queueName] = $aryQueues[$i];
            }
            $queueUrls = $urls;
            
            // Initialize queue urls
            foreach ($this->queueUrls as $key => $value) {
                if (array_key_exists($key, $queueUrls)) {
                    $this->queueUrls[$key] = $queueUrls[$key];
                } else {
                    $this->queueUrls[$key] = $this->createQueue($key, array('VisibilityTimeout' => 540));
                }
            }
        }
        
        return $this->sqsClient;
    }
    
    public function getQueueNameByConfig($queueName){
        return Config::getConfigStaging(Config::AWS_SQS)['sqsUrl'][$queueName];
    }

    public function createQueue($queueName, $attributes = array())
    {
        $result = $this->getClient()->createQueue(array(
            'QueueName' => $queueName,
            'Attributes' => $attributes
        ));
        return $result->get('QueueUrl');
    }

    /**
     * Get queue amazon url
     *
     * @param unknown $queueName            
     * @return unknown
     */
    public function getQueueUrl($queueName)
    {
        $result = $this->getClient()->getQueueUrl(array(
            'QueueName' => $this->getQueueNameByConfig($queueName)
        ));
        return $result;
    }

    /**
     * Send a message to a queue
     *
     * @param array $message
     *            [
     *            'DelaySeconds' => <integer>,
     *            'MessageAttributes' => [
     *            '<String>' => [
     *            'BinaryListValues' => [<Psr\Http\Message\StreamableInterface>, ...],
     *            'BinaryValue' => <Psr\Http\Message\StreamableInterface>,
     *            'DataType' => '<string>', // REQUIRED
     *            'StringListValues' => ['<string>', ...],
     *            'StringValue' => '<string>',
     *            ],
     *            // ...
     *            ],
     *            'MessageBody' => '<string>', // REQUIRED
     *            'QueueUrl' => '<string>', // REQUIRED
     *            ]
     */
    public function sendMessage($message)
    {
        $message['QueueUrl'] = $this->queueUrls[$this->getQueueNameByConfig($message['QueueUrl'])];
        $result = $this->getClient()->sendMessage($message);
        
        return $result;
    }

    /**
     * Send a batch of messages to a queue
     *
     * @param array $message
     *            in format array(
     *            'Entries' => [ // REQUIRED
     *            [
     *            'DelaySeconds' => <integer>,
     *            'Id' => '<string>', // REQUIRED
     *            'MessageAttributes' => [
     *            '<String>' => [
     *            'BinaryListValues' => [<Psr\Http\Message\StreamableInterface>, ...],
     *            'BinaryValue' => <Psr\Http\Message\StreamableInterface>,
     *            'DataType' => '<string>', // REQUIRED
     *            'StringListValues' => ['<string>', ...],
     *            'StringValue' => '<string>',
     *            ],
     *            // ...
     *            ],
     *            'MessageBody' => '<string>', // REQUIRED
     *            ],
     *            // ...
     *            ],
     *            'QueueUrl' => '<string>', // REQUIRED
     *            )
     * @return array
     */
    public function sendMessageBatch($messages)
    {
        $messages['QueueUrl'] = $this->queueUrls[$this->getQueueNameByConfig($messages['QueueUrl'])];
        
        $result = $this->getClient()->sendMessageBatch($messages);
        
        return $result;
    }

    /**
     * Recieve one message from given queue, if queue is empty, function will wait for 5 seconds before returning result
     *
     * @param string $queueName            
     * @param string $attributes            
     * @param number $visibilityTimeout            
     * @param number $waitTimeSeconds            
     * @return Array (
     *         [MessageId] => '08146615-b1f1-4c7d-a7f2-6173be4fd955'
     *         [ReceiptHandle] => 'AQEBGbrXh3pv+UazexdZwkATCRYOaQ7IJF9icdzaQVddF2yM4Hc2IoNyWHVAk6Zx5Iqa2XU18c/ZNonXH9l1VWG+7BtaIkFq9Jka8y/zQMYLQP2BtkQ2z+6g0q2ZiFrXWkXq58vqYLN/xfwkmUfaDylkhsd/BvMSJJF9OXBooyWA9UFlzQbEhZupXWOCaAxWlHBhbU59UasElWYQk4G+8mtiJ4l+z828TeRv+3vlGJAFhXekZS3EWtP3wkhCcDvWOD4ZJ9yq7vI0QHYxYHpY7zOyo7nN5ndqtYfIl693llVETRCkOd1OmTSUkFLU9ZFhQj1YYVUqJaqHv38HYj8G1rYFbZ6JoR08pR5PWLjpASfMTUE='
     *         [MD5OfBody] => 'dff5c1cdfd81a3734b0ed5990c3f0345'
     *         [Body] => {"classId":"StringValue", "startIndex":"IntValue"}
     *         )|
     *         null
     */
    public function receiveMessage($queueName, $attributes = array(), $visibilityTimeout = 120, $waitTimeSeconds = 5)
    {
        $result = $this->getClient()->receiveMessage(array(
            'QueueUrl' => $this->queueUrls[$this->getQueueNameByConfig($queueName)],
            'AttributeNames' => $attributes,
            // 'MessageAttributeNames' => array('string', ... ),
            'MaxNumberOfMessages' => 1,
            'VisibilityTimeout' => $visibilityTimeout,
            'WaitTimeSeconds' => $waitTimeSeconds
        ));
        
        if ($result['@metadata']['statusCode'] != 200 || ! isset($result['Messages'])) {
            return null;
        } else {
            return $result['Messages'][0];
        }
    }

    public function changeMessageVisibility($queueUrl, $receiptHandle, $visibilityTimeout = 120)
    {
        try {
            $this->getClient()->changeMessageVisibility([
                'QueueUrl' => $this->queueUrls[$this->getQueueNameByConfig($queueUrl)],
                'ReceiptHandle' => $receiptHandle,
                'VisibilityTimeout' => $visibilityTimeout
            ]);
        } catch (Exception $e) {
            // TODO log this error or report to system admin
            // Got one of exceptions:
            // - MessageNotInflight
            // - ReceiptHandleIsInvalid
        }
    }

    public function deleteMessage($queueUrl, $receiptHandle)
    {
        $result = $this->getClient()->deleteMessage(array(
            'QueueUrl' => $this->queueUrls[$this->getQueueNameByConfig($queueUrl)],
            'ReceiptHandle' => $receiptHandle
        ));
        
        return $result;
    }

    /**
     * Delete message batch
     *
     * @param unknown $queueUrl            
     * @param unknown $receiptHandle
     *            array( // REQUIRED
     *            array(
     *            'Id' => '<string>', // REQUIRED
     *            'ReceiptHandle' => '<string>', // REQUIRED
     *            ),
     *            // ...
     *            ),
     * @return unknown
     */
    public function deleteMessageBatch($queueUrl, $receiptHandle)
    {
        // TODO function body will be implemented here...
        return true;
    }
}
