<?php

namespace ConsoleInvitation\Service;

class CreateDataInvitation implements \Zend\ServiceManager\ServiceLocatorAwareInterface {

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

    public function __construct(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        $this->setServiceLocator($serviceLocator);
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    private function getListPupilIdOfClass($classId) {
        $res = $this->getEntityManager()
                ->getRepository('\Application\Entity\Pupil')
                ->findBy(array('classId' => $classId, 'isDelete' => 0));
        $listId = array();
        foreach ($res as $pupil) {
            $listId[] = $pupil->getId();
        }
        return $listId;
    }

    /**
     * @param int $classId
     * @param \Application\Entity\InvitationSetting  $invitationSetting
     * @return array \Application\Entity\InvitationLetter
     */
    public function generateInvatationLetter($classId, $invitationSetting) {
        $listPupilId = $this->getListPupilIdOfClass($classId);
        $this->genAuthenKeyForPupil($listPupilId, $invitationSetting->getEikenScheduleId(), $invitationSetting->getOrganization());
        $em = $this->getEntityManager();
        // load helper invatation
        $msgHelper = \ConsoleInvitation\Helper\InvitationMsgHelper::getInstance($em, $this->getServiceLocator());

        $eikenSchedule = $invitationSetting->getEikenSchedule();

        $organization = $invitationSetting->getOrganization();

        $temp_invationmsg1 = (NULL != $invitationSetting->getTemplateInvitationMsgId1()) ? $em->getReference('Application\Entity\TemplateInvitationMsg', $invitationSetting->getTemplateInvitationMsgId1()) : NULL;
        $temp_invationmsg2 = (NULL != $invitationSetting->getTemplateInvitationMsgId2()) ? $em->getReference('Application\Entity\TemplateInvitationMsg', $invitationSetting->getTemplateInvitationMsgId2()) : NULL;
        $cityObj = (NULL != $organization->getcityId()) ? $em->getReference('Application\Entity\City', $organization->getcityId()) : NULL;

        foreach ($listPupilId as $pupilId) {
            $examDate1 = NULL;
            $examDate2 = NULL;

            if (empty($pupilId)) {
                continue;
            }

            $pupil = $em->getRepository('Application\Entity\Pupil')->findOneBy(array(
                'id' => $pupilId
            ));

            if (empty($pupil)) {
                continue;
            }

            //$recommend = $this->getRecomendedLevelMessage($pupilId, $eikenSchedule->getId());
            $recommend = $msgHelper->getRecomendedLevelMessage($pupilId, $eikenSchedule->getId());
            /* @var $inviLetter \Application\Entity\InvitationLetter */
            $inviLetter = $em->getRepository('Application\Entity\InvitationLetter')->findOneBy(array(
                'pupilId' => $pupilId,
                'eikenScheduleId' => $eikenSchedule->getId(),
                'isDelete' => 0
            ));
            // check exit Pupil
            $recommendLevelMsg = (NULL != $recommend) ? $em->getReference('Application\Entity\ConditionMessages', $recommend['id']) : NULL;
            /* @var $recommendLevel \Application\Entity\RecommendLevel */
            $recommendLevel = $em->getRepository('\Application\Entity\RecommendLevel')->findOneBy(array(
                'eikenScheduleId' => $eikenSchedule->getId(),
                'pupilId' => $pupilId
            ));

            if ($recommendLevel) {
                $eikenLevelId = $recommendLevel->getEikenLevelId();
            } else {
                $eikenLevelId = null;
            }

            // check update or insert
            if (!$inviLetter) {
                $inviLetter = new \Application\Entity\InvitationLetter();
            }
            // get examDate1
            $day = $invitationSetting->getExamDay();
            switch ($day) {
                case 1:
                    $examDate1 = $eikenSchedule->getFriDate();
                    break;
                case 2:
                    $examDate1 = $eikenSchedule->getSatDate();
                    break;
                case 3:
                    $examDate1 = $eikenSchedule->getSunDate();
                    break;
                case 4:
                    $examDate1 = $eikenSchedule->getFriDate();
                    $examDate2 = $eikenSchedule->getSatDate();
                    break;
            }
            $inviLetter->setExamDate1($examDate1);
            $inviLetter->setExamDate12($examDate2);

            $inviLetter->setHallType($invitationSetting->getHallType());
            $inviLetter->setOrganizationName($organization->getOrgNameKanji());
            $inviLetter->setNumberPupil($pupil->getNumber());

//            $inviLetter->setTitle('');
            if (0 == $inviLetter->getUpdateByHand()) {
                $inviLetter->setMessages1($invitationSetting->getMessage1());
                $inviLetter->setMessages2($invitationSetting->getMessage2());
            }
            if (NULL != $invitationSetting->getDoubleEikenMessagesId()) {
                $doubleEikenMes = $em->getRepository('Application\Entity\DoubleEikenMessages')->findOneBy(array(
                    'id' => $invitationSetting->getDoubleEikenMessagesId()
                ));
                $inviLetter->setDoubleExamMsgs($doubleEikenMes->getMessages());
            } else {
                $inviLetter->setDoubleExamMsgs($invitationSetting->getDoubleEikenMessage());
            }
            $inviLetter->setCombini($invitationSetting->getCombini());
            $inviLetter->setDeadline($invitationSetting->getDeadLine());
            $inviLetter->setExamPlace1($invitationSetting->getExamPlace());
            $inviLetter->setExamDate2($eikenSchedule->getRound2Day2ExamDate());
            $inviLetter->setEikenSchedule($eikenSchedule);
            $inviLetter->setPrintMessage($invitationSetting->isPrintMessage());
            $inviLetter->setPupil($pupil);
            $inviLetter->setCity($cityObj);
            $meritMsgId = $msgHelper->getAcquisitionMeritMessageId($eikenLevelId);
            if ($meritMsgId) {
                $acquisitionMerit = $em->getReference('Application\Entity\ConditionMessages', array(
                    'id' => $meritMsgId
                ));
                $inviLetter->setAcquisitionMerit($acquisitionMerit);
            } else {
                $inviLetter->setAcquisitionMerit(NULL);
            }

            $inviLetter->setRecommendLevelMsg($recommendLevelMsg);
            if ($recommendLevel) {
                $recomendLevelEikenLevel = $recommendLevel->getEikenLevel();

                if (!empty($recomendLevelEikenLevel)) {
                    $inviLetter->setRecommendLevelName($recomendLevelEikenLevel->getLevelName());
                }
                $inviLetter->setRecommendLevelId($recommendLevel->getId());
                $inviLetter->setRecommendLevel($recommendLevel);
            }

            $resultComment = $msgHelper->getResultCommentMessageId($pupilId);
            if (!empty($resultComment['primary'])) {
                $priResultComment = $em->getReference('Application\Entity\ResultsComment', array(
                    'id' => $resultComment['primary']
                ));
                $inviLetter->setResultComment($priResultComment);
            } else {
                $inviLetter->setResultComment(NULL);
            }

            if (!empty($resultComment['second'])) {
                $sceResultComment = $em->getReference('Application\Entity\ResultsComment', array(
                    'id' => $resultComment['second']
                ));
                $inviLetter->setResultComment2($sceResultComment);
            } else {
                $inviLetter->setResultComment2(NULL);
            }
            // questionguideld
            $questionGuideld = (NULL != $msgHelper->getQuestionGuildMessageId($eikenLevelId)) ? $em->getReference('Application\Entity\ConditionMessages', array(
                        'id' => $msgHelper->getQuestionGuildMessageId($eikenLevelId)
                    )) : NULL;
            $questionFormatld = (NULL != $msgHelper->getQuestionFormatMessageId($eikenLevelId)) ? $em->getReference('Application\Entity\ConditionMessages', array(
                        'id' => $msgHelper->getQuestionFormatMessageId($eikenLevelId)
                    )) : NULL;
            $inviLetter->setQuestionGuide($questionGuideld);
            $inviLetter->setQuestionFormat($questionFormatld);
            $inviLetter->setTemplate1($temp_invationmsg1);
            $inviLetter->setTemplate2($temp_invationmsg2);
            $inviLetter->setInvitationSetting($invitationSetting);
            $inviLetter->setPrintMessage($invitationSetting->isPrintMessage());
            // get candolist
            $canDoListRead = (NULL != $msgHelper->getCanDoListReadMessageId($eikenLevelId)) ? $em->getReference('Application\Entity\ConditionMessages', array(
                        'id' => $msgHelper->getCanDoListReadMessageId($eikenLevelId)
                    )) : NULL;
            $canDoListSpeak = (NULL != $msgHelper->getCanDoListSpeakMessageId($eikenLevelId)) ? $em->getReference('Application\Entity\ConditionMessages', array(
                        'id' => $msgHelper->getCanDoListSpeakMessageId($eikenLevelId)
                    )) : NULL;
            $canDoListWriten = (NULL != $msgHelper->getCanDoListWriteMessageId($eikenLevelId)) ? $em->getReference('Application\Entity\ConditionMessages', array(
                        'id' => $msgHelper->getCanDoListWriteMessageId($eikenLevelId)
                    )) : NULL;
            $canDoListListen = (NULL != $msgHelper->getCanDoListListenMessageId($eikenLevelId)) ? $em->getReference('Application\Entity\ConditionMessages', array(
                        'id' => $msgHelper->getCanDoListListenMessageId($eikenLevelId)
                    )) : NULL;
            // end
            $inviLetter->setCanDoListMessageReading($canDoListRead);
            $inviLetter->setCanDoListMessageSpeaking($canDoListSpeak);
            $inviLetter->setCanDoListMessageWriting($canDoListWriten);
            $inviLetter->setCanDoListMessageListening($canDoListListen);
            $em->persist($inviLetter);
            $em->flush();
        }
        $em->flush();
        $em->clear();
    }

    private function genAuthenKeyForPupil($listPupilId, $eikenScheduleId, $organization) {

        $countPupil = count($listPupilId);

        $listExistAuthenKey = $this->getListExistAuthenKey($organization->getId());

        $authenKeyExists = array();
        $authenKeys = array();
        foreach ($listExistAuthenKey as $authKey) {
            /* @var $authKey \Application\Entity\AuthenticationKey */
            $authenKeyExists[] = (int) $authKey['authenKey'];
            if($authKey['eikenScheduleId'] == $eikenScheduleId){
                $authenKeys[$authKey['pupilId']] = 1;
            }
        }

        $countBatch = 0;
        foreach ($listPupilId as $pupilId) {

            if (array_key_exists($pupilId, $authenKeys)) {
                continue;
            }
            $newAuthenKey = $this->getUniqueId($authenKeyExists);

            $pupil = $this->getEntityManager()->getReference('\Application\Entity\Pupil', $pupilId);

            $authenticationKey = new \Application\Entity\AuthenticationKey();
            $authenticationKey->setAuthenKey(sprintf("%'.08d", $newAuthenKey));
            $authenticationKey->setOrganizationNo($organization->getOrganizationNo());
            $authenticationKey->setPupil($pupil);
            $authenticationKey->setEikenSchedule($this->getEntityManager()->getReference('\Application\Entity\EikenSchedule', $eikenScheduleId));

            $this->getEntityManager()->persist($authenticationKey);
            $countBatch ++;

            if ($countBatch == 20) {
                $countBatch = 0;
                $this->getEntityManager()->flush();
                $this->getEntityManager()->clear('\Application\Entity\AuthenticationKey');
            }
        }
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear('\Application\Entity\AuthenticationKey');
    }

    private function getListExistAuthenKey($organizationId) {
        $dq = $this->getEntityManager()->createQueryBuilder();
        $dq->select('AuthenticationKey.authenKey,AuthenticationKey.eikenScheduleId,AuthenticationKey.pupilId')
                ->from('\Application\Entity\AuthenticationKey', 'AuthenticationKey')
                ->join('\Application\Entity\Pupil', 'Pupil'
                        , \Doctrine\ORM\Query\Expr\Join::WITH
                        , 'Pupil.id = AuthenticationKey.pupilId')
                ->where('Pupil.isDelete = 0 AND AuthenticationKey.isDelete = 0 '
                        . 'AND Pupil.organizationId = :orgId ')
                ->setParameter('orgId', $organizationId);
        return $dq->getQuery()->getArrayResult();
    }

    private function getUniqueId(&$existKey) {
        $number = rand(1, 99999999);
        while (in_array($number, $existKey)) {
            $number = rand(1, 99999999);
        }
        $existKey[] = $number;
        return $number;
    }

    private function getRecomendedLevelMessage($pupilId, $eikenScheduleId) {

        $recommendLevel = $this->getEntityManager()->getRepository('Application\Entity\RecommendLevel')->findOneBy(array(
            'pupilId' => $pupilId,
            'eikenScheduleId' => $eikenScheduleId
        ));

        if (empty($recommendLevel)) {
            return null;
        }

        $eikenRepository = $this->getEntityManager()->getRepository('Application\Entity\EikenTestResult');
        $ibaRepository = $this->getEntityManager()->getRepository('Application\Entity\IBATestResult');

        $eikenLevelId = $recommendLevel->getEikenLevelId();

        $eikenResult123 = $eikenRepository->getDataResultLastestByPupilIdAndType($pupilId, 0);
        $eikenResult45 = $eikenRepository->getDataResultLastestByPupilIdAndType($pupilId, 1);
        $ibaResult = $ibaRepository->getDataResultLastestByPupilId($pupilId);

        if ($eikenResult123 == Null && $eikenResult45 == Null && $ibaResult == Null) {
            //Eiken History and IBA of corresponding pupil is blank
            $condition = 'condition1';
        } else {

            $eikenDate123 = ($eikenResult123['secondCertificationDate'] != Null) ? $eikenResult123['secondCertificationDate']->format('Y-m-d') : '';
            $eikenDate45 = ($eikenResult45['certificationDate'] != Null) ? $eikenResult45['certificationDate']->format('Y-m-d') : '';
            $ibaDate = ($ibaResult["examDate"] != Null) ? $ibaResult["examDate"]->format('Y-m-d') : '';
            //get ekienLevel by date lastest of eiken or IBA
            $arrDate = array($eikenDate123, $eikenDate45, $ibaDate);

            if (max($arrDate) == $eikenDate123) {
                $level = $eikenResult123["eikenLevelId"];
            } else if (max($arrDate) == $eikenDate45) {
                $level = $eikenResult45["eikenLevelId"];
            } else {
                $level = $ibaResult["eikenLevelId"];
            }

            if ($eikenLevelId > $level) {
                //Recommended Level < Level thi latest của Eiken/IBA
                $condition = 'condition1';
            } elseif ($eikenLevelId < $level) {
                //Recommended Level > Level dự thi của latest IBA/Eiken
                $condition = 'condition2';
            } else {
                //Recommended Level = Level thi latest của Eiken/IBA
                $condition = 'condition3';
            }
        }

        $message = $this->getEntityManager()->getRepository('Application\Entity\ConditionMessages')->findOneBy(array(
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
        return null;
    }

}
