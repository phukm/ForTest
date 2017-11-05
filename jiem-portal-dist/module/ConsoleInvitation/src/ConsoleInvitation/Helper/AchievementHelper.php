<?php

/**
 * @author minhbn1<minhbn1@fsoft.com.vn>
 */

namespace ConsoleInvitation\Helper;

class AchievementHelper {

    protected $sqs;
    protected $sqsType;

    const QUEUE_RANGE_LIMIT = 100;
    const QUEUE_LIST_LIMIT = 2;

    function __construct() {
        $this->sqs = \Dantai\Aws\AwsSqsClient::getInstance();
        $this->sqsType = \Dantai\Aws\AwsSqsClient::QUEUE_ANALYSEACHIEVEMENT_COMBIBI;
    }

    /**
     * Add Org Id to sqs queue
     * 
     * @param type $orgId
     * @return boolean
     */
    function addOrgToQueue($orgId = 0) {
        $orgId = (int) $orgId;
        if (!$orgId)
            return false;
        $this->sqs->sendMessage(array(
            'MessageBody' => \Zend\Json\Encoder::encode(array(
                'orgId' => $orgId
            )),
            'QueueUrl' => $this->sqsType,
        ));
        return true;
    }

    /**
     * get org id in sqs queue
     * 
     * @return mix
     */
    function getOrgInQueue() {
        $message = $this->sqs->receiveMessage($this->sqsType);
        if ($message) {
            $orgInfo = \Zend\Json\Json::decode($message['Body'], \Zend\Json\Json::TYPE_ARRAY);
            if ($orgInfo && isset($orgInfo['orgId']))
                return (int) $orgInfo['orgId'];
        }
        return null;
    }

    /**
     * get list org in queue
     * @param type $limit
     * @return array
     */
    function getListOrgInQueue($limit = 0) {
        $limit = (int) $limit;
        $results = array();
        if (!$limit)
            return $results;
        for ($i = 0; $i < $limit; $i++) {
            $orgId = $this->getOrgInQueue();
            if ($orgId)
                $results[] = $orgId;
        }
        return $results;
    }

}
