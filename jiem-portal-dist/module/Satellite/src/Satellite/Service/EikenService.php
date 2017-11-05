<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Satellite\Service;


use Application\Entity\ApplyEikenPersonalInfo;
use Application\Entity\InvitationSetting;
use Zend\Di\ServiceLocator;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Satellite\Service\ServiceInterface\EikenServiceInterface;
use Satellite\Constants;
use Application\Entity\ApplyEikenLevel;
use Dantai\PrivateSession;
use Application\Service\DantaiService;
use Zend\Session\Container as SessionContainer;

class EikenService implements EikenServiceInterface, ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    private $entityManager;
    
    private $examGrade = array(
        '1級' => 1,
        '準1級' => 2,
        '2級' => 3,
        '準2級' => 4,
        '3級' => 5,
        '4級' => 6,
        '5級' => 7
    );
    
    public function getListPaidApplyEiken($pupilId, $scheduleId, $listKyu, $listKyuPrice) {
        $listPaid = array();
        
        foreach ($listKyu as $kyu) {
            $paidApplyEiken = $this->getPaidApplyEiken($pupilId, $scheduleId, $kyu, $listKyuPrice[$kyu]['price']);
            if(!empty($paidApplyEiken)){
                array_push($listPaid, $paidApplyEiken->getEikenLevel()->getId());
            }
        }
        
        return $listPaid;
    }
    
    public function getPaidApplyEiken($pupilId, $scheduleId, $kyu, $price) {
        $em = $this->getEntityManager();
        $paidApplyEiken = $em->getRepository('Application\Entity\ApplyEikenLevel')
                ->findOneBy(array(
                    'pupilId' => $pupilId,
                    'eikenScheduleId' => $scheduleId,
                    'eikenLevelId' => $kyu,
                    'tuitionFee' => $price,
                    'paymentStatus' => 1,
                ));
        if(empty($paidApplyEiken)){
            $paidApplyEiken =  $em->getRepository('Application\Entity\ApplyEikenLevel')
                ->findOneBy(array(
                    'pupilId' => $pupilId,
                    'eikenScheduleId' => $scheduleId,
                    'eikenLevelId' => $kyu,
                    'paymentStatus' => 1,
                ));
        }
        return $paidApplyEiken;
    }

    public function savePersonalInfo($orgId, $scheduleId, $currentPupil)
    {
        $em = $this->getEntityManager();
        $kyuNumber = 0;
        $testSite = 0;
        $data = PrivateSession::getData(Constants::SESSION_APPLYEIKEN);
        $dataExemption = PrivateSession::getData(Constants::DATA_TEST_SITE_EXEMPTION);
        $userIdentity = PrivateSession::getData(Constants::SESSION_SATELLITE);
        $dantaiService = new DantaiService();

        $prePersonalPayment = $userIdentity['personalPayment'];
        $returnData['isSupportCredit'] = (strpos($prePersonalPayment, '0') !== false);
        $returnData['isSupportConbini'] = (strpos($prePersonalPayment, '1') !== false);
        $returnData['deadline'] = date('Y/m/d', strtotime($userIdentity['deadline']['deadlineTo']->format('Y/m/d')));
        $returnData['combiniDeadline'] = date('Y/m/d', strtotime($userIdentity['deadline']['combiniDeadline']->format('Y/m/d')));
        $returnData['creditDeadline'] = date('Y/m/d', strtotime($userIdentity['deadline']['creditDeadline']->format('Y/m/d')));

        $data['txtPhoneNo'] = $data['txtPhoneNo1'].'-'.$data['txtPhoneNo2'].'-'.$data['txtPhoneNo3'];

        $returnData['applyEikenLevelId'] = array();
        if(!empty($data['chooseKyu'])){
            $applyEikenNumber = $this->getApplyEikenLevels($currentPupil['pupilId'], $scheduleId);
            if($applyEikenNumber && $currentPupil['doubleEiken'] == Constants::DOUBLE_EIKEN){
                return array('status' => false, 'message' => $this->translate('geKyuPaymentMSG56'));
            }
            //fix bug apply by two tab
            if (count($applyEikenNumber) >= 2 || ($applyEikenNumber && count($data['chooseKyu']) >= 2)){
                return array('status' => false, 'message' => $this->translate('geKyuPaymentMSG56'));
            }
            if($applyEikenNumber && abs($applyEikenNumber[0]->getEikenLevelId() - $data['chooseKyu'][0]) != 1){
                return array('status' => false, 'message' => $this->translate('MSG6'));
            }

            // validate all data before save.
            $doubleEikenNotSupport = 0;
            if (isset($data['doubleEiken']) && $data['doubleEiken'] == Constants::DOUBLE_EIKEN) {
                $doubleEikenNotSupport = 1;
            }
            
            
            $partFolder = $userIdentity['deadline']['year'] . $userIdentity['deadline']['kai'] . "/" . $userIdentity['organizationNo'];                
            $logData = array_merge((array)$data,(array)$dataExemption);
            $dantaiService->writeLog(\Satellite\Constants::LOG_APPLY_EIKEN_JUKENSHA, $partFolder, $logData, 'saveAction', 'DATA BEFORE LATEST VALIDATE FOR DANTAINO : ' . $userIdentity['organizationNo'] . ' PUPILID : ' . $userIdentity['pupilId']);
            
            if(!empty($this->validateApplyEikenAndTestSiteExemption($data, $dataExemption, $doubleEikenNotSupport))){
                return array('status' => false, 'message' => $this->validateApplyEikenAndTestSiteExemption($data, $dataExemption, $doubleEikenNotSupport));
            }
            
            $returnData['status'] = true;
            // get list paid apply eiken before submit
            $listPaid = array();
            if($returnData['isSupportConbini']){
                $listPaid = $this->getListPaidApplyEiken($currentPupil['pupilId'], $scheduleId, $data['chooseKyu'], $data['kyu']);
            }
            $returnData['listPaid'] = $listPaid;

            foreach ($data['chooseKyu'] as $eikenLevel){
                $data['eikenLevelId'] = $eikenLevel;
                $price  = $data['kyu'][$eikenLevel]['price'];
                if ($data['kyu'][$eikenLevel]['hallType'] == 1)
                {
                    $hallType = 1;
                    $testSite = 1;
                }
                else{
                    $hallType = 0;
                }
                $applyEikenPersonalInfoSession = new SessionContainer('ApplyEikenPersonalInfoSession');
                $this->setSessionData($applyEikenPersonalInfoSession, $data);
                $applyEikenPersonalInfoSession->infoData->setOrganization = $orgId;
                
                $dantaiService->writeLog(\Satellite\Constants::LOG_APPLY_EIKEN_JUKENSHA, $partFolder, $logData, 'saveAction', 'DATA BEFORE SAVE FOR DANTAINO : ' . $userIdentity['organizationNo'] . ' PUPILID : ' . $userIdentity['pupilId']);
                //main hall
                if ($hallType == 1){
                    // Call API here
                    $currentEikenInfo =  $em->getRepository('Application\Entity\ApplyEikenPersonalInfo')->findOneBy(array(
                        'pupilId' => $currentPupil['pupilId'],
                        'isDelete' => 0
                    ));

                    if (!$currentEikenInfo || !$currentEikenInfo->getEikenId()){
                        // Preapare data
                        $data['txtEikenPassword'] = $this->random_password();
                        $newEikenId = $this->getNewEikenIdApi($data, $currentPupil);
                    }
                    else
                    {
                        $newEikenId = $currentEikenInfo->getEikenId();
                        $data['txtEikenPassword'] = $currentEikenInfo->getEikenPassword();
                    }

                    $applyEikenPersonalInfoSession->infoData->setEikenId = $newEikenId;
                    $applyEikenPersonalInfoSession->infoData->setEikenPassword = $data['txtEikenPassword'];
                    $returnData['eikenId'] = $newEikenId;
                    $returnData['eikenPassword'] = $data['txtEikenPassword'];

                    $kyuNumber++;

                    if ($kyuNumber <2)
                    {
                        // Save personal info to DB
                        $storePersonInfoResult = $this->storeApplyPersonalInfo($applyEikenPersonalInfoSession, $scheduleId, $currentPupil, $hallType);
                        $applyEikenPersonalInfoSession->infoData->id = $storePersonInfoResult['infoId'];

                        // Validate rules: One EikenId can apply with 2 continuos Kyu
                        $em = $this->getEntityManager();
                        $isValidEikenLevel = $em->getRepository('Application\Entity\ApplyEikenLevel')->checkValidEikenLevel(
                            $applyEikenPersonalInfoSession->infoData->setEikenId,
                            $applyEikenPersonalInfoSession->eikenLevelId,
                            $applyEikenPersonalInfoSession->applyEikenLevelId,
                            $scheduleId
                        );
                    }
                    // Add new record to ApplyEikenLevel
                    // Change the logic enable/disable of 2 buttons: check/get EikenId

                    if ($isValidEikenLevel)
                    {
                        $applyEikenPersonalInfoSession->applyEikenLevelId = $this->updateApplyEikenLevel(
                            $applyEikenPersonalInfoSession->infoData->id,
                            $scheduleId,
                            $applyEikenPersonalInfoSession->eikenLevelId,
                            $applyEikenPersonalInfoSession->applyEikenLevelId,
                            $storePersonInfoResult['originalEikenId'],
                            $hallType,
                            $currentPupil,
                            $dataExemption,
                            $kyuNumber,
                            $price
                        );
                    }
                }
                else {
                    $storePersonInfoResult = $this->storeApplyPersonalInfo($applyEikenPersonalInfoSession, $scheduleId, $currentPupil, $hallType);
                    $applyEikenPersonalInfoSession->infoData->id = $storePersonInfoResult['infoId'];

                    $applyEikenPersonalInfoSession->applyEikenLevelId = $this->updateApplyEikenLevel(
                        $applyEikenPersonalInfoSession->infoData->id,
                        $scheduleId,
                        $data['eikenLevelId'],
                        0,
                        null,
                        $hallType,
                        $currentPupil,
                        null,
                        null,
                        $price
                    );
                }
                if (!empty($applyEikenPersonalInfoSession->applyEikenLevelId)) array_push ($returnData['applyEikenLevelId'], $applyEikenPersonalInfoSession->applyEikenLevelId);

            }
        }else{
            return array('status' => false, 'message' => $this->translate('MSG_RECHECK_DATA_STUDENT_APPLY').'<br>'.$this->translate('chooseKyu'));
        }

        PrivateSession::clear(Constants::SESSION_APPLYEIKEN);
        PrivateSession::clear(Constants::DATA_TEST_SITE_EXEMPTION);
        $returnData['testSite'] = $testSite;
        $returnData['chooseKyu'] = isset($data['chooseKyu']) ? array_values(array_diff($data['chooseKyu'], $listPaid)) : array();
        $returnData['kyuInfo'] = isset($data['kyu']) ? $data['kyu'] : array();

        return $returnData;

    }

    public function createResponseMessage($returnData)
    {
        $isCredit = $returnData['isSupportCredit'];
        $isCombini = $returnData['isSupportConbini'];
        $isIndividual = $isCredit || $isCombini;
        $testSide = $returnData['testSite'];
        $listPaid = $returnData['listPaid'];
        $chooseKyu = $returnData['chooseKyu'];
        $eikenId = isset($returnData['eikenId']) ? $returnData['eikenId'] : null;
        $eikenPassword = isset($returnData['eikenPassword']) ? $returnData['eikenPassword'] : null;
        $kyuInfoList = $returnData['kyuInfo'];
        // Set response message:
        // main hall + collective payment
        if ($testSide == 1 && !$isIndividual) {
            $returnData['message'] = sprintf($this->translate('R4_MSG32'), $eikenId, $eikenPassword);
        }
        // main hall + individual payment + not paid before
        else if ($testSide == 1 && $isIndividual && count($listPaid) == 0){
            if ($isCombini && $isCredit) {
                $returnData['message'] = sprintf($this->translate('msgMainBothCombiniCredit'), $eikenId, $eikenPassword, date('Y/m/d', strtotime($returnData['combiniDeadline'])), date('Y/m/d', strtotime($returnData['creditDeadline'])));
            } elseif ($isCombini) {
                $returnData['message'] = sprintf($this->translate('msgMainOnlyCombiniOrCredit'), $eikenId, $eikenPassword, date('Y/m/d', strtotime($returnData['combiniDeadline'])));
            } elseif ($isCredit) {
                $returnData['message'] = sprintf($this->translate('msgMainOnlyCombiniOrCredit'), $eikenId, $eikenPassword, date('Y/m/d', strtotime($returnData['creditDeadline'])));
            }
            //$returnData['message'] = sprintf($this->translate('R4_MSG27'), $eikenId, $eikenPassword, date('Y/m/d',strtotime($returnData['combiniDeadline'])),  date('Y/m/d',strtotime($returnData['creditDeadline'])));
        }
        // main hall + individual payment + paid all kyu before
        else if($testSide == 1 && $isIndividual && count($chooseKyu) == 0 && count($listPaid) > 0){
            $returnData['message'] = sprintf($this->translate('R4_MSG32'), $eikenId, $eikenPassword);
            $returnData['message2'] = $this->createResponseMessageForPopup2($listPaid, $kyuInfoList);
        }
        // standard hall + individual + paid for one kyu when apply 2 kyu
        else if($testSide == 1 && $isIndividual && count($chooseKyu) > 0 && count($listPaid) > 0){
            $returnData['message']  = sprintf($this->translate('R4_MSG32'), $eikenId, $eikenPassword);
            $returnData['message2'] = sprintf($this->translate('msgInformPaidOneKyuOf2Kyu'), $kyuInfoList[$listPaid[0]]['name'], $kyuInfoList[$chooseKyu[0]]['name']);
        }
        // standard hall + collective
        else if ($testSide == 0 && !$isIndividual){
            $returnData['message']  = $this->translate('R4_MSG28');
        }
        // standard hall + individual + not paid before
        else if($testSide == 0 && $isIndividual && count($listPaid) == 0){
            if ($isCombini && $isCredit) {
                $returnData['message'] = sprintf($this->translate('msgStandardBothCombiniCredit'), date('Y/m/d', strtotime($returnData['combiniDeadline'])), date('Y/m/d', strtotime($returnData['creditDeadline'])));
            } elseif ($isCombini) {
                $returnData['message'] = sprintf($this->translate('msgStandardOnlyCombiniOrCredit'), date('Y/m/d', strtotime($returnData['combiniDeadline'])));
            } elseif ($isCredit) {
                $returnData['message'] = sprintf($this->translate('msgStandardOnlyCombiniOrCredit'), date('Y/m/d', strtotime($returnData['creditDeadline'])));
            }
            //$returnData['message'] = sprintf($this->translate('R4_MSG33'),  date('Y/m/d',strtotime($returnData['combiniDeadline'])),  date('Y/m/d',strtotime($returnData['creditDeadline'])));
        }
        // standard hall + individual + paid all kyu before
        else if($testSide == 0 && $isIndividual && count($chooseKyu) == 0 && count($listPaid) > 0){
            $returnData['message']  = $this->translate('R4_MSG28');
            $returnData['message2'] = $this->createResponseMessageForPopup2($listPaid, $kyuInfoList);
        }
        // standard hall + individual + paid for one kyu when apply 2 kyu
        else if($testSide == 0 && $isIndividual && count($chooseKyu) > 0 && count($listPaid) > 0) {
            $returnData['message']  = $this->translate('R4_MSG28');
            $returnData['message2'] = sprintf($this->translate('msgInformPaidOneKyuOf2Kyu'), $kyuInfoList[$listPaid[0]]['name'], $kyuInfoList[$chooseKyu[0]]['name']);
        }

        $returnData['msgCombiniDeadline'] = $returnData['msgCreditDeadline'] = '';
        $paymentService = new PaymentEikenExamService($this->getServiceLocator());
        $userIdentity = PrivateSession::getData(Constants::SESSION_SATELLITE);
        if ($paymentService->checkCombiniDateline($userIdentity['deadline'])) {
            $returnData['msgCombiniDeadline'] = sprintf($this->translate('msgCombiniDeadlineExpire'));
        }
        if ($paymentService->checkCreditDateline($userIdentity['deadline'])) {
            $returnData['msgCreditDeadline'] = sprintf($this->translate('msgCreditDeadlineExpire'));
        }

        return $returnData;
    }

    public function createResponseMessageForPopup2($listPaid, $kyuInfoList)
    {
        if(count($listPaid) == 1) {
            return sprintf($this->translate('msgInformPaidOneKyu'), $kyuInfoList[$listPaid[0]]['name']);
        }elseif (count($listPaid) == 2){
            return sprintf($this->translate('msgInformPaidBoth'), $kyuInfoList[$listPaid[0]]['name'], $kyuInfoList[$listPaid[1]]['name']);
        }
    }
    
    protected function updateApplyEikenLevel ($appEikInfoId, $scheduleId, $eikenLevelId, $appEikLevelId, $originalEikenId, $hallType, $currentPupil, $data, $key, $price)
    {
        $em = $this->getEntityManager();
        
        if ($appEikLevelId > 0)
        {
            $appEikLevel = $em->getRepository('Application\Entity\ApplyEikenLevel')->find($appEikLevelId);
            // Set old eikenId
            if ($appEikLevel->isIsSubmit() && $originalEikenId != '' && $appEikLevel->getOldEikenId() == '')
            {
                $appEikLevel->setOldEikenId($originalEikenId);
            }
        }
        else
        {
            $appEikLevel = $em->getRepository('Application\Entity\ApplyEikenLevel')
                    ->findOneBy(array(
                        'eikenScheduleId' => $scheduleId,
                        'eikenLevelId' => $eikenLevelId,
                        'pupilId' => $currentPupil['pupilId'],
                        'tuitionFee' => $price,
                        'isDelete' => 0
            ));
            if(empty($appEikLevel)){
                $appEikLevel = $em->getRepository('Application\Entity\ApplyEikenLevel')
                    ->findOneBy(array(
                        'eikenScheduleId' => $scheduleId,
                        'eikenLevelId' => $eikenLevelId,
                        'pupilId' => $currentPupil['pupilId'],
                        'isDelete' => 0
                ));
            }
            $appEikLevel = empty($appEikLevel) ? new ApplyEikenLevel() : $appEikLevel;
        }
        // set isRegister = 1 and set isSateline = 1
        $appEikLevel->setIsRegister(1);
        $appEikLevel->setIsSateline(1);

        $appEikLevel->setApplyEikenPersonalInfo($em->getReference('Application\Entity\ApplyEikenPersonalInfo', array(
                'id' => $appEikInfoId
        )));
        $appEikLevel->setEikenSchedule($em->getReference('Application\Entity\EikenSchedule', array(
            'id' => $scheduleId
        )));
        $appEikLevel->setEikenLevel($em->getReference('Application\Entity\EikenLevel', array(
            'id' => $eikenLevelId
        )));
        
        $appEikLevel->setPupil($em->getReference('Application\Entity\Pupil', array(
            'id' => $currentPupil['pupilId']
        )));
        /*@var $appEikLevel \Application\Entity\ApplyEikenLevel */
        if(isset($data['examGrade'.$key])){
            $appEikLevel->setFeeFirstTime(NULL);
            if($data['exemption'.$key] != ''){
                $appEikLevel->setFeeFirstTime($data['exemption'.$key]);
            } 
            $appEikLevel->setFirstPassedTime(NULL);
            if($data['passedKai'.$key] != ''){
                $appEikLevel->setFirstPassedTime($data['passedKai'.$key]);
            }
            $appEikLevel->setAreaNumber1(NULL);
            if($data['passedPlace'.$key] != ''){
                $appEikLevel->setAreaNumber1($data['passedPlace'.$key]);
            }
            $appEikLevel->setAreaPersonal1(NULL);
            if($data['personalId'.$key] != ''){
                $appEikLevel->setAreaPersonal1($data['personalId'.$key]);
            }
            $appEikLevel->setCityId1(NULL);
            if($data['firstTestCity'.$key] != ''){
                $appEikLevel->setAreaNumber2($em->getReference('Application\Entity\City',(int)$data['firstTestCity'.$key]));
            }
            $appEikLevel->setDistrictId1(NULL);
            if($data['firstExamPlace'.$key] != ''){
                $appEikLevel->setAreaPersonal2($em->getReference('Application\Entity\District',(int)$data['firstExamPlace'.$key]));
            }
            $appEikLevel->setCityId2(NULL);
            if($data['secondTestCity'.$key] != ''){
                $appEikLevel->setAreaNumber3($em->getReference('Application\Entity\City',(int)$data['secondTestCity'.$key]));
            }            
            if($data['secondExamPlace'.$key] != ''){                
                $appEikLevel->setAreaPersonal3($em->getReference('Application\Entity\District',(int)$data['secondExamPlace'.$key]));
            }
        }
        $appEikLevel->setHallType($hallType);
        $appEikLevel->setPaymentStatus($appEikLevel->getPaymentStatus() == null ? 0 :$appEikLevel->getPaymentStatus());
        $appEikLevel->setTuitionFee($price);
        $appEikLevel->setIsSubmit(0);
        $appEikLevel->setRegDateOnSatellite(new \DateTime());
        
        $isDiscount = $this->getIsDiscountValue($hallType, $scheduleId, $currentPupil['pupilId'],$eikenLevelId);
        $appEikLevel->setIsDiscount($isDiscount);

        $em->persist($appEikLevel);
        $em->flush();
        
        $appEikLevelId = $appEikLevel->getId();
        $em->clear();
        
        return $appEikLevelId;
    }
    protected function setSessionData ($applyEikenPersonalInfoSession, $data)
    {
        if (!isset($applyEikenPersonalInfoSession->infoData))
            $applyEikenPersonalInfoSession->infoData = new \stdClass;
        $applyEikenPersonalInfoSession->infoData->postData = $data;
        $applyEikenPersonalInfoSession->eikenLevelId = (int) $data['eikenLevelId'];
        $applyEikenPersonalInfoSession->applyEikenLevelId = (int) $data['applyEikLevelId'];
    }

    protected function storeApplyPersonalInfo($applyEikenPersonalInfoSession, $scheduleId, $currentPupil, $hallType)
    {
        $infoData = $applyEikenPersonalInfoSession->infoData;
        $em = $this->getEntityManager();
        //find this pupil had eikenId or not
        $applyEikenPersonalInfo = $em->getRepository('Application\Entity\ApplyEikenPersonalInfo')->findOneBy(array(
            'pupilId' => $currentPupil['pupilId'],
            'eikenScheduleId' => $scheduleId,
            'isDelete' => 0,
            'isSateline' => 1
        ));
        // Set data from post

        $data = $infoData->postData;
        if (!$applyEikenPersonalInfo) {
            /**
             * @var ApplyEikenPersonalInfo
             */
            $applyEikenPersonalInfo2 = $em->getRepository('Application\Entity\ApplyEikenPersonalInfo')->findOneBy(array(
                'pupilId' => $currentPupil['pupilId'],
                'eikenScheduleId' => $scheduleId,
                'isDelete' => 0,
                'isSateline' => 0
            ));

            /**
             * In case ref/get by click 2 buttons in each row
             * ==> there is one appEikInfoId
             * ==> must check if this record with 2 cases:
             *   1. It has already have EikenId
             *      - check if it was used by another appEikLevelRecord ==> create new appEikInfoId
             *      - If not update the new get EikenId to this record
             *   2. It has not have EikenId yet ==> update the new get EikenId to this record
             */
            $appInfoId = isset($data['appEikInfoId']) ? (int)$data['appEikInfoId'] : 0;
            if ($appInfoId > 0) {
                $applyEikenPersonalInfo = $em->getRepository('Application\Entity\ApplyEikenPersonalInfo')->find($appInfoId);
                if (!empty($applyEikenPersonalInfo)) {
                    // Get original pupilId
                    if (!empty($applyEikenPersonalInfo->getPupil()))
                        $pupil = $applyEikenPersonalInfo->getPupil();
                    // Get original schoolYear
                    if (!empty($applyEikenPersonalInfo->getOrgSchoolYear()))
                        $orgSchoolYear = $applyEikenPersonalInfo->getOrgSchoolYear();
                    // Get original class
                    if (!empty($applyEikenPersonalInfo->getClass()))
                        $class = $applyEikenPersonalInfo->getClass();

                    /**
                     * Store this EikenId for comparing and send to Ukesuke if needed
                     */
                    if (trim($applyEikenPersonalInfo->getEikenId()) != '' && $infoData->setEikenId != trim($applyEikenPersonalInfo->getEikenId()))
                        $originalEikenId = $applyEikenPersonalInfo->getEikenId();
                    // check if it was used by another appEikLevelRecord ==> create new appEikInfoId
                    $appEikLevel = $em->getRepository('Application\Entity\ApplyEikenLevel')->getApplyEikLevelByInfoId($appInfoId);
                    if (!empty($appEikLevel) && count($appEikLevel) > 1) {
                        $applyEikenPersonalInfo = new ApplyEikenPersonalInfo();
                        $applyEikenPersonalInfo->setIsSateline(1);

                        // set Original information
                        if (isset($pupil))
                            $applyEikenPersonalInfo->setPupil($pupil);
                        if (isset($orgSchoolYear))
                            $applyEikenPersonalInfo->setOrgSchoolYear($orgSchoolYear);
                        if (isset($class))
                            $applyEikenPersonalInfo->setClass($class);
                    }
                }
            } else {
                $applyEikenPersonalInfo = empty($applyEikenPersonalInfo2)
                    ? new ApplyEikenPersonalInfo()
                    : $applyEikenPersonalInfo2;
                $applyEikenPersonalInfo->setIsSateline(1);
            }
        } else {
            $currentInfoId = (int)$data['appEikInfoId'];
            if ($currentInfoId > 0) {
                $currentInfo = $em->getRepository('Application\Entity\ApplyEikenPersonalInfo')->find($currentInfoId);
                if (!empty($currentInfo)) {
                    /**
                     * Store this EikenId for comparing and send to Ukesuke if needed
                     */
                    if (trim($currentInfo->getEikenId()) != '' && $infoData->setEikenId != trim($currentInfo->getEikenId()))
                        $originalEikenId = $currentInfo->getEikenId();
                }
            }
        }


        $applyEikenPersonalInfo->setGender($data['rdSex']);

        if ($hallType == 1) {
            $applyEikenPersonalInfo->setPostalCode(trim($data['txtPostalCode1']) . '-' . trim($data['txtPostalCode2']));

            if ((int)$data['ddlCity'] > 0 && (int)$data['ddlCity'] <= 47)
                $applyEikenPersonalInfo->setCity($em->getReference('Application\Entity\City', array(
                    'id' => (int)$data['ddlCity']
                )));
            else
                $applyEikenPersonalInfo->setCity(null);

            $applyEikenPersonalInfo->setDistrict(isset($data['txtDistrict']) ? $data['txtDistrict'] : null);
            $applyEikenPersonalInfo->setBuildingName(isset($data['txtBuilding']) ? $data['txtBuilding'] : null);
            $applyEikenPersonalInfo->setHouseNumber(isset($data['txtAreaCode']) ? $data['txtAreaCode'] : null);
            $applyEikenPersonalInfo->setTown(isset($data['txtTown']) ? $data['txtTown'] : null);
            $applyEikenPersonalInfo->setEmail(isset($data['txtEmail']) ? $data['txtEmail'] : null);
            $applyEikenPersonalInfo->setPhoneNo(trim($data['txtPhoneNo']));
            $applyEikenPersonalInfo->setJobCode(isset($data['ddlJobName']) ? $data['ddlJobName'] : null);
            $applyEikenPersonalInfo->setEikenId($infoData->setEikenId);
            $applyEikenPersonalInfo->setEikenPassword(trim($infoData->setEikenPassword));
            $applyEikenPersonalInfo->setFirstNameKanji(isset($data['txtFirstNameKanji']) ? $data['txtFirstNameKanji'] : null);
            $applyEikenPersonalInfo->setLastNameKanji(isset($data['txtLastNameKanji']) ? $data['txtLastNameKanji'] : null);
            $applyEikenPersonalInfo->setFirstNameKana(isset($data['txtFirstNameKana']) ? $data['txtFirstNameKana'] : null);
            $applyEikenPersonalInfo->setLastNameKana(isset($data['txtLastNameKana']) ? $data['txtLastNameKana'] : null);
            $applyEikenPersonalInfo->setSchoolType(isset($data['ddlSchoolCode']) ? $data['ddlSchoolCode'] : null);

            if (!empty($data['ddlYear']) && !empty($data['ddlMonth']) && !empty($data['ddlDay'])) {
                $birthday = new \DateTime(date($data['ddlYear'] . '/' . $data['ddlMonth'] . '/' . $data['ddlDay']));
                $applyEikenPersonalInfo->setBirthday($birthday);
            }

        }

        $applyEikenPersonalInfo->setEikenSchedule($em->getReference('Application\Entity\EikenSchedule', array(
            'id' => $scheduleId
        )));

        $applyEikenPersonalInfo->setOrganization($em->getReference('Application\Entity\Organization', array(
            'id' => $infoData->setOrganization
        )));

        $pupil = $em->getRepository('Application\Entity\Pupil')->findOneBy(array(
            'id' => $currentPupil['pupilId']
        ));
        $applyEikenPersonalInfo->setPupil($pupil);

        //set org school year and class
        $orgSchoolYearId = $pupil->getOrgSchoolYearId();
        $classId = $pupil->getClassId();
        $applyEikenPersonalInfo->setOrgSchoolYearId($orgSchoolYearId);
        $applyEikenPersonalInfo->setClassId($classId);

        $class = $em->getRepository('Application\Entity\ClassJ')->findOneBy(array(
            'id' => $classId, 'isDelete' => 0
        ));
        $orgSchoolYear = $em->getRepository('Application\Entity\OrgSchoolYear')->findOneBy(array(
            'id' => $orgSchoolYearId, 'isDelete' => 0
        ));
        $applyEikenPersonalInfo->setOrgSchoolYear($orgSchoolYear);
        $applyEikenPersonalInfo->setClass($class);
        if ($orgSchoolYear) {
            $applyEikenPersonalInfo->setOrgSchoolYearName($orgSchoolYear->getDisplayName());
            $applyEikenPersonalInfo->setSchoolCode($orgSchoolYear->getSchoolYear()->getId());
        }
        if ($class)
            $applyEikenPersonalInfo->setClassName($class->getClassName());

        $em->persist($applyEikenPersonalInfo);
        if (isset($infoData->setEikenId))
            $pupil->setEikenId(trim($infoData->setEikenId));
        if (isset($infoData->setEikenPassword))
            $pupil->setEikenPassword($infoData->setEikenPassword);

        $em->persist($pupil);

        $em->flush();
        $infoId = $applyEikenPersonalInfo->getId();
        $em->clear();

        return array(
            'infoId' => isset($infoId) ? $infoId : 0,
            'originalEikenId' => isset($originalEikenId) ? $originalEikenId : ''
        );
    }
    
    /**
     * @param array $data
     * @author LangDD
     * @uses Get fake data, must be overwritten when available
     */

    protected function getNewEikenIdApi ($data, $currentPupil)
    {         
        // Get config
        $config = $this->getServiceLocator()->get('Config')['eiken_config']['api'];

       
        $ukesukeData =$this->apiDataMapping($data, $currentPupil);
        $result = new \stdClass();
        $result->eikenid = '00';
        try {
            $result = \Dantai\Api\UkestukeClient::getInstance()->callEir1c03($config, $ukesukeData);
        }
        catch (Exception $e) {
            return false;
        }
        
        if ($result && !empty($result->eikenid)) {
            return $result->eikenid;
        }
        
        return false;
    }
    
    protected function apiDataMapping ($data,$currentPupil)
    {
        $cityName = '';
        $em = $this->getEntityManager();
        if ((int) $data['ddlCity'] > 0)
        {
            $city = $em->getRepository('Application\Entity\City')->find((int) $data['ddlCity']);
            if (!empty($city))
                $cityName = trim($city->getCityName());
        }
        $grade = array();
        $class = array();
        $firstCharGrade = '';
        $firstCharClass = '';
        if(isset($currentPupil['pupilId'])){
            /*@var $objPupil \Application\Entity\Pupil*/
            $objPupil = $em->getRepository('Application\Entity\Pupil')->find((int) $currentPupil['pupilId']);
            if($objPupil){
                /*@var $objOrgSchoolYear \Application\Entity\OrgSchoolYear*/
                $objOrgSchoolYear = $em->getRepository('Application\Entity\OrgSchoolYear')->find((int) $objPupil->getOrgSchoolYearId());
                if($objOrgSchoolYear){
                    $grade = $objOrgSchoolYear->getDisplayName();
                }
                $class = $objPupil->getClass()->getClassName();
            }
            /*@var $dantaiService \Application\Service\DantaiService*/
            $dantaiService = new \Application\Service\DantaiService();
            $dantaiService->setServiceLocator($this->getServiceLocator());
            
            if($data['ddlJobName'] == 1){
                $firstCharGrade = $dantaiService->convertFullToHaf($dantaiService->cutCharacterWithNumber($grade, 1));
                $firstCharClass = $dantaiService->convertFullToHaf($dantaiService->cutCharacterWithNumber($class, 2));
                if(!preg_match('/^[A-Za-z0-9]*$/', $firstCharGrade)){
                        $firstCharGrade = '';
                }
                if(!preg_match('/^[A-Za-z0-9]*$/', $firstCharClass)){
                        $firstCharClass = '';
                }
            }
        }
        return array(
            'sei_kanji' => $data['txtFirstNameKanji'],
            'mei_kanji' => $data['txtLastNameKanji'],
            'sei_kana' => $data['txtFirstNameKana'],
            'mei_kana' => $data['txtLastNameKana'],
            'gender' => $data['rdSex'],
            'birthday' => $data['ddlYear']. '/'. $data['ddlMonth']. '/'. $data['ddlDay'],
            'zip_code' => trim($data['txtPostalCode1']).trim($data['txtPostalCode2']),
            'prefecture' => $cityName,
            'city' => $data['txtDistrict'],
            'town' => $data['txtTown'],
            'street' => '',
            'building' => '',
            'phone_no' => trim($data['txtPhoneNo']),
            'email' => $data['txtEmail'],
            'shokugyocd' => $data['ddlJobName'],
            'gakkoucd' => !empty($data['ddlSchoolCode']) ? $data['ddlSchoolCode'] : '' ,
            'gakunen' => $firstCharGrade,
            'gakkoumei' => '',
            'kumi' => $firstCharClass,
            'eikenpass' => trim($data['txtEikenPassword'])
        );
    }
    
    public function getEntityManager() {
        return isset($this->entityManager)
                ? $this->entityManager 
                : $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function setEntityManager($entityManager) {
        $this->entityManager = $entityManager;
    }

    public function validateApplyEikenAndTestSiteExemption($dataInfo, $dataExemption, $isDoubleEikenNotSupport){
        $resultValidApply   = array();
        $resultExemption    = array();
        $resultExamtion     = array();
        
        if(!empty($this->validateApplyEiken($dataInfo, $isDoubleEikenNotSupport))){
            $resultValidApply = $this->validateApplyEiken($dataInfo, $isDoubleEikenNotSupport);
        }
        
        $checkKyuExamtion = $this->validateKyuExamtion($dataInfo, $dataExemption);
        if($checkKyuExamtion === false){
            $resultExamtion['nonMapKyuExamtion_chooseKyu'] = $this->translate('chooseKyu');
        }else if($this->checkSelectMainHall($dataInfo) && !$this->validateTestSideExemption($dataExemption)){
            $resultExemption = $this->validateTestSideExemption($dataExemption);
        }
        
        $resultField = array_merge($resultValidApply,$resultExemption,$resultExamtion);
        
        return $this->getMSGvalidateField($resultField);
    }

    public function validateTestSideExemption($data) {      
        // if exemption = 0 (No) then only require firstTestCity and firstTestCity.
        // if exemption = 1 (Yes) and examgrade != 4級 and != 5級 then only require secondTestCity2 and secondExamPlace2.
        // if exemption = 1 (Yes) then will check personalId is digit only, maxlength = 7 and not require
        $dataField = array();
        // validate exemption and test side 1     
        if (!isset($data['exemption1'])) {
            $dataField['nonIsset_exemption1'] = $this->translate('exemption1');
        }
        if ((int)$data['exemption1'] === 0 && !empty($this->checkRequire($data, array('firstTestCity1', 'firstExamPlace1')))) {
            $dataField = array_merge($dataField,$this->checkRequire($data, array('firstTestCity1', 'firstExamPlace1')));
        } 
        if ((int)$data['exemption1'] === 1) {
            if((int)$data['examGrade1'] < $this->examGrade['4級'] && !empty($this->checkRequire($data, array('secondTestCity1', 'secondExamPlace1')))){
                $dataField = array_merge($dataField,$this->checkRequire($data, array('secondTestCity1', 'secondExamPlace1')));
            }
            if(isset($data['personalId1']) && !$this->validatePersonalId($data['personalId1'])){
                $dataField = array_merge($dataField,array('nonValid_personalId1'=>$this->translate('personalId1')));
            }
        }
        // validate exemption and test side 2
        if (!isset($data['exemption2'])) {
            return $dataField;
        }
        if ((int)$data['exemption2'] === 0 && !empty($this->checkRequire($data, array('firstTestCity2', 'firstExamPlace2')))) {
            $dataField = array_merge($dataField,$this->checkRequire($data, array('firstTestCity2', 'firstExamPlace2')));
        }
       
        if ((int)$data['exemption2'] === 1) {
            if((int)$data['examGrade2'] < $this->examGrade['4級'] && !empty($this->checkRequire($data, array('secondTestCity2', 'secondExamPlace2')))){
                $dataField = array_merge($dataField,$this->checkRequire($data, array('secondTestCity2', 'secondExamPlace2')));
            }
            if(isset($data['personalId2']) && !$this->validatePersonalId($data['personalId2'])){
                $dataField = array_merge($dataField,array('nonValid_personalId2'=>$this->translate('personalId2')));
            }
        }

        return $dataField;
    }

    public function checkRequire($data, $arr) {
        $listField = array();
        foreach ($arr as $key) {
            if (!isset($data[$key]) || (string)$data[$key] === '') {
                    if(!array_key_exists($this->translate($key), $listField)){
                        $listField['require_'.$key] = $this->translate($key);
                    }
            }
        }
        return $listField;
    }

    public function validatePersonalId($personalId)
    {
        if (isset($personalId) && (string)$personalId !== '' && (strlen($personalId) > 7 || !ctype_digit((string)$personalId))) {
            return false;
        }

        return true;
    }
    
    public function getApplyEikenLevels($pupilId, $eikenScheduleId) {
        return $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenLevel')
                    ->findBy(array('pupilId' => $pupilId,
                                    'eikenScheduleId' => $eikenScheduleId,
                                    'isDelete' => 0,
                                    'isRegister' => 1), 
                            array('eikenLevelId' => 'ASC')
                            );
    }
    
    public function getApplyEikenLevel($id, $pupilId) {
        return $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenLevel')
                    ->findOneBy(array('id' => $id,
                                    'pupilId' => $pupilId,
                                    'isDelete' => 0,
                                    'isRegister' => 1)
                            );
    }

    public function getExamDate($eikenScheduleId) {
        return $this->getEntityManager()
                ->getRepository('Application\Entity\EikenSchedule')
                ->find($eikenScheduleId);
    }

    public function getSchoolCode()
    {
        return array(
            '6' => '小学',
            '5' => '中学',
            '4' => '高校',
            '3' => '高専',
            '2' => '短大',
            '1' => '大学',
            '8' => '大学院',
            '7' => '専修各種学校'
        );
    }

    public function mappingKyu($eikenLevelPrice, $examDate, $listEikenLevel, $hallTypeExamDay)
    {
        $listEikenLevel = $listEikenLevel ? json_decode($listEikenLevel) : array();
        
        $listKyu = array();
        $hallTypeExamDate = Constants::HALL_TYPE_EXAM_DATE;
        foreach ($listEikenLevel as $kyu) {
            $listKyu[$kyu]['priceName'] = sprintf($this->translate('priceName'), number_format($eikenLevelPrice[$kyu]['price']));
            $listKyu[$kyu]['price'] = $eikenLevelPrice[$kyu]['price'];
            $listKyu[$kyu]['name'] = $eikenLevelPrice[$kyu]['name'];
            $listKyu[$kyu]['examDate'] = '';
            if ($hallTypeExamDay == $hallTypeExamDate['sunDate'] || $kyu == 1 || $kyu == 2) {
                $listKyu[$kyu]['examDate'] = $this->getExamDateToString($examDate->getSunDate(), 'sunDate');
            }
            else if ($hallTypeExamDay == $hallTypeExamDate['friDate']) {
                $listKyu[$kyu]['examDate'] = $this->getExamDateToString($examDate->getFriDate(), 'friDate');
            }
            else if ($hallTypeExamDay == $hallTypeExamDate['satDate']) {
                $listKyu[$kyu]['examDate'] = $this->getExamDateToString($examDate->getSatDate(), 'satDate');
            }
            else if ($hallTypeExamDay == $hallTypeExamDate['friDateAndSatDate']) {
                $listKyu[$kyu]['examDate'] = $this->getExamDateToString($examDate->getFriDate(), 'friDate') . $this->translate('comma') . $this->getExamDateToString($examDate->getSatDate(), 'satDate');
            }
        }

        return $listKyu;
    }
    public function getApplyEikenPersonalInfo($pupilId, $eikenScheduleId) {
        return $this->getEntityManager()
            ->getRepository('Application\Entity\ApplyEikenPersonalInfo')
            ->findOneBy(array('pupilId' => $pupilId,
                              'eikenScheduleId' => $eikenScheduleId,
                              'isDelete' => 0));
    }

    public function getCity($cityId) {
        return $this->getEntityManager()
            ->getRepository('Application\Entity\City')
            ->find($cityId);
    }
    
    public function getClass($params, $orgId)
    {
        $em = $this->getEntityManager();
        $schoolyearId = $params()->fromQuery('schoolyear');
        $data = $em->getRepository('Application\Entity\ClassJ')->getListClassBySchoolYear($schoolyearId, $orgId);
        return $data;
    }
    private function getExamDateToString($examDate, $date)
    {
        return !empty($examDate) ? sprintf($this->translate($date), $examDate->format('n'), $examDate->format('j')) : '';
    }
    
    public function translate($key) {
        return $this->getServiceLocator()->get('MVCTranslator')->translate($key);
    }
    
    public function validateApplyEiken($data, $doubleEikenNotSupport)
    {
        $dataChooseKyu          = array();
        $dataOrderKyu              = array();
        $dataValidHallType      = array();
        $dataRequire            = array();
        $dataHaftSize           = array();
        $dataStringKana         = array();
        $dataBirthDate          = array();
        $dataNotNumber          = array();
        $dataLengthPostalCode   = array();
        $dataCheckZipcode       = array();
        $dataCheckEmail         = array();
        if (empty($data['chooseKyu']) || count($data['chooseKyu']) > 2) {
            $dataChooseKyu = array(
                                    'numberKyu_chooseKyu'=> $this->translate('chooseKyu')
                                );
        }
        if (count($data['chooseKyu']) == 2 && ($doubleEikenNotSupport == 1 || (abs($data['chooseKyu'][1] - $data['chooseKyu'][0]) > 1))) {
            $dataOrderKyu = array(
                                    'numericalOrderKyu_chooseKyu'=> $this->translate('chooseKyu')
                                );
        }
        if (!$this->checkValidHallType($data)) {
            $dataValidHallType = array(
                                    'validHallType_chooseKyu'=> $this->translate('chooseKyu')
                                );
        }
        $arrCheckRequire = array(
            'txtFirstNameKanji',
            'txtLastNameKanji',
            'txtFirstNameKana',
            'txtLastNameKana',
            'rdSex',
            'ddlYear',
            'ddlMonth',
            'ddlDay',
            'txtPostalCode1',
            'txtPostalCode2',
            'ddlCity',
            'txtDistrict',
            'txtTown',
            'txtPhoneNo1',
            'txtPhoneNo2',
            'txtPhoneNo3',
            'ddlJobName');
        if($data['ddlJobName'] == 1){
            $arrCheckRequire = array(
            'txtFirstNameKanji',
            'txtLastNameKanji',
            'txtFirstNameKana',
            'txtLastNameKana',
            'rdSex',
            'ddlYear',
            'ddlMonth',
            'ddlDay',
            'txtPostalCode1',
            'txtPostalCode2',
            'ddlCity',
            'txtDistrict',
            'txtTown',
            'txtPhoneNo1',
            'txtPhoneNo2',
            'txtPhoneNo3',
            'ddlJobName',
            'ddlSchoolCode');
        }
        if ($this->checkSelectMainHall($data)) {
            $dataRequire    = $this->checkRequire($data, $arrCheckRequire);
            $dataHaftSize   = $this->checkValidHaftSize($data, array('txtFirstNameKanji', 'txtLastNameKanji', 'txtDistrict', 'txtTown'));
            $dataStringKana = $this->checkValidStringKana($data, array('txtFirstNameKana', 'txtLastNameKana'));
            
            if (!$this->checkValidBirthDate($data['ddlYear'], $data['ddlMonth'], $data['ddlDay'])) {
                $dataBirthDate = array(
                                    'birthDate_ddlYear'=> $this->translate('ddlYear')
                                );
            }
            
            $dataNotNumber = $this->checkValidNumber($data, array('txtPostalCode1', 'txtPostalCode2', 'txtPhoneNo1', 'txtPhoneNo2', 'txtPhoneNo3'));
            
            if (mb_strlen($data['txtPostalCode1']) != 3 || mb_strlen($data['txtPostalCode2']) != 4) {
                $dataLengthPostalCode = array(
                                        'lengthPostalCode_txtPostalCode1' => $this->translate('txtPostalCode1')
                                    );
            }
            
            $dantaiService = new \Application\Service\DantaiService();
            $dantaiService->setServiceLocator($this->getServiceLocator());
            $checkZipcode = $dantaiService->zipcode2Address($data['txtPostalCode1'] . $data['txtPostalCode2']);
            
            if (empty($checkZipcode)) {
                $dataCheckZipcode = array(
                                        'checkZipcode_txtPostalCode1' => $this->translate('txtPostalCode1')
                                    );
            };
            
            if ((mb_strlen($data['txtPostalCode1']) + mb_strlen($data['txtPostalCode2']) + mb_strlen($data['txtPostalCode3']) > 11)) {
                $dataLengthPostalCode = array(
                                        'lengthPostalCode_txtPostalCode1' => $this->translate('txtPostalCode1')
                                    );
            }
            
            if (!empty($data['txtEmail']) && !$this->checkValidEmail($data['txtEmail'])) {
               $dataCheckEmail = array(
                                        'checkZipcode_txtEmail' => $this->translate('txtEmail')
                                    );
            }
            
        }
        $dataValidate = array_merge($dataChooseKyu , $dataOrderKyu, $dataValidHallType, $dataRequire , $dataHaftSize , $dataStringKana , $dataBirthDate , $dataNotNumber , $dataLengthPostalCode , $dataCheckZipcode , $dataCheckEmail);

        return $dataValidate;
    }
    
    public function checkValidSelectedKyu($chooseKyu) { 
        foreach ($chooseKyu as $value){
            if ($value == 1 || $value == 2){
                return true;
            }                
        }
        return false;
    }
    
    public function checkValidHallType($data) {
        for($i = 1; $i <= 7; $i++){
            if(isset($data['hallType'.$i])){
                return true;
            }
        }
        return false;
    }

    // get main hall function.
    public function checkSelectMainHall($data) {
        for($i = 1; $i <= 7; $i++){
            if(isset($data['hallType'.$i]) && $data['hallType'.$i] == 1){
                return true;
            }
        }
        return false;
    }
    
    public function checkValidHaftSize($data,$arr) { 
        $listField = array();
        $haftSize = '/^[a-zA-Z0-9-_\x{FF61}-\x{FFDC}\x{FFE8}-\x{FFEE}]*$/u';
        foreach ($arr as $key) {
            if (preg_match($haftSize,(string)$data[$key])) {
                 if(!array_key_exists($this->translate($key), $listField)){
                        $listField['haftSize_'.$key] = $this->translate($key);
                    }
            }
        }
        return $listField;
    }
    
    public function checkValidStringKana($data,$arr) { 
        $listField = array();
        $stringKana = '/^([゠ァアィイゥウェエォオカガキギクグケゲコゴサザシジスズセゼソゾタダチヂッツヅテデトドナニヌネノハバパヒビピフブプヘベペホボポマミムメモャヤュユョヨラリルレロヮワヰヱヲンヴヵヶヷヸヹヺ・ーヽヾヿ｡｢｣､･ｦｧｨｩｪｫｬｭｮｯｰｱｲｳｴｵｶｷｸｹｺｻｼｽｾｿﾀﾁﾂﾃﾄﾅﾆﾇﾈﾉﾊﾋﾌﾍﾎﾏﾐﾑﾒﾓﾔﾕﾖﾗﾘﾙﾚﾛﾜﾝﾞﾟ]+)$/';
        foreach ($arr as $key) {
            if (!preg_match($stringKana,(string)$data[$key])) {
                if(!array_key_exists($this->translate($key), $listField)){
                        $listField['stringKana_'.$key] = $this->translate($key);
                }
            }
        }
        return $listField;
    }
    
    public function checkValidBirthDate($year, $month, $day)
    {
        return (date('Y-m-d', strtotime($year . '-' . $month . '-' . $day)) < date('Y-m-d'));
    }
    
    public function checkValidNumber($data,$arr) { 
        $number = '/[^0-9]/';
        $listField = array();
        foreach ($arr as $key) {
            if (preg_match($number,(string)$data[$key])) {
                if(!array_key_exists($this->translate($key), $listField)){
                        $listField['notNumber_'.$key] = $this->translate($key);
                }
            }
        }
        return $listField;
    }
    
    public function checkValidEmail($email) { 
        $regexEmail = '/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/';
        if (!preg_match($regexEmail,(string)$email)) {
            return false;
        }
        return true;
    }
    
    public function random_password( $length = 6) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password = substr( str_shuffle( $chars ), 0, $length );
        return $password;
    }
    
    public function loadMainHall($cityId, $eikenLevelId, $isFirstTime = true)
    {
        $em = $this->getEntityManager();
        $condition = array(
            'isDelete' => 0,
            'forHallType'=> 0
        );
        if ((int) $cityId)
        {
            $condition['cityId'] = $cityId;
            $conditionField = $this->getExamLocationCondition($eikenLevelId, $isFirstTime);
            $condition[$conditionField] = 1;
            $mainHallAddresses = \Satellite\View\Helper\SatelliteCommon::generateSelectOptions($em->getRepository('Application\Entity\District')->findBy($condition), 'getName');
        }
        else
            $mainHallAddresses = array();
        return $mainHallAddresses;
    }
    
    public function getExamLocationCondition ($eikenLevelId, $isFirtTime = true)
    {
        switch ($eikenLevelId) {
            case 1:
                if ($isFirtTime)
                    $field = 'kyuOneFirstTime';
                else
                    $field = 'kyuOneSecondTime';
                break;
            case 2:
                if ($isFirtTime)
                    $field = 'kyuPreOneFirstTime';
                else
                    $field = 'kyuPreOneSecondTime';
                break;
            case 3:
                if ($isFirtTime)
                    $field = 'kyuTwoFirstTime';
                else
                    $field = 'kyuTwoSecondTime';
                break;
            case 4:
                if ($isFirtTime)
                    $field = 'kyuPreTwoFirstTime';
                else
                    $field = 'kyuPreTwoSecondTime';
                break;
            case 5:
                if ($isFirtTime)
                    $field = 'kyuThreeFirstTime';
                else
                    $field = 'kyuThreeSecondTime';
                break;
            case 6:
                $field = 'kyuFourFirstTime';
                break;
            case 7:
                $field = 'kyuFiveFirstTime';
                break;
            default:
                return false;
        }
        return $field;
    }
    
    /**
     * @param int $eikenScheduleId
     * @return multitype:string
     * @author LangDD
     * Get Kai values list for first time free 
     */
    public function getKaiOptions ($eikenScheduleId) 
    {
        $eikenSchedule = $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule')->find($eikenScheduleId);
        $currentKai = $eikenSchedule->getKai();
        $currentYear = $eikenSchedule->getYear();
        $kaiOptions = array(
            '' => ''
        );
        $translator = $this->getServiceLocator()->get('MVCTranslator');

        $year = $currentYear;
        $j = 1;
        $flag = false;
        for ($i = 1; $i <= 3; $i++)
        {
            if (!$flag)
            {
                $kai = $currentKai - $i;
            }
            else 
            {
                $kai = 3 - $j;
                $j = $j + 1;
            }
            if ($kai <= 0)
            {
                $flag = true;
                $kai = 3;
                $year = $currentYear - 1;
            }
            $kaiOptions[$year.'|'.$kai] = $year. $translator->translate('PassedKai1').$kai. $translator->translate('PassedKai2');
        }
        return $kaiOptions;
    }

    /**
     * @param $pupilId
     * @param $eikenScheduleId
     * @param $eikenLevelId
     * @return array: receiptNo + telNo |bool: false if not found the payment
     */
    public function getReceiptNoTelNo($pupilId, $eikenScheduleId, $eikenLevelId) {
        $em = $this->getEntityManager();
        $siteCode = $this->getServiceLocator()->get('Config')['ConsoleInvitation']['econtext_combini_site_code'];
        $paymentInfo = $em->getRepository('Application\Entity\PaymentInfo')->findOneBy(array(
            'pupilId' => $pupilId,
            'eikenScheduleId' => $eikenScheduleId,
            'siteCode' => $siteCode
        ));
        
        if (empty($paymentInfo)) {
            return false;
        }
        /** @var ApplyEikenLevel $applyEikenLevel */
        $applyEikenLevel = $em->getRepository('Application\Entity\ApplyEikenLevel')
                ->findOneBy(
                    array('pupilId'         => $pupilId,
                          'eikenScheduleId' => $eikenScheduleId,
                          'isDelete'        => 0,
                          'isRegister'    => 1,
                          'eikenLevelId'    => $eikenLevelId),
                    array('eikenLevelId' => 'ASC')
                );
        if (empty($applyEikenLevel)) {
            return false;
        }
        /**
         * Select payment of an apply eiken.
         */
        $issuingPayment = $em->getRepository('Application\Entity\IssuingPayment')->findOneBy(array(
            'paymentInfoId'     => $paymentInfo->getId(),
            'eikenLevelId' => $eikenLevelId,
            'price' => $applyEikenLevel->getTuitionFee()
            ),
            array('updateAt' => 'Desc')
        );
        /**
         * If student has paid before apply eiken with different price (can't find payment by price),
         * system must show TelNo and ReceiptNo of paid payment.
         */
        if(empty($issuingPayment) && $applyEikenLevel->getPaymentStatus() == 1){
            $issuingPayment = $em->getRepository('Application\Entity\IssuingPayment')->findOneBy(array(
                'paymentInfoId'     => $paymentInfo->getId(),
                'eikenLevelId' => $eikenLevelId,
                ),
                array('updateAt' => 'Desc')
            );
        }
        if (empty($issuingPayment)) {
            return false;
        }
        
        return array('telNo' => $issuingPayment->getTelNo(), 'receiptNo' => $issuingPayment->getReceiptNo());
    }
    
    public function getIsDiscountValue($hallType,$scheduleId,$pupilId,$eikenLevelId) {
        $isDiscount = 0;
        if(empty($hallType) || empty($scheduleId) || empty($pupilId) || empty($eikenLevelId)){
            return $isDiscount;
        }
        
        $em = $this->getEntityManager();
        
        /*@var $objPupil \Application\Entity\Pupil */
        $objPupil = $em->getRepository('Application\Entity\Pupil')->find($pupilId);
        
        /*@var $objEikenSchedule \Application\Entity\EikenSchedule */
        $objEikenSchedule = $em->getRepository('Application\Entity\EikenSchedule')
                ->find($scheduleId);
        if(empty($objPupil) || empty($objEikenSchedule)){
            return $isDiscount;
        }
        $conditions = array(
            'orgNo' => $objPupil->getOrganization()->getOrganizationNo(),
            'orgSchoolYearId' => $objPupil->getOrgSchoolYearId(),
            'year' => $objEikenSchedule->getYear(),
            'kai' => $objEikenSchedule->getKai()
        );

        $objSpecial = $this->getEntityManager()
                        ->getRepository('\Application\Entity\SpecialPrice')
                        ->getSpecialPrice($conditions);

        if(empty($objSpecial)){
            return $isDiscount;
        }
        $listDiscount = '';
        foreach ($objSpecial as $row){
            if(isset($row['hallType']) && $row['hallType'] == 1){
                $listDiscount = $row['discountKyu'];
            }
        }
        if($listDiscount){
            $listDiscount = json_decode($listDiscount,true);
        }

        if(!empty($listDiscount) && in_array($eikenLevelId, $listDiscount)){
            $isDiscount = 1;
        }
        
        return $isDiscount;
    }

    public function getListDistrictByCode(){
        $em = $this->getEntityManager();
        return $em->getRepository('Application\Entity\District')->getListDistrictByCode();
    }

    public function getMSGvalidateField($data){
        $msg = '';
        
        if(!empty($data)){
            $arr = array_flip($data);
            foreach ($arr as $key => $field){
                if(empty($msg)){
                    $msg = $this->translate('MSG_RECHECK_DATA_STUDENT_APPLY').'<br>'.$key;
                }else{
                    $msg = $msg.'、<br>'.$key;
                }
            }
        }
        return $msg;
   }

    public function validateKyuExamtion($dataInfo, $dataExamtion){
        if(!empty($dataInfo)){
            $numberChoseKyu = 0;
            for($i = 1; $i <= 7; $i++){
                if(isset($dataInfo['hallType'.$i]) && $dataInfo['hallType'.$i] == 1){
                    $numberChoseKyu = $numberChoseKyu + 1;
                }
            }
            $numberExamtion = 0;
            if(!empty($dataExamtion)){
                if(isset($dataExamtion['examGrade1'])){
                    $numberExamtion = $numberExamtion + 1;
                }
                if(isset($dataExamtion['examGrade2'])){
                    $numberExamtion = $numberExamtion + 1;
                }
            }
            
            if($numberChoseKyu == $numberExamtion){
                return true;
            }
        }
        return false;
   }
}
