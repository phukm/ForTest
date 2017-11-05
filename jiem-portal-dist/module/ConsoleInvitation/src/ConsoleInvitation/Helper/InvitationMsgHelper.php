<?php

namespace ConsoleInvitation\Helper;

class InvitationMsgHelper {

    const QUESTION_GUILD_TYPE = 2;
    const QUESTION_FORMAT_TYPE = 3;
    const ACQUISITION_MERIT_TYPE = 4;
    const CAN_DO_LIST_READ_TYPE = 6;
    const CAN_DO_LIST_LISTEN_TYPE = 7;
    const CAN_DO_LIST_SPEAK_TYPE = 8;
    const CAN_DO_LIST_WRITE_TYPE = 9;
    const PASS_PRIMARY_EKIEN = 'condition1';
    // pass round 1
    const FAIL_PRIMARY_LEVEL_A_EKIEN = 'condition2';
    // fail level A round 1
    const FAIL_PRIMARY_LEVEL_B_EKIEN = 'condition3';
    // fail level B round 1
    const EXEMPTION_PRIMARY_EIKEN = 'condition4';
    // exempion round 1
    const PASS_SECCOND_EKIEN = 'condition5';
    // pass round 2
    const FAIL_SECCOND_LEVEL_A_EKIEN = 'condition6';
    // fail level A round 2
    const FAIL_SECCOND_LEVEL_B_EKIEN = 'condition7';
    // fail level B round 2
    const NO_CONTEST_EKIEN = 'condition8';

    // khong du thi
    protected static $instance = null;
    protected $em;
    protected $serviceManager;

    protected function __construct(\Doctrine\ORM\EntityManager $em, \Zend\ServiceManager\ServiceLocatorInterface $serviceManager) {
        $this->em = $em;
        $this->serviceManager = $serviceManager;
    }

    /**
     *
     * @return InvitationMsgHelper
     */
    public static function getInstance(\Doctrine\ORM\EntityManager $em, \Zend\ServiceManager\ServiceLocatorInterface $serviceManager) {
        if (NULL === self::$instance) {
            self::$instance = new self($em, $serviceManager);
        }
        return self::$instance;
    }

    protected $cacheMessageId = array();

    public function getListMessages() {
        if (!empty($this->cacheMessageId)) {
            return $this->cacheMessageId;
        }
        $mesages = $this->em->getRepository('\Application\Entity\ConditionMessages')->findAll();
        foreach ($mesages as $mesage) {
            $this->cacheMessageId[$mesage->getType()][$mesage->getEikenLevelId()] = $mesage->getId();
        }
        return $this->cacheMessageId;
    }

    public function getListResultComment() {
        $resultComment = array();
        $mesages = $this->em->getRepository('\Application\Entity\ResultsComment')->findAll();
        foreach ($mesages as $mesage) {
            $resultComment[$mesage->getCondition()] = $mesage->getId();
        }
        return $resultComment;
    }

    public function getQuestionGuildMessageId($eikenLevelId) {
        $listMessages = $this->getListMessages();
        return array_key_exists(self::QUESTION_GUILD_TYPE, $listMessages) ? (array_key_exists($eikenLevelId, $listMessages[self::QUESTION_GUILD_TYPE]) ? $listMessages[self::QUESTION_GUILD_TYPE][$eikenLevelId] : null) : null;
    }

    public function getQuestionFormatMessageId($eikenLevelId) {
        $listMessages = $this->getListMessages();
        return array_key_exists(self::QUESTION_FORMAT_TYPE, $listMessages) ? (array_key_exists($eikenLevelId, $listMessages[self::QUESTION_FORMAT_TYPE]) ? $listMessages[self::QUESTION_FORMAT_TYPE][$eikenLevelId] : null) : null;
    }

    public function getAcquisitionMeritMessageId($eikenLevelId) {
        $listMessages = $this->getListMessages();
        return array_key_exists(self::ACQUISITION_MERIT_TYPE, $listMessages) ? (array_key_exists($eikenLevelId, $listMessages[self::ACQUISITION_MERIT_TYPE]) ? $listMessages[self::ACQUISITION_MERIT_TYPE][$eikenLevelId] : null) : null;
    }

    public function getCanDoListListenMessageId($eikenLevelId) {
        $listMessages = $this->getListMessages();
        return array_key_exists(self::CAN_DO_LIST_LISTEN_TYPE, $listMessages) ? (array_key_exists($eikenLevelId, $listMessages[self::CAN_DO_LIST_LISTEN_TYPE]) ? $listMessages[self::CAN_DO_LIST_LISTEN_TYPE][$eikenLevelId] : null) : null;
    }

    public function getCanDoListReadMessageId($eikenLevelId) {
        $listMessages = $this->getListMessages();
        return array_key_exists(self::CAN_DO_LIST_READ_TYPE, $listMessages) ? (array_key_exists($eikenLevelId, $listMessages[self::CAN_DO_LIST_READ_TYPE]) ? $listMessages[self::CAN_DO_LIST_READ_TYPE][$eikenLevelId] : null) : null;
    }

    public function getCanDoListSpeakMessageId($eikenLevelId) {
        $listMessages = $this->getListMessages();
        return array_key_exists(self::CAN_DO_LIST_SPEAK_TYPE, $listMessages) ? (array_key_exists($eikenLevelId, $listMessages[self::CAN_DO_LIST_SPEAK_TYPE]) ? $listMessages[self::CAN_DO_LIST_SPEAK_TYPE][$eikenLevelId] : null) : null;
    }

    public function getCanDoListWriteMessageId($eikenLevelId) {
        $listMessages = $this->getListMessages();
        return array_key_exists(self::CAN_DO_LIST_WRITE_TYPE, $listMessages) ? (array_key_exists($eikenLevelId, $listMessages[self::CAN_DO_LIST_WRITE_TYPE]) ? $listMessages[self::CAN_DO_LIST_WRITE_TYPE][$eikenLevelId] : null) : null;
    }

    public function getResultCommentMessageId($pupilId) {

        $messagesId = array("primary" => "", "second" => "");

        $testResultClass = \ConsoleInvitation\Helper\TestResultHelper::getInstance($this->em, $this->serviceManager);
        $testResult = $testResultClass->getLastestTestResult($pupilId);

        if ($testResult === Null) {
            return $messagesId;
        }
        if ($testResult instanceof \Application\Entity\EikenTestResult) {
            /* @var $testResult \Application\Entity\EikenTestResult */
            $resultComment = $this->getListResultComment();

            if ($testResult->getOneExemptionFlag() == 1) {
                $messagesId["primary"] = !empty($resultComment[self::EXEMPTION_PRIMARY_EIKEN]) ? $resultComment[self::EXEMPTION_PRIMARY_EIKEN] : "";
            } else if ($testResult->getPrimaryPassFailFlag() == 1) {
                $messagesId["primary"] = !empty($resultComment[self::PASS_PRIMARY_EKIEN]) ? $resultComment[self::PASS_PRIMARY_EKIEN] : "";
            } else if ($testResult->getPrimaryFailureLevel() == 'A') {
                $messagesId["primary"] = !empty($resultComment[self::FAIL_PRIMARY_LEVEL_A_EKIEN]) ? $resultComment[self::FAIL_PRIMARY_LEVEL_A_EKIEN] : "";
            } else if ($testResult->getPrimaryFailureLevel() == 'B') {
                $messagesId["primary"] = !empty($resultComment[self::FAIL_PRIMARY_LEVEL_B_EKIEN]) ? $resultComment[self::FAIL_PRIMARY_LEVEL_B_EKIEN] : "";
            }

            if ($testResult->getEikenLevelId() <= 5) {
                if ($testResult->getSecondPassFailFlag() == 1) {
                    $messagesId["second"] = !empty($resultComment[self::PASS_SECCOND_EKIEN]) ? $resultComment[self::PASS_SECCOND_EKIEN] : "";
                } else if ($testResult->getSecondPassFailFlag() === Null) {
                    $messagesId["second"] = !empty($resultComment[self::NO_CONTEST_EKIEN]) ? $resultComment[self::NO_CONTEST_EKIEN] : "";
                } else if ($testResult->getSecondUnacceptableLevel() == 'A') {
                    $messagesId["second"] = !empty($resultComment[self::FAIL_SECCOND_LEVEL_A_EKIEN]) ? $resultComment[self::FAIL_SECCOND_LEVEL_A_EKIEN] : "";
                } else if ($testResult->getSecondUnacceptableLevel() == 'B') {
                    $messagesId["second"] = !empty($resultComment[self::FAIL_SECCOND_LEVEL_B_EKIEN]) ? $resultComment[self::FAIL_SECCOND_LEVEL_B_EKIEN] : "";
                }
            }
        }

        return $messagesId;
    }

    public function getRecomendedLevelMessage($pupilId, $eikenScheduleId) {

        $recommendLevel = $this->em->getRepository('Application\Entity\RecommendLevel')->findOneBy(array(
            'pupilId' => $pupilId,
            'eikenScheduleId' => $eikenScheduleId
        ));
        if ($recommendLevel === Null) {
            return Null;
        }
        $eikenLevelId = $recommendLevel->getEikenLevelId();
        $testResultClass = \ConsoleInvitation\Helper\TestResultHelper::getInstance($this->em, $this->serviceManager);
        $testResult = $testResultClass->getLastestTestResult($pupilId);

        if ($testResult != Null) {
            //$testResult is EikenTestResult or IBATestResult;
            $level = $testResult->getEikenLevelId() != Null ? $testResult->getEikenLevelId() : 0;
            if ($eikenLevelId > $level) {
                //Recommended Level < Level thi latest của Eiken/IBA
                $condition = 'condition1';
            } else if ($eikenLevelId < $level) {
                //Recommended Level > Level dự thi của latest IBA/Eiken
                $condition = 'condition2';
            } else {
                //Recommended Level = Level thi latest của Eiken/IBA
                $condition = 'condition3';
            }
        } else {
            $condition = 'condition1';
        }
        /* @var $message \Application\Entity\ConditionMessages */
        $message = $this->em->getRepository('Application\Entity\ConditionMessages')->findOneBy(array(
            'eikenLevelId' => $eikenLevelId,
            'type' => 1,
            'condition' => $condition
        ));
        if ($message != Null) {
            return array(
                'id' => $message->getId(),
                'messages' => $message->getMessages()
            );
        }
        return Null;
    }

}
