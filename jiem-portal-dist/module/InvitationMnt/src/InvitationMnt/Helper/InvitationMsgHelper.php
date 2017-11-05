<?php

namespace InvitationMnt\Helper;

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

    protected function __construct(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
    }

    /**
     *
     * @return InvitationMessageHelper
     */
    public static function getInstance(\Doctrine\ORM\EntityManager $em) {
        if (NULL === self::$instance) {
            self::$instance = new self($em);
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

    public function getResultCommentMessageId($pupilId, $eikenScheduleId) {
        $messagesId = array("primary" => "","second" => "");
        /* @var $eikenTestResult \Application\Entity\EikenTestResult */
        $eikenTestResult = $this->em->getRepository('\Application\Entity\EikenTestResult')->findOneBy(array(
            "pupilId" => $pupilId,
            "eikenScheduleId" => $eikenScheduleId
        ));
        /* @var $ibaTestResult \Application\Entity\IBATestResult */
        $ibaTestResult = $this->em->getRepository('\Application\Entity\IBATestResult')->findOneBy(array(
            "pupilId" => $pupilId,
            "eikenScheduleId" => $eikenScheduleId
        ));

        if ($eikenTestResult != Null) {
            $examDateIBA = '';
            if($ibaTestResult != Null){
                $examDateIBA = $ibaTestResult->getExamDate() != Null ? $ibaTestResult->getExamDate()->format('Y-m-d') : '';
            }    

            if (in_array($eikenTestResult->getEikenLevelId(), array(1, 2, 3, 4, 5))) {
                $examDateEiken = $eikenTestResult->getSecondCertificationDate() != Null ? $eikenTestResult->getSecondCertificationDate()->format('Y-m-d') : '';
            } else {
                $examDateEiken = $eikenTestResult->getCertificationDate() != Null ? $eikenTestResult->getCertificationDate()->format('Y-m-d') : '';
            }

            if ($examDateEiken > $examDateIBA) {
                $resultComment = $this->getListResultComment();
                if ($eikenTestResult->getPrimaryPassFailFlag() == 1) {
                    $messagesId["primary"] = !empty($resultComment[self::PASS_PRIMARY_EKIEN]) ? $resultComment[self::PASS_PRIMARY_EKIEN] : "";
                } else if ($eikenTestResult->getPrimaryFailureLevel() == 'A') {
                    $messagesId["primary"] = !empty($resultComment[self::FAIL_PRIMARY_LEVEL_A_EKIEN]) ? $resultComment[self::FAIL_PRIMARY_LEVEL_A_EKIEN] : "";
                } else if ($eikenTestResult->getPrimaryFailureLevel() == 'B') {
                    $messagesId["primary"] = !empty($resultComment[self::FAIL_PRIMARY_LEVEL_B_EKIEN]) ? $resultComment[self::FAIL_PRIMARY_LEVEL_B_EKIEN] : "";
                } else if ($eikenTestResult->getOneExemptionFlag() == 1) {
                    $messagesId["primary"] = !empty($resultComment[self::EXEMPTION_PRIMARY_EIKEN]) ? $resultComment[self::EXEMPTION_PRIMARY_EIKEN] : "";
                }

                if ($eikenTestResult->getSecondPassFailFlag() == 1) {
                    $messagesId["second"] = !empty($resultComment[self::PASS_SECCOND_EKIEN]) ? $resultComment[self::PASS_SECCOND_EKIEN] : "";
                } else if ($eikenTestResult->getSecondPassFailFlag() === NULL) {
                    $messagesId["second"] = !empty($resultComment[self::NO_CONTEST_EKIEN]) ? $resultComment[self::NO_CONTEST_EKIEN] : "";
                } else if ($eikenTestResult->getSecondUnacceptableLevel() == 'A') {
                    $messagesId["second"] = !empty($resultComment[self::FAIL_SECCOND_LEVEL_A_EKIEN]) ? $resultComment[self::FAIL_SECCOND_LEVEL_A_EKIEN] : "";
                } else if ($eikenTestResult->getSecondUnacceptableLevel() == 'B') {
                    $messagesId["second"] = !empty($resultComment[self::FAIL_SECCOND_LEVEL_B_EKIEN]) ? $resultComment[self::FAIL_SECCOND_LEVEL_B_EKIEN] : "";
                }
            }
        }

        return $messagesId;
    }

}
