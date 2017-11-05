<?php

namespace Eiken\Service;

use Application\Entity\Repository\ApplyEikenLevelRepository;
use Application\Service\DantaiService;
use Aws\Ses\SesClient;
use Aws\Ses\Exception\SesException;
use Eiken\Service\ServiceInterface\ApplyEikenOrgServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Application\Entity\ApplyEikenOrg;
use Application\Entity\ApplyEikenOrgDetails;
use Application\Entity\Organization;
use Application\Entity\EikenTestResult;
use Application\Entity\IBATestResult;
use Application\Entity\EikenLevel;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Dantai\PrivateSession;
use Zend\Json\Json;
use Eiken\EikenConst;
use InvitationMnt\InvitationConst;
use Dantai\PublicSession;
use Application\Entity\ApplyEikenStudent;

class ApplyEikenOrgService implements ApplyEikenOrgServiceInterface, ServiceLocatorAwareInterface {

    use ServiceLocatorAwareTrait;

    protected static $invitationSetting;
    protected static $currentUserInfo;
    protected static $eikenSchedule;
    protected static $organization;
    protected static $applyEikenOrg;
    protected static $eikenScheduleId;
    protected $definitionSpecial = 0;

    /**
     * Save Information when user click save as draft button or submitted data to eikesuke
     * @author DuongTD
     * @var $theParams
     */
    public function saveInformationData($theParams) {
        $currentUserInfor = $this->getCurrentUserInfor();
        $orgId = $currentUserInfor['organizationId'];
        $em = $this->getEntityManager();
        if (!$this->getEikenScheduleId()) {
            return false;
        }
        $applyEikenOrg = $em->getRepository('Application\Entity\ApplyEikenOrg')->findOneBy(array(
            'organizationId' => $this->getOrganizationId(),
            'eikenScheduleId' => $this->getEikenScheduleId()
        ));


        // Set session flag to log activities
        PrivateSession::setData('create-activity-log-flag', false);
        if (empty($applyEikenOrg)) {
            $applyEikenOrg = new ApplyEikenOrg();
            $applyEikenOrg->setExecutorName($currentUserInfor['firstNameKanji'].$currentUserInfor['lastNameKanji']);
            // Set session flag to log activities
            PrivateSession::setData('create-activity-log-flag', true);

            if (!empty($theParams['FirtNameKanji'])) {
                $applyEikenOrg->setFirtNameKanji($theParams['FirtNameKanji']);
            }
            if (!empty($theParams['LastNameKanji'])) {
                $applyEikenOrg->setLastNameKanji($theParams['LastNameKanji']);
            }
            if (!empty($theParams['MailAddress'])) {
                $applyEikenOrg->setMailAddress($theParams['MailAddress']);
            }
            // LangDD - Implement update Apply Eiken
            if (!empty($theParams['confirmMailAddress'])) {
                $applyEikenOrg->setConfirmEmail($theParams['confirmMailAddress']);
            }
            if (!empty($theParams['cityId'])) {
                $applyEikenOrg->setCity($em->getReference('Application\Entity\City', array(
                    'id' => $theParams['cityId']
                )));
            }
            if (!empty($theParams['districtId'])) {
                $applyEikenOrg->setDistrict($em->getReference('Application\Entity\District', array(
                    'id' => $theParams['districtId']
                )));
            }
        }
        $applyEikenOrg->setOrganization($em->getReference('Application\Entity\Organization', array(
            'id' => $orgId
        )));
        if ( isset($theParams['date0']) )
            $applyEikenOrg->setTypeExamDate($theParams['date0']);
        $applyEikenOrg->setEikenSchedule($em->getReference('Application\Entity\EikenSchedule', array(
            'id' => $this->getEikenScheduleId()
        )));
        //status is DRAFT or SUBMITTED
        if ($theParams['isDraft']) {
            if (!empty($applyEikenOrg->getStatus()) && $applyEikenOrg->getStatus() == 'DRAFT') {
                $applyEikenOrg->setApplyStatus('変更通知');
            } else {
                $applyEikenOrg->setApplyStatus('初回通知');
            }
            $applyEikenOrg->setStatus('DRAFT');

            // Change session ApplyEikenOrg status
            $currentStatus = PrivateSession::getData('applyEikenStatus');
            $currentStatus['hasApplyEikenOrg'] = true;
            PrivateSession::setData('applyEikenStatus', $currentStatus);
        }
        $hasMainHall = 0;
        
        $applyEikenOrg->setTotal($this->getTotal($theParams, $hasMainHall));
        $applyEikenOrg->setActualExamDate($this->getActualExamDate($theParams));
        $applyEikenOrg->setHasMainHall($hasMainHall);
        $applyEikenOrg->setCd($theParams['totalcd']);
        if ( isset($theParams['locationType']) ){
            $applyEikenOrg->setLocationType((int) $theParams['locationType']);
            if($theParams['locationType'] === '' || $theParams['locationType'] === null){
                $applyEikenOrg->setLocationType(null);
            }
        }
        if ( isset($theParams['locationType1']) ){
            $applyEikenOrg->setLocationType1((int) $theParams['locationType1']);
            if($theParams['locationType1'] === '' || $theParams['locationType1'] === null){
                $applyEikenOrg->setLocationType1(null);
            }
        }
        if (empty($theParams['EikenOrgNo1']))
            $theParams['EikenOrgNo1'] = '';
        $applyEikenOrg->setEikenOrgNo1($theParams['EikenOrgNo1']);
        if (empty($theParams['EikenOrgNo2']))
            $theParams['EikenOrgNo2'] = '';
        $applyEikenOrg->setEikenOrgNo2($theParams['EikenOrgNo2']);
        if (empty($theParams['EikenOrgNo123']))
            $theParams['EikenOrgNo123'] = '';
        $applyEikenOrg->setEikenOrgNo123($theParams['EikenOrgNo123']);
        $config = $this->getServiceLocator()->get('config');
        $listRefundOption = $config['refundStatusOption'];
        if (empty($theParams['refundStatus']) || !in_array($theParams['refundStatus'], array_keys($listRefundOption)))
            $theParams['refundStatus'] = 0;
        $applyEikenOrg->setStatusRefund($theParams['refundStatus']);
        try {
            $em->persist($applyEikenOrg);
            $em->flush();
            if($applyEikenOrg->getId() && $theParams['isOrgDiscount'] == 1){
                $this->saveDataToApplyEikenStudent($applyEikenOrg->getId(),$orgId ,$theParams['pupilDiscountStand']);
            }
        } catch (Exception $e) {
            throw $e;
        }
        // get SGH Price
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        $eikenLevelPrice = $dantaiService->getListPriceOfOrganization($currentUserInfor['organizationNo'], array(1, 2, 3, 4, 5, 6, 7));
        // Save price from table EikenLevel to ApplyEikenOrgDetails standard hall detail
        $this->saveDetailOrg($applyEikenOrg->getId(), 0, $eikenLevelPrice, $theParams);
        // Save price from table EikenLevel to ApplyEikenOrgDetails main hall detail
        $this->saveDetailOrg($applyEikenOrg->getId(), 1, $eikenLevelPrice, $theParams);
    }
    
    private $paymentRepository;
    public function setPaymentMethodRepository($paymentRep = null){
        $this->paymentRepository = $paymentRep ? $paymentRep : $this->getEntityManager()->getRepository('Application\Entity\PaymentMethod');
    }
    
    public function getPaymentMethodExistValue(){
        $currentUserInfor = $this->getCurrentUserInfor();
        $orgId = $currentUserInfor['organizationId'];
        $em = $this->getEntityManager();
        if (!$this->getEikenScheduleId()) {
            return false;
        }
        if(!$this->paymentRepository){
            $this->setPaymentMethodRepository();
        }
        $paymentMethod = $this->paymentRepository->findOneBy(array(
            'organizationId' => $orgId,
            'eikenScheduleId' => $this->getEikenScheduleId()
        ));
        if(!$paymentMethod){
            return EikenConst::NOT_EXIST;
        }
        return EikenConst::EXIST;
        
    }
    
    private $orgRepository;
    public function setOrgRepository($orgRep = null){
        $this->orgRepository = $orgRep ? $orgRep : $this->getEntityManager()->getRepository('Application\Entity\Organization');
    }
    
    private $eikenScheduleRepository;
    public function setEikenScheduleRepository($eikenScheduleRep = null)
    {
        $this->eikenScheduleRepository = $eikenScheduleRep ? $eikenScheduleRep : $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule');
    }


    public function createPaymentMethod($fundingStatus, $paymentStatus, $em = false){
        $currentUserInfor = $this->getCurrentUserInfor();
        $orgId = $currentUserInfor['organizationId'];
        $em = $em ? $em : $this->getEntityManager();
        if (!$this->getEikenScheduleId()) {
            return false;
        }
        
        if(!$this->orgRepository){
            $this->setOrgRepository();
        }
        $organization = $this->orgRepository->findOneBy(array(
            'id' => $orgId,
            'isDelete' => 0
        ));
        
        if(!$this->eikenScheduleRepository){
            $this->setEikenScheduleRepository();
        }
        $eikenSchedule = $this->eikenScheduleRepository->findOneBy(array(
            'id' => $this->getEikenScheduleId(),
            'isDelete' => 0
        ));
        
        $paymentMethod = new \Application\Entity\PaymentMethod(); 
        $paymentMethod->setOrganization($organization);
        $paymentMethod->setEikenSchedule($eikenSchedule);
        $paymentMethod->setPublicFunding($fundingStatus);
        $paymentMethod->setPaymentBill($paymentStatus);
        try{
            $em->persist($paymentMethod);
            $em->flush();
            return EikenConst::SAVE_DATA_INTO_DATABASE_SUCCESS;
        }catch(Exception $e){
            return EikenConst::SAVE_DATA_INTO_DATABASE_FAIL;
        }
    }
    
    public function updatePaymentMethod($fundingStatus){
        $currentUserInfor = $this->getCurrentUserInfor();
        $orgId = $currentUserInfor['organizationId'];
        $em = $this->getEntityManager();
        if (!$this->getEikenScheduleId()) {
            return false;
        }
        $organization = $em->getReference('Application\Entity\Organization', array('id' => $orgId));
        $eikenSchedule = $em->getReference('Application\Entity\EikenSchedule', array('id' => $this->getEikenScheduleId()));

        $paymentMethod = $em->getRepository('Application\Entity\PaymentMethod')->findOneBy(array(
            'organizationId' => $this->getOrganizationId(),
            'eikenScheduleId' => $this->getEikenScheduleId()
        ));
        $paymentMethod->setOrganization($organization);
        $paymentMethod->setEikenSchedule($eikenSchedule);
        $paymentMethod->setPublicFunding($fundingStatus);
        try{
            $em->persist($paymentMethod);
            $em->flush();
            return EikenConst::SAVE_DATA_INTO_DATABASE_SUCCESS;
        }catch(Exception $e){
            return EikenConst::SAVE_DATA_INTO_DATABASE_FAIL;
        }
    }

    /**
     * Get total apply Eiken to save to total field
     */
    public function getTotal($theParams, &$hasMainHall = 0) {
        $total = 0;
        for($i = 1; $i <= 7; $i++){
            $total += !empty($theParams['hasRegisterd'.$i]) ? (int) $theParams['hasRegisterd'.$i] : 0; 
        }
        if ($total > 0) {
            $hasMainHall = 1;
        }
        for($i = 3; $i <= 7; $i++){
            $total += !empty($theParams['ExpectApplyNo'.$i]) ? (int) $theParams['ExpectApplyNo'.$i] : 0; 
        }
        return $total;
    }

    public function getActualExamDate($theParams) {
        $hasFriday = false;
        $hasSaturday = false;
        if (!empty($theParams['date5']) && $theParams['date5'] == 1) {
            $hasFriday = true;
        } else if (!empty($theParams['date5']) && $theParams['date5'] == 2) {
            $hasSaturday = true;
        }
        if (!empty($theParams['date4']) && $theParams['date4'] == 1) {
            $hasFriday = true;
        } else if (!empty($theParams['date4']) && $theParams['date4'] == 2) {
            $hasSaturday = true;
        }
        if (!empty($theParams['date3']) && $theParams['date3'] == 1) {
            $hasFriday = true;
        } else if (!empty($theParams['date3']) && $theParams['date3'] == 2) {
            $hasSaturday = true;
        }
        if (!empty($theParams['date2']) && $theParams['date2'] == 1) {
            $hasFriday = true;
        } else if (!empty($theParams['date2']) && $theParams['date2'] == 2) {
            $hasSaturday = true;
        }
        if (!empty($theParams['date1']) && $theParams['date1'] == 1) {
            $hasFriday = true;
        } else if (!empty($theParams['date1']) && $theParams['date1'] == 2) {
            $hasSaturday = true;
        }
        if ($hasFriday && !$hasSaturday) {
            return 1;
        }
        if (!$hasFriday && $hasSaturday) {
            return 2;
        }
        if ($hasFriday && $hasSaturday) {
            return 4;
        }
        return 0;
    }

    /**
     *  Refactor Author : Huy Manh(Manhnh5)
     */
    function saveDetailOrg($eikenOrgId, $hallType, $eikenLevelPrice, $theParams)
    {
        $em = $this->getEntityManager();
        if (empty($eikenOrgId)) {
            return false;
        }
        $typeName = array(
            'expectApplyNoName' => 'ExpectApplyNo'
        );
        $eikenLevel = $eikenLevelPrice[$hallType];
        $detailEikenOrg = $em->getRepository('Application\Entity\ApplyEikenOrgDetails')->findOneBy(array(
            'hallType'        => $hallType,
            'applyEikenOrgId' => $eikenOrgId
        ));
        if (empty($detailEikenOrg)) {
            $detailEikenOrg = new ApplyEikenOrgDetails();
        }
        $detailEikenOrg->setApplyEikenOrg($em->getReference('Application\Entity\ApplyEikenOrg', array('id' => $eikenOrgId)));
        $detailEikenOrg->setHallType($hallType);
        if ($hallType == 1) {
            $typeName['expectApplyNoName'] = 'MainHallExpectApplyNo';
            if (empty($theParams['hasRegisterd3'])) {
                $theParams[$typeName['expectApplyNoName'] . '3'] = 0;
            }else{
                $theParams[$typeName['expectApplyNoName'] . '3'] = $theParams['hasRegisterd3'];
            }
            if (empty($theParams['hasRegisterd4'])) {
                $theParams[$typeName['expectApplyNoName'] . '4'] = 0;
            }else{
                $theParams[$typeName['expectApplyNoName'] . '4'] = $theParams['hasRegisterd4'];
            }
            if (empty($theParams['hasRegisterd5'])) {
                $theParams[$typeName['expectApplyNoName'] . '5'] = 0;
            }else{
                $theParams[$typeName['expectApplyNoName'] . '5'] = $theParams['hasRegisterd5'];
            }
            if (empty($theParams['hasRegisterd6'])) {
                $theParams[$typeName['expectApplyNoName'] . '6'] = 0;
            }else{
                $theParams[$typeName['expectApplyNoName'] . '6'] = $theParams['hasRegisterd6'];
            }
            if (empty($theParams['hasRegisterd7'])) {
                $theParams[$typeName['expectApplyNoName'] . '7'] = 0;
            }else{
                $theParams[$typeName['expectApplyNoName'] . '7'] = $theParams['hasRegisterd7'];
            }
            $detailEikenOrg->setLev1(!empty($theParams['hasRegisterd1']) ? (int)$theParams['hasRegisterd1'] : 0);
            $detailEikenOrg->setPreLev1(!empty($theParams['hasRegisterd2']) ? (int)$theParams['hasRegisterd2'] : 0);
        }
        else {
            $detailEikenOrg->setDateExamLev5(!empty($theParams['date5']) ? $theParams['date5'] : 0);
            $detailEikenOrg->setDateExamLev4(!empty($theParams['date4']) ? $theParams['date4'] : 0);
            $detailEikenOrg->setDateExamLev3(!empty($theParams['date3']) ? $theParams['date3'] : 0);
            $detailEikenOrg->setDateExamPreLev2(!empty($theParams['date2']) ? $theParams['date2'] : 0);
            $detailEikenOrg->setDateExamLev2(!empty($theParams['date1']) ? $theParams['date1'] : 0);
        }
        
        $detailEikenOrg->setLev2(!empty($theParams[$typeName['expectApplyNoName'] . '3']) ? (int)$theParams[$typeName['expectApplyNoName'] . '3'] : 0);
        $detailEikenOrg->setPreLev2(!empty($theParams[$typeName['expectApplyNoName'] . '4']) ? (int)$theParams[$typeName['expectApplyNoName'] . '4'] : 0);
        $detailEikenOrg->setLev3(!empty($theParams[$typeName['expectApplyNoName'] . '5']) ? (int)$theParams[$typeName['expectApplyNoName'] . '5'] : 0);
        $detailEikenOrg->setLev4(!empty($theParams[$typeName['expectApplyNoName'] . '6']) ? (int)$theParams[$typeName['expectApplyNoName'] . '6'] : 0);
        $detailEikenOrg->setLev5(!empty($theParams[$typeName['expectApplyNoName'] . '7']) ? (int)$theParams[$typeName['expectApplyNoName'] . '7'] : 0);
        if ($hallType == 1) {
            $detailEikenOrg->setPriceLev1(!empty($eikenLevel[1]['price']) ? (int)$eikenLevel[1]['price'] : 0);
            $detailEikenOrg->setPricePreLev1(!empty($eikenLevel[2]['price']) ? (int)$eikenLevel[2]['price'] : 0);
        }
        $detailEikenOrg->setPriceLev2(!empty($eikenLevel[3]['price']) ? (int)$eikenLevel[3]['price'] : 0);
        $detailEikenOrg->setPricePreLev2(!empty($eikenLevel[4]['price']) ? (int)$eikenLevel[4]['price'] : 0);
        $detailEikenOrg->setPriceLev3(!empty($eikenLevel[5]['price']) ? (int)$eikenLevel[5]['price'] : 0);
        $detailEikenOrg->setPriceLev4(!empty($eikenLevel[6]['price']) ? (int)$eikenLevel[6]['price'] : 0);
        $detailEikenOrg->setPriceLev5(!empty($eikenLevel[7]['price']) ? (int)$eikenLevel[7]['price'] : 0);
        
        if(isset($theParams['isOrgDiscount']) && $theParams['isOrgDiscount'] == 1 && $hallType == 1){
            $detailEikenOrg->setDiscountLev1(intval($theParams['mainPupilDiscountKuy1']));
            $detailEikenOrg->setDiscountPreLev1(intval($theParams['mainPupilDiscountKuy2']));
            $detailEikenOrg->setDiscountLev2(intval($theParams['mainPupilDiscountKuy3']));
            $detailEikenOrg->setDiscountPreLev2(intval($theParams['mainPupilDiscountKuy4']));
            $detailEikenOrg->setDiscountLev3(intval($theParams['mainPupilDiscountKuy5']));
            $detailEikenOrg->setDiscountLev4(intval($theParams['mainPupilDiscountKuy6']));
            $detailEikenOrg->setDiscountLev5(intval($theParams['mainPupilDiscountKuy7']));
        }else if(isset($theParams['isOrgDiscount']) && $theParams['isOrgDiscount'] == 1){
            $detailEikenOrg->setDiscountLev2(intval($theParams['standPupilDiscountKuy3']));
            $detailEikenOrg->setDiscountPreLev2(intval($theParams['standPupilDiscountKuy4']));
            $detailEikenOrg->setDiscountLev3(intval($theParams['standPupilDiscountKuy5']));
            $detailEikenOrg->setDiscountLev4(intval($theParams['standPupilDiscountKuy6']));
            $detailEikenOrg->setDiscountLev5(intval($theParams['standPupilDiscountKuy7']));
        }
        
        $em->persist($detailEikenOrg);
        $em->flush();
        $em->clear();
    }

    /**
     * Get detail Fee for confirmation page
     */
    public function getDetailFee($detailEikenOrgDetails) {
        $detailFee = array(
            'TotlalFeeSt' => 0,
            'TotlalFeeMa' => 0,
            'ProcessFee' => 0,
            'ClassFee' => 0,
            'ManagementFee' => 0,
            'OtherFee' => 0
        );
        if (!empty($detailEikenOrgDetails)) {
            foreach ($detailEikenOrgDetails as $detail) {
                if (!empty($detail['hallType'])) {
                    $totalPupil = (int) $detail['lev5'] + (int) $detail['lev4'] + (int) $detail['lev3'] + (int) $detail['preLev2'] + (int) $detail['lev2'] + (int) $detail['preLev1'] + (int) $detail['lev1'];
                    //< = 9名	0円
                    if ($totalPupil <= 9) {
                        $detailFee['TotlalFeeSt'] = 0;
                    } else if ($totalPupil >= 10 && $totalPupil <= 100) {//１０名～１００名	2,250円
                        $detailFee['TotlalFeeSt'] = 2250;
                    } else { //１０１名以上/ 101 người trở lên
                        //2,250円+（志願者数-100名）×90円
                        $detailFee['TotlalFeeSt'] = 2250 + ($totalPupil - 100) * 90;
                    }
                } else {
                    //standard hall
                    $totalPupil = (int) $detail['lev5'] + (int) $detail['lev4'] + (int) $detail['lev3'] + (int) $detail['preLev2'] + (int) $detail['lev2'];
                    if ($totalPupil <= 9) {
                        $detailFee['TotlalFeeMa'] = 0;
                        $detailFee['ProcessFee'] = 0;
                        $detailFee['ClassFee'] = 0;
                        $detailFee['ManagementFee'] = 0;
                        $detailFee['OtherFee'] = 0;
                    } else if ($totalPupil >= 10 && $totalPupil <= 19) {
                        $detailFee['TotlalFeeMa'] = 2750;
                        $detailFee['ProcessFee'] = 2750;
                        $detailFee['ClassFee'] = 0;
                        $detailFee['ManagementFee'] = 0;
                        $detailFee['OtherFee'] = 0;
                    } else if ($totalPupil >= 20 && $totalPupil <= 29) {
                        $detailFee['TotlalFeeMa'] = 6250;
                        $detailFee['ProcessFee'] = 2750;
                        $detailFee['ClassFee'] = 500;
                        $detailFee['ManagementFee'] = 3000;
                        $detailFee['OtherFee'] = 0;
                    } else if ($totalPupil >= 30 && $totalPupil <= 39) {
                        $detailFee['TotlalFeeMa'] = 9750;
                        $detailFee['ProcessFee'] = 2750;
                        $detailFee['ClassFee'] = 1000;
                        $detailFee['ManagementFee'] = 6000;
                        $detailFee['OtherFee'] = 0;
                    } else if ($totalPupil >= 40 && $totalPupil <= 59) {
                        $detailFee['TotlalFeeMa'] = 13250;
                        $detailFee['ProcessFee'] = 2750;
                        $detailFee['ClassFee'] = 1500;
                        $detailFee['ManagementFee'] = 9000;
                        $detailFee['OtherFee'] = 0;
                    } else if ($totalPupil >= 60 && $totalPupil <= 79) {
                        $detailFee['TotlalFeeMa'] = 16750;
                        $detailFee['ProcessFee'] = 2750;
                        $detailFee['ClassFee'] = 2000;
                        $detailFee['ManagementFee'] = 12000;
                        $detailFee['OtherFee'] = 0;
                    } else if ($totalPupil >= 80 && $totalPupil <= 100) {
                        $detailFee['TotlalFeeMa'] = 20250;
                        $detailFee['ProcessFee'] = 2750;
                        $detailFee['ClassFee'] = 2500;
                        $detailFee['ManagementFee'] = 15000;
                        $detailFee['OtherFee'] = 0;
                    } else if ($totalPupil >= 101 && $totalPupil <= 200) {
                        $detailFee['TotlalFeeMa'] = 21040 + ($totalPupil - 101) * 290;
                        $detailFee['ProcessFee'] = 2840 + ($totalPupil - 101) * 90;
                        ;
                        $detailFee['ClassFee'] = 2525 + ($totalPupil - 101) * 25;
                        ;
                        $detailFee['ManagementFee'] = 15150 + ($totalPupil - 101) * 150;
                        ;
                        $detailFee['OtherFee'] = 525 + ($totalPupil - 101) * 25;
                    } else if ($totalPupil >= 201 && $totalPupil <= 300) {
                        $detailFee['TotlalFeeMa'] = 20750 + ($totalPupil - 100) * 290;
                        $detailFee['ProcessFee'] = 2750 + ($totalPupil - 100) * 90;
                        ;
                        $detailFee['ClassFee'] = $totalPupil / 20 * 500;
                        $detailFee['ManagementFee'] = $totalPupil / 20 * 3000;
                        $detailFee['OtherFee'] = 500 + ($totalPupil - 100) * 25;
                    } else if ($totalPupil >= 301 && $totalPupil <= 500) {
                        $detailFee['TotlalFeeMa'] = 78750 + ($totalPupil - 300) * 340;
                        $detailFee['ProcessFee'] = 20750 + ($totalPupil - 300) * 90;
                        ;
                        $detailFee['ClassFee'] = $totalPupil / 20 * 500;
                        $detailFee['ManagementFee'] = $totalPupil / 20 * 3000;
                        $detailFee['OtherFee'] = 5500 + ($totalPupil - 300) * 75;
                    } else {
                        $detailFee['TotlalFeeMa'] = 146750 + ($totalPupil - 500) * 390;
                        $detailFee['ProcessFee'] = 38750 + ($totalPupil - 500) * 90;
                        ;
                        $detailFee['ClassFee'] = $totalPupil / 20 * 500;
                        $detailFee['ManagementFee'] = $totalPupil / 20 * 3000;
                        $detailFee['OtherFee'] = 20500 + ($totalPupil - 500) * 125;
                    }
                }
            }
            return $detailFee;
        }
    }

    /**
     * Get Apply Eiken data for each Level
     */
    public function getApplyEikenLevel($eikenSchedule = 0) {
        if (!empty($eikenSchedule)) {
            if (!self::$eikenScheduleId) {
                self::$eikenScheduleId = $eikenSchedule;
            }
        }
        $eiKenApplyPerson = $this->formatApplyEikenData($this->getApplyEikenPersonal());
        $applyEikenData = array(
            'theMainHallSateLine' => array(), // contain apply eiken data for the main hall, sateline
            'theMainHallPayment' => array(), // contain apply eiken data for the main hall, payment
            'notMainHallSateLine' => array(), // contain apply eiken data for "hoi truong chuan" for the main hall, sateline
            'notMainHallPayment' => array(), // contain apply eiken data for "hoi truong chuan" for the main hall, payment
            'noOfExpectation' => array(), // for the main hall
            'noOfExpectationStandard' => array(), // for the standard hall
            'applyEikenOrg' => array()
        ); // contain apply eiken for expectation number

        $applyEikenData['applyEikenOrg'] = $this->getApplyEikenOrg();
        $eikenData = array();
        // check if is the main hall or not..
        if (!empty($eiKenApplyPerson)) {
            foreach ($eiKenApplyPerson as $personDetail) {
                if (!empty($personDetail['isSateline'])) {
                    $eikenData[$personDetail['id']] = $personDetail;
                } else {
                    // if !isSateline ==> is main hall (create new pupil)
                    $applyEikenData['noOfExpectation'][$personDetail['id']] = $personDetail;
                }
            }
        }
        $invitationSetting = $this->getInvitationSetting();
        $applyEikenData['noOfExpectationStandard'] = $this->getExpectNoByTheHall(0);
        if ($this->isTheMainHall() && $this->getSemiMainVenue($this->getOrganizationId()) != 1) {
            $applyEikenData['theMainHallPayment'] = $eikenData;
        } else {
            if (empty($invitationSetting['listEikenLevel'])) {
                $invitationSetting['listEikenLevel'] = "[]";
            }
            $listEikenLevel = json_decode($invitationSetting['listEikenLevel']);
            if ($this->isPaymentOrg()) { // if true, data is stateline
                $applyEikenData['notMainHallPayment'] = $eikenData;
                //có value mới hiện thị không thì blank
                if ($eikenData[2]['total'] == 0 || !in_array(2, $listEikenLevel)) {
                    $eikenData[2]['total'] = '';
                }
                if ($eikenData[1]['total'] == 0 || !in_array(1, $listEikenLevel)) {
                    $eikenData[1]['total'] = '';
                }
                $applyEikenData['notMainHallPayment'][1] = $eikenData[1];
                $applyEikenData['notMainHallPayment'][2] = $eikenData[2];
                for ($i = 3; $i <= 7; $i++) {
                    $applyEikenData['notMainHallPayment'][$i] = array(
                        'id' => $i,
                        'total' => '',
                        'isSateline' => 0
                    );
                }
            } else {
                $applyEikenData['notMainHallPayment'] = $eikenData;

                if ($eikenData[1]['total'] == 0 || !in_array(1, $listEikenLevel)) {
                    $eikenData[1]['total'] = '';
                }
                $applyEikenData['theMainHallPayment'][1] = $eikenData[1];

                if ($eikenData[2]['total'] == 0 || !in_array(2, $listEikenLevel)) {
                    $eikenData[2]['total'] = '';
                }
                $applyEikenData['theMainHallPayment'][2] = $eikenData[2];

                for ($i = 3; $i <= 7; $i++) {
                    $applyEikenData['theMainHallPayment'][$i] = array(
                        'id' => $i,
                        'total' => '',
                        'isSateline' => 0
                    );
                }
            }
        }
        $applyEikenData = $this->getRealExpectNo($applyEikenData);
        $applyEikenData['applyEikenStatus'] = $this->getApplyEikenStatus();
        $applyEikenData['isSentStandardHall'] = $this->getIsSentStandardHall();
        $applyEikenData['isSentMainHall'] = $this->getIsSentMainHall();
        if (!empty($applyEikenData['notMainHallPayment'][1]['total'])) {
            $applyEikenData['notMainHallPayment'][1]['total'] = 0;
        }
        if (!empty($applyEikenData['notMainHallPayment'][2]['total'])) {
            $applyEikenData['notMainHallPayment'][2]['total'] = 0;
        }
        if (!empty($applyEikenData['notMainHallSateLine'][1]['total'])) {
            $applyEikenData['notMainHallSateLine'][1]['total'] = 0;
        }
        if (!empty($applyEikenData['notMainHallSateLine'][2]['total'])) {
            $applyEikenData['notMainHallSateLine'][2]['total'] = 0;
        }

        $applyEikenData['totalKyuPayment']['mainHall'] = $this->getTotalKyuPaymentInfo(1);
        $applyEikenData['totalKyuPayment']['standardHall'] = $this->getTotalKyuPaymentInfo(0);
        $applyEikenData['hallType'] = isset($invitationSetting['hallType']) ? $invitationSetting['hallType'] : '';
        return $applyEikenData;
    }

    public function detailPriceForConfirmation() {
        $eikenOrgDetails = $this->getApplyEikenOrgDetails();
        $detailPrice = array();
        $totalPrice = 0;
        $totalDiscountPrice = 0;
        if (!empty($eikenOrgDetails)) {
            foreach ($eikenOrgDetails as $key =>  $detail) {
                if($key < 2){
                    $totalPrice += (int) $detail['lev5'] * (int) $detail['priceLev5'];
                    $totalPrice += (int) $detail['lev4'] * (int) $detail['priceLev4'];
                    $totalPrice += (int) $detail['lev3'] * (int) $detail['priceLev3'];
                    $totalPrice += (int) $detail['preLev2'] * (int) $detail['pricePreLev2'];
                    $totalPrice += (int) $detail['lev2'] * (int) $detail['priceLev2'];
                    $totalPrice += (int) $detail['preLev1'] * (int) $detail['pricePreLev1'];
                    $totalPrice += (int) $detail['lev1'] * (int) $detail['priceLev1'];
                    
                    if($key === 1){
                        $totalDiscountPrice += (int) $detail['lev5'] * (int) $eikenOrgDetails[0]['priceLev5'];
                        $totalDiscountPrice += (int) $detail['lev4'] * (int) $eikenOrgDetails[0]['priceLev4'];
                        $totalDiscountPrice += (int) $detail['lev3'] * (int) $eikenOrgDetails[0]['priceLev3'];
                        $totalDiscountPrice += (int) $detail['preLev2'] * (int) $eikenOrgDetails[0]['pricePreLev2'];
                        $totalDiscountPrice += (int) $detail['lev2'] * (int) $eikenOrgDetails[0]['priceLev2'];
                        $totalDiscountPrice += (int) $detail['preLev1'] * (int) $detail['pricePreLev1'];
                        $totalDiscountPrice += (int) $detail['lev1'] * (int) $detail['priceLev1'];
                    }
                    if($key === 0){
                        $totalDiscountPrice += (int) $detail['lev5'] * (int) $detail['priceLev5'];
                        $totalDiscountPrice += (int) $detail['lev4'] * (int) $detail['priceLev4'];
                        $totalDiscountPrice += (int) $detail['lev3'] * (int) $detail['priceLev3'];
                        $totalDiscountPrice += (int) $detail['preLev2'] * (int) $detail['pricePreLev2'];
                        $totalDiscountPrice += (int) $detail['lev2'] * (int) $detail['priceLev2'];
                        $totalDiscountPrice += (int) $detail['preLev1'] * (int) $detail['pricePreLev1'];
                        $totalDiscountPrice += (int) $detail['lev1'] * (int) $detail['priceLev1'];
                    }

                    $data = array(
                        7 => array(
                            'name' => '5級',
                            'total' => $detail['lev5'] . '人',
                            'price' => number_format(((int) $detail['priceLev5']), 0, '', ',') . '円',
                            'totalPrice' => number_format(((int) $detail['lev5'] * (int) $detail['priceLev5']), 0, '', ',') . '円',
                            'discountPrice' => number_format(((int) $eikenOrgDetails[0]['priceLev5']), 0, '', ',') . '円',
                            'totalDiscountPrice' => number_format(((int) $detail['lev5'] * (int) $eikenOrgDetails[0]['priceLev5']), 0, '', ',') . '円'
                        ),
                        6 => array(
                            'name' => '4級',
                            'total' => $detail['lev4'] . '人',
                            'price' => number_format(((int) $detail['priceLev4']), 0, '', ',') . '円',
                            'totalPrice' => number_format(((int) $detail['lev4'] * (int) $detail['priceLev4']), 0, '', ',') . '円',
                            'discountPrice' => number_format(((int) $eikenOrgDetails[0]['priceLev4']), 0, '', ',') . '円',
                            'totalDiscountPrice' => number_format(((int) $detail['lev4'] * (int) $eikenOrgDetails[0]['priceLev4']), 0, '', ',') . '円'
                        ),
                        5 => array(
                            'name' => '3級',
                            'total' => $detail['lev3'] . '人',
                            'price' => number_format(((int) $detail['priceLev3']), 0, '', ',') . '円',
                            'totalPrice' => number_format(((int) $detail['lev3'] * (int) $detail['priceLev3']), 0, '', ',') . '円',
                            'discountPrice' => number_format(((int) $eikenOrgDetails[0]['priceLev3']), 0, '', ',') . '円',
                            'totalDiscountPrice' => number_format(((int) $detail['lev3'] * (int) $eikenOrgDetails[0]['priceLev3']), 0, '', ',') . '円'
                        ),
                        4 => array(
                            'name' => '準2級',
                            'total' => $detail['preLev2'] . '人',
                            'price' => number_format(((int) $detail['pricePreLev2']), 0, '', ',') . '円',
                            'totalPrice' => number_format(((int) $detail['preLev2'] * (int) $detail['pricePreLev2']), 0, '', ',') . '円',
                            'discountPrice' => number_format(((int) $eikenOrgDetails[0]['pricePreLev2']), 0, '', ',') . '円',
                            'totalDiscountPrice' => number_format(((int) $detail['preLev2'] * (int) $eikenOrgDetails[0]['pricePreLev2']), 0, '', ',') . '円'
                        ),
                        3 => array(
                            'name' => '2級',
                            'total' => $detail['lev2'] . '人',
                            'price' => number_format(((int) $detail['priceLev2']), 0, '', ',') . '円',
                            'totalPrice' => number_format(((int) $detail['lev2'] * (int) $detail['priceLev2']), 0, '', ',') . '円',
                            'discountPrice' => number_format(((int) $eikenOrgDetails[0]['priceLev2']), 0, '', ',') . '円',
                            'totalDiscountPrice' => number_format(((int) $detail['lev2'] * (int) $eikenOrgDetails[0]['priceLev2']), 0, '', ',') . '円'
                        ),
                        2 => array(
                            'name' => '準1級',
                            'total' => $detail['preLev1'] . '人',
                            'price' => number_format(((int) $detail['pricePreLev1']), 0, '', ',') . '円',
                            'totalPrice' => number_format(((int) $detail['preLev1'] * (int) $detail['pricePreLev1']), 0, '', ',') . '円',
                            'discountPrice' => number_format(((int) $detail['pricePreLev1']), 0, '', ',') . '円',
                            'totalDiscountPrice' => number_format(((int) $detail['preLev1'] * (int) $detail['pricePreLev1']), 0, '', ',') . '円'
                        ),
                        1 => array(
                            'name' => '1級',
                            'total' => $detail['lev1'] . '人',
                            'price' => number_format(((int) $detail['priceLev1']), 0, '', ',') . '円',
                            'totalPrice' => number_format(((int) $detail['lev1'] * (int) $detail['priceLev1']), 0, '', ',') . '円',
                            'discountPrice' => number_format(((int) $detail['priceLev1']), 0, '', ',') . '円',
                            'totalDiscountPrice' => number_format(((int) $detail['lev1'] * (int) $detail['priceLev1']), 0, '', ',') . '円'
                        ),
                    );
                    // LangDD: fix F1GJIEM-1622 - reverse sorting of kyus
                    $data = array_reverse($data);
                    if (empty($detail['hallType'])) {
                        $detailPrice['standardHall'] = $data;
                    } else {
                        $detailPrice['mainHall'] = $data;
                    }
                }
            }
            $detailPrice['totalPrice'] = $totalPrice;
            $detailPrice['totalDiscountPrice'] = $totalDiscountPrice;
            $detailPrice['totalDecreasePrice'] = $totalPrice - $totalDiscountPrice;  
            return $detailPrice;
        }
    }

    public function formatApplyEikenData($eikenData = array()) {
        $listEikenLevel = $this->getEikenLevel();
        $defaultData = array();
        foreach ($listEikenLevel as $levelId => $data) {
            $defaultData[$levelId][] = array(
                'id' => $levelId,
                'total' => 0,
                'isSateline' => 0
            );
            $defaultData[$levelId][] = array(
                'id' => $levelId,
                'total' => 0,
                'isSateline' => 1
            );
        }

        if (!empty($eikenData)) {
            foreach ($eikenData as $item) {
                if (!empty($item['isSateline'])) {
                    $defaultData[$item['id']][1] = $item;
                } else {
                    $defaultData[$item['id']][0] = $item;
                }
            }
        }
        $eikenData = array();
        foreach ($defaultData as $item) {
            $eikenData[] = $item[0];
            $eikenData[] = $item[1];
        }
        return $eikenData;
    }

    public function getExpectNoByTheHall($hallType = 0) {
        $eikenOrgDetail = $this->getApplyEikenOrgDetail($hallType);
        if (!empty($eikenOrgDetail)) {
            //TODO need get from database for eiken Level
            return array(
                'origin' => $eikenOrgDetail,
                3 => array('total' => $eikenOrgDetail['lev2']),
                4 => array('total' => $eikenOrgDetail['preLev2']),
                5 => array('total' => $eikenOrgDetail['lev3']),
                6 => array('total' => $eikenOrgDetail['lev4']),
                7 => array('total' => $eikenOrgDetail['lev5']),
            );
        }
        return array();
    }

    /**
     * merge current no of sateline into new apply eiken pupil
     * apply for the main hall only
     */
    public function getRealExpectNo($applyEikenData = array()) {
        $this->getApplyEikenStatus();
        $hasRegisterd = array();
        $totalRegistereds = 0;
        foreach ($applyEikenData['noOfExpectation'] as $level => &$detail) {
            $totalRegistered = !empty($detail['totalRegister']) ? (int) $detail['totalRegister'] : 0;
            $totalRegisteredSateLine = !empty($applyEikenData['theMainHallSateLine'][$level]['totalRegister']) ? (int) $applyEikenData['theMainHallSateLine'][$level]['totalRegister'] : (!empty($applyEikenData['theMainHallPayment'][$level]['totalRegister']) ? (int) $applyEikenData['theMainHallPayment'][$level]['totalRegister'] : 0);
            $totalExpectation = $totalRegistered + $totalRegisteredSateLine;
            if ($totalExpectation == 0) {
                if (empty($this->getApplyEikenStatus())) {
                    $totalDeleted = !empty($detail['totalDeleted']) ? (int) $detail['totalDeleted'] : 0;
                    $totalDeletedSateLine = !empty($applyEikenData['theMainHallSateLine'][$level]['totalDeleted']) ? (int) $applyEikenData['theMainHallSateLine'][$level]['totalDeleted'] : (!empty($applyEikenData['theMainHallPayment'][$level]['totalDeleted']) ? (int) $applyEikenData['theMainHallPayment'][$level]['totalDeleted'] : 0);
                    $totalDeleted += $totalDeletedSateLine;
                    $detail['total'] = (!empty($applyEikenData['theMainHallSateLine'][$level]['total']) ? (int) $applyEikenData['theMainHallSateLine'][$level]['total'] : (!empty($applyEikenData['theMainHallPayment'][$level]['total']) ? (int) $applyEikenData['theMainHallPayment'][$level]['total'] : 0)) - $totalDeleted;
                    if ($detail['total'] < 0) {
                        $detail['total'] = 0;
                    }
                } else {
                    $detail['total'] = 0;
                }
            } else {
                $hasRegisterd[$level] = $totalExpectation;
                $detail['total'] = $totalExpectation;
                $totalRegistereds += $totalExpectation;
            }
        }
        $applyEikenData['hasRegisterd'] = $hasRegisterd;
        $applyEikenData['totalRegistereds'] = $totalRegistereds;
        return $applyEikenData;
    }

    public function getRealExpectNoForConfirmation($applyEikenData) {
        if (!empty($applyEikenData['noOfExpectation'])) {
            foreach ($applyEikenData['noOfExpectation'] as $level => &$detail) {
                $totalRegistered = !empty($detail['totalRegister']) ? (int) $detail['totalRegister'] : 0;
                $totalRegisteredSateLine = !empty($applyEikenData['theMainHallSateLine'][$level]['totalRegister']) ? (int) $applyEikenData['theMainHallSateLine'][$level]['totalRegister'] : (!empty($applyEikenData['theMainHallPayment'][$level]['totalRegister']) ? (int) $applyEikenData['theMainHallPayment'][$level]['totalRegister'] : 0);
                $detail['total'] = $totalRegistered + $totalRegisteredSateLine;
            }
        }
        return $applyEikenData;
    }

    public function updateEikenStatus() {
        $em = $this->getEntityManager();
        $ApplyEikenOrg = $em->getRepository('Application\Entity\ApplyEikenOrg')->findOneBy(array(
            'organizationId' => $this->getOrganizationId(),
            'eikenScheduleId' => $this->getEikenScheduleId()
        ));
        //Dev-san
        if (!empty($ApplyEikenOrg) && ($ApplyEikenOrg->getStatus() == 'DRAFT' || empty($ApplyEikenOrg->getStatus()))) {
            $ApplyEikenOrg->setStatus('DRAFT');
            $em->persist($ApplyEikenOrg);
            $em->flush();
        }
    }

    /**
     * Submit data to UKESTUKE
     * TODO need to check again after have real API
     */
    public function submitApplyEikenOrgToUkestuke($managerName = '', $logData) {
        $countByKyu = array();
        $applyEikenOrgDetail = $this->getApplyEikenOrgDetails(-1);
        //update EikenStatus before submit to Ukestuke
        $this->updateEikenStatus();
        $applyEikenOrg = $this->getApplyEikenOrg();
        $currentUserInfor = $this->getCurrentUserInfor();
        $jishiyoubi = null;
        if (!empty($applyEikenOrg['typeExamDate'])) {
            if ($applyEikenOrg['typeExamDate'] == 1) {
                //trong JIEM-Portal 1: friday, 2: saturday, 3: sunday, 4: the both friday & saturday -  Eikusture 1: sunday 2: saturday, 3: friday, 4: the same
                $applyEikenOrg['typeExamDate'] = 3;
            } else if ($applyEikenOrg['typeExamDate'] == 3) {
                $applyEikenOrg['typeExamDate'] = 1;
            }
        }
        $currentNoOfApiCalls = !empty($applyEikenOrg['noApiCalls']) ? (int) $applyEikenOrg['noApiCalls'] : 0;
        $callApiStatus1 = (object) array('kekka' => '10');
        $callApiStatus2 = (object) array('kekka' => '10');
        $callApiStatus3 = (object) array('kekka' => '10');

        $em = $this->getEntityManager();
        $isSendStandardHall = 0;
        $isSendMainHall = 0;
        $notSendMainHall = false;
        $previousPupilSubmit = array();
        if (!empty($applyEikenOrgDetail)) {
            $ukestukeData1 = array(); //data hội trường chuẩn
            $ukestukeData2 = array(); //data hội trường chính
            $logMainHall = array();
            $logStandardHall = array();
            foreach ($applyEikenOrgDetail as $detail) {
                if (empty($detail['hallType'])) {
                    $jishiyoubi_g3 = $jishiyoubi_g4 = $jishiyoubi_g5 = $jishiyoubi_g6 = $jishiyoubi_g7 = (($applyEikenOrg['typeExamDate'] != 4) ? $applyEikenOrg['typeExamDate'] : null);
                    if ($applyEikenOrg['typeExamDate'] == 4) {
                        $jishiyoubi_g3 = ($detail['dateExamLev2'] == 1) ? 3 : $detail['dateExamLev2'];
                        $jishiyoubi_g4 = ($detail['dateExamPreLev2'] == 1) ? 3 : $detail['dateExamPreLev2'];
                        $jishiyoubi_g5 = ($detail['dateExamLev3'] == 1) ? 3 : $detail['dateExamLev3'];
                        $jishiyoubi_g6 = ($detail['dateExamLev4'] == 1) ? 3 : $detail['dateExamLev4'];
                        $jishiyoubi_g7 = ($detail['dateExamLev5'] == 1) ? 3 : $detail['dateExamLev5'];
                    }
                }
                //Standard Hall
                if (empty($detail['hallType'])) { // hoi truong chuan
                    $previousPupilSubmit = $detail;
                    //validate data of Standard Hall
                    if (!$this->validateStandardHall($detail)) {
                        continue;
                    }
                    //continue;
                    $ukestukeData1 = array(
                        "nendo" => date('Y'), // Năm thi
                        "kai" => $this->getKaiNumber(),
                        "dantaino" => $this->getOrganizationNumber(),
                        "moshikomiseq" => ($currentNoOfApiCalls + 1),
                        //"uketsukeno" => null,
                        "moshikomijokyo" => "4",
                        "uketsukedt" => date('Y/m/d H:i:s'), // hard code
                        //                           "jukenchicd" => "3101", // hard code
                        "kaijokbn" => "1", ////  hoi truong test 1: chuan, 2 chinhs
                        "junmoshikomikbn" => "1", //hard code
                        "jishiyoubi" => $applyEikenOrg['typeExamDate'],
                        "tapesu" => $applyEikenOrg['cd'],
                        "ninzu_g1jun" => null, // So nguoi thi o hoi truong chuan  cho kyu 1
                        "ninzu_g1hon" => null, // So nguoi thi o hoi truong chinh cho kyu 1
                        "ninzu_g2jun" => null, // So nguoi thi o hoi truong chuan  cho kyu Pre1
                        "ninzu_g2hon" => null, // Số người thi hội trường chính Pre-1,
                        "ninzu_g3jun" => $detail['lev2'] - (!empty($detail['oldLev2']) ? (int) $detail['oldLev2'] : 0), // Số người thi hội trường chuẩn kyu2
                        "ninzu_g3hon" => null, // Số người thi hội trường chính kyu2
                        "ninzu_g4jun" => $detail['preLev2'] - (!empty($detail['oldPreLev2']) ? (int) $detail['oldPreLev2'] : 0), // Số người thi hội trường chuẩn pre2
                        "ninzu_g4hon" => null, // Số người thi hội trường chính Pre-2
                        "ninzu_g5jun" => $detail['lev3'] - (!empty($detail['oldLev3']) ? (int) $detail['oldLev3'] : 0), // Số người thi hội trường chuẩn kyu3
                        "ninzu_g5hon" => null, // Số người thi hội trường chính kyu3
                        "ninzu_g6jun" => $detail['lev4'] - (!empty($detail['oldLev4']) ? (int) $detail['oldLev4'] : 0), // Số người thi hội trường chuẩn kyu 4
                        "ninzu_g6hon" => null, // Số người thi hội trường chính kyu4
                        "ninzu_g7jun" => $detail['lev5'] - (!empty($detail['oldLev5']) ? (int) $detail['oldLev5'] : 0), // Số người thi hội trường chuẩn kyu5
                        "ninzu_g7hon" => null, // Số người thi hội trường chính kyu5,
                        //"sekininsha" => null,//hard code
                        "rentai" => $managerName? $managerName:$applyEikenOrg['managerName'],
                        "chushiflg" => null, //hard code
                        "chushikbn" => null, //hard code
                        "chushiriyu" => null, //hard code
                        "sakuseiflg" => null, //hard code
                        "sakuseidt" => null, //hard code
                        "henkoflg" => null, //hard code
                        "createdt" => $detail['insertAt']->format('Y/m/d H:i:s'),
                        "updatedt" => $detail['updateAt']->format('Y/m/d H:i:s'),
                        "kakuteiflg" => 1, //hard code
                        //"sekininsha" => $applyEikenOrg['firtNameKanji'] . $applyEikenOrg['lastNameKanji'],
                        "sekininsha" => null,
                        //                           "sekininsha_sei" => $applyEikenOrg['firtNameKanji'],
                        //                           "sekininsha_mei" => $applyEikenOrg['lastNameKanji'],
                        "jishikbn" => $detail['locationType'] ? 2 : 1, //1:単独実施/ Single、2:合同実施/ Combination
                        "goudoukbn" => !empty($detail['locationType1']) ? ($detail['locationType1'] == 1 ? 2 : 1) : null,
                        "gouryudantaino" => $detail['locationType1'] == 2 ? $detail['eikenOrgNo1'] : null,
                        "kyusyudantai" => $detail['locationType1'] == 1 ? $detail['eikenOrgNo2'] : null,
                        "jishiyoubi_g1" => null,
                        "jishiyoubi_g2" => null,
                        "jishiyoubi_g3" => isset($jishiyoubi_g3) ? $jishiyoubi_g3 : null,
                        "jishiyoubi_g4" => isset($jishiyoubi_g4) ? $jishiyoubi_g4 : null,
                        "jishiyoubi_g5" => isset($jishiyoubi_g5) ? $jishiyoubi_g5 : null,
                        "jishiyoubi_g6" => isset($jishiyoubi_g6) ? $jishiyoubi_g6 : null,
                        "jishiyoubi_g7" => isset($jishiyoubi_g7) ? $jishiyoubi_g7 : null
                    );
                    $logStandardHall = array(
                        'lev2' => $detail['lev2'],
                        'preLev2' => $detail['preLev2'],
                        'lev3' => $detail['lev3'],
                        'lev4' => $detail['lev4'],
                        'lev5' => $detail['lev5'],
                        'oldLev2' => $detail['oldLev2'],
                        'oldPreLev2' => $detail['oldPreLev2'],
                        'oldLev3' => $detail['oldLev3'],
                        'oldLev4' => $detail['oldLev4'],
                        'oldLev5' => $detail['oldLev5'],
                    );
                } else {
                    // hoi truong chinh
                    // Get list pupils
                    $pupilList = $em->getRepository('Application\Entity\ApplyEikenLevel')->getListApplyEikenLevel($this->getOrganizationId(), $this->getEikenScheduleId());
                    // If there is not any pupil in list ==> should not send
                    if (empty($pupilList)) {
                        continue;
                    }
                    $preparedData = $this->prepareDataPupilList($pupilList);

                    $countByKyu = $preparedData['countByKyu'];
                    $ukestukeData2 = array(
                        "nendo" => date('Y'), // Năm thi - Value = selected year
                        "kai" => $this->getKaiNumber(), //Value = selected 回
                        "dantaino" => $this->getOrganizationNumber(), // Value = organization number of current user.
                        "moshikomiseq" => ($currentNoOfApiCalls + 1), //The first time organization apply, value = "1", then the next application form 2,3,4,5…
                        "moshikomijokyo" => "4", //"Fix value = “4"". Meaning 申込み確\confirmed"
                        "uketsukedt" => date('Y/m/d H:i:s'), // hard code
                        //                           "jukenchicd" => "3101",//"Value = value of [受験地コード] column in the 受験地番号マスタ file In release 1.1, fix value = ""3101""."
                        "kaijokbn" => "2", ////  hoi truong test 1: chuan, 2 chinhs Fix value = “2”
                        "junmoshikomikbn" => null, //Fix value = “NULL"
                        "jishiyoubi" => null, //Fix value = “1"
                        //"tapesu" => null,//Value = "NULL"
                        //"ninzu_g1jun" => null, // So nguoi thi o hoi truong chuan  cho kyu 1
                        "ninzu_g1hon" => $countByKyu['lev1'] != 0 ? $countByKyu['lev1'] : null, // So nguoi thi o hoi truong chinh cho kyu 1
                        //"ninzu_g2jun" => null,// So nguoi thi o hoi truong chuan  cho kyu Pre1
                        "ninzu_g2hon" => $countByKyu['preLev1'] != 0 ? $countByKyu['preLev1'] : null, // Số người thi hội trường chính Pre-1,
                        //"ninzu_g3jun" => null,// Số người thi hội trường chuẩn kyu2
                        "ninzu_g3hon" => $countByKyu['lev2'] != 0 ? $countByKyu['lev2'] : null, // Số người thi hội trường chính kyu2
                        //"ninzu_g4jun" => null,// Số người thi hội trường chuẩn pre2
                        "ninzu_g4hon" => $countByKyu['preLev2'] != 0 ? $countByKyu['preLev2'] : null, // Số người thi hội trường chính Pre-2
                        //"ninzu_g5jun" => null,// Số người thi hội trường chuẩn kyu3
                        "ninzu_g5hon" => $countByKyu['lev3'] != 0 ? $countByKyu['lev3'] : null, // Số người thi hội trường chính kyu3
                        //"ninzu_g6jun" => null,// Số người thi hội trường chuẩn kyu 4
                        "ninzu_g6hon" => $countByKyu['lev4'] != 0 ? $countByKyu['lev4'] : null, // Số người thi hội trường chính kyu4
                        //"ninzu_g7jun" => null,// Số người thi hội trường chuẩn kyu5
                        "ninzu_g7hon" => $countByKyu['lev5'] != 0 ? $countByKyu['lev5'] : null, // Số người thi hội trường chính kyu5,
                        //"sekininsha" => null,//hard code
                        "rentai" => $applyEikenOrg['managerName'] != ''? $applyEikenOrg['managerName']:null, //Value = [氏名（漢字）] 60 characters fullwidth
                        "chushiflg" => null, //hard code
                        "chushikbn" => null, //hard code
                        "chushiriyu" => null, //hard code
                        "sakuseiflg" => null, //Fix value =1
                        "sakuseidt" => null, //Value= created date and time Format: “YYYY/MM/DD HH:MM:SS.
                        "henkoflg" => null, //There are two cases: + If value of [申込人数] >"0", value = "1" + Else, value = "0"
                        "createdt" => $detail['insertAt']->format('Y/m/d H:i:s'),
                        "updatedt" => $detail['updateAt']->format('Y/m/d H:i:s'),
                        "kakuteiflg" => 1, //Fix value =1
                        "sekininsha" => null,
                        //                           "sekininsha_sei" => $applyEikenOrg['firtNameKanji'],
                        //                           "sekininsha_mei" => $applyEikenOrg['lastNameKanji'],
                        "jishikbn" => null, //Fix value NULL
                        "goudoukbn" => null, //fix value NULL
                        "gouryudantaino" => null, //fix value NULL
                        "kyusyudantai" => null, //fix value NULL
                        "jishiyoubi_g1" => null, //Value= "1"
                        "jishiyoubi_g2" => null, //Value= "1"
                        "jishiyoubi_g3" => null, //Value= "1"
                        "jishiyoubi_g4" => null, //Value= "1"
                        "jishiyoubi_g5" => null, //Value= "1"
                        "jishiyoubi_g6" => null, //Value= "1"
                        "jishiyoubi_g7" => null//Value= "1"
                    );
                    
                    $logMainHall = array(
                        'lev1' => $detail['lev1'],
                        'preLev1' => $detail['preLev1'],
                        'lev2' => $detail['lev2'],
                        'preLev2' => $detail['preLev2'],
                        'lev3' => $detail['lev3'],
                        'lev4' => $detail['lev4'],
                        'lev5' => $detail['lev5'],
                        'oldLev1' => $detail['lev1'] - $countByKyu['lev1'],
                        'oldPreLev1' => $detail['preLev1'] - $countByKyu['preLev1'],
                        'oldLev2' => $detail['lev2'] - $countByKyu['lev2'],
                        'oldPreLev2' => $detail['preLev2'] - $countByKyu['preLev2'],
                        'oldLev3' => $detail['lev3'] - $countByKyu['lev3'],
                        'oldLev4' => $detail['lev4'] - $countByKyu['lev4'],
                        'oldLev5' => $detail['lev5'] - $countByKyu['lev5'],
                    );

                    if ($countByKyu['lev1'] == 0 && $countByKyu['preLev1'] == 0 && $countByKyu['lev2'] == 0 && $countByKyu['preLev2'] == 0 && $countByKyu['lev3'] == 0 && $countByKyu['lev4'] == 0 && $countByKyu['lev5'] == 0){
                        $notSendMainHall = true;
                        $logMainHall = array();
                    }
                }
            }

            if (!empty($ukestukeData1) && !empty($ukestukeData2)) { // gửi data cả 2 hội trường chính & chuẩn
                //Gộp cả 2 data hội trường chính & chuẩn vào 1 mảng, những field dùng chung thì lấy theo hội trường chuẩn
                $ukestukeData1['ninzu_g1hon'] = $ukestukeData2['ninzu_g1hon'];
                $ukestukeData1['ninzu_g2hon'] = $ukestukeData2['ninzu_g2hon'];
                $ukestukeData1['ninzu_g3hon'] = $ukestukeData2['ninzu_g3hon'];
                $ukestukeData1['ninzu_g4hon'] = $ukestukeData2['ninzu_g4hon'];
                $ukestukeData1['ninzu_g5hon'] = $ukestukeData2['ninzu_g5hon'];
                $ukestukeData1['ninzu_g6hon'] = $ukestukeData2['ninzu_g6hon'];
                $ukestukeData1['ninzu_g7hon'] = $ukestukeData2['ninzu_g7hon'];

                $callApiStatus1 = $callApiStatus2 = $this->callAPIToUkestuke($ukestukeData1);
                if (!empty($callApiStatus2->kekka) && $callApiStatus2->kekka == '10') {
                    $currentNoOfApiCalls++;
                    // LangDD - Get and send list applied pupil list in main hall to Ukesuke
                    $callApiStatus3 = $this->sendAppliedPupilInMainHall($pupilList, $preparedData['apiMapped']);
                    $this->updateCurrentNoExpectation($previousPupilSubmit);
                    $isSendStandardHall = 1;
                    $isSendMainHall = 1;
                    $this->saveLogApplyEiken($logMainHall, $logStandardHall, $logData);
                }
            } else if (!empty($ukestukeData1)) {//thông tin hội trường chuẩn
                $callApiStatus1 = $this->callAPIToUkestuke($ukestukeData1);
                if (!empty($callApiStatus1) && $callApiStatus1->kekka == '10') {
                    $currentNoOfApiCalls++;
                    $this->updateCurrentNoExpectation($previousPupilSubmit);
                    //                     $ApplyEikenOrgEntity->setIsSentStandardHall(1);
                    $isSendStandardHall = 1;
                    $this->saveLogApplyEiken($logMainHall, $logStandardHall, $logData);
                }
            } else if (!empty($ukestukeData2)) {//thông tin hội trường chính
                if (!empty($applyEikenOrg['cd'])) {
                    //đã gửi thông tin hội trường chuẩn lên Ukestuke thì thông tin hội trường chính gửi cả thông tin hội trường chuẩn cd, abc
                    $ukestukeData2['tapesu'] = $applyEikenOrg['cd'];
                    $ukestukeData2['kaijokbn'] = "1";
                    unset($ukestukeData2['jishiyoubi_g1']);
                    unset($ukestukeData2['jishiyoubi_g2']);
                    $ukestukeData2['jishiyoubi_g3'] = isset($jishiyoubi_g3) ? $jishiyoubi_g3 : null;
                    $ukestukeData2['jishiyoubi_g4'] = isset($jishiyoubi_g4) ? $jishiyoubi_g4 : null;
                    $ukestukeData2['jishiyoubi_g5'] = isset($jishiyoubi_g5) ? $jishiyoubi_g5 : null;
                    $ukestukeData2['jishiyoubi_g6'] = isset($jishiyoubi_g6) ? $jishiyoubi_g6 : null;
                    $ukestukeData2['jishiyoubi_g7'] = isset($jishiyoubi_g7) ? $jishiyoubi_g7 : null;

                    $ukestukeData2['jishikbn'] = $previousPupilSubmit['locationType'] ? 2 : 1; //1:単独実施/ Single、2:合同実施/ Combination
                    $ukestukeData2['goudoukbn'] = !empty($previousPupilSubmit['locationType1']) ? ($previousPupilSubmit['locationType1'] == 1 ? 2 : 1) : null;
                    $ukestukeData2['gouryudantaino'] = (!empty($previousPupilSubmit['locationType1']) && $previousPupilSubmit['locationType1'] == 2) ? $previousPupilSubmit['eikenOrgNo1'] : null;
                    $ukestukeData2['kyusyudantai'] = (!empty($previousPupilSubmit['locationType1']) && $previousPupilSubmit['locationType1'] == 1) ? $previousPupilSubmit['eikenOrgNo2'] : null;
                    $ukestukeData2['jishiyoubi'] = $applyEikenOrg['typeExamDate'];
                    $ukestukeData2['junmoshikomikbn'] = 1;
                }
                if (!$notSendMainHall) {
                    $callApiStatus2 = $this->callAPIToUkestuke($ukestukeData2);
                    if (!empty($callApiStatus2->kekka) && $callApiStatus2->kekka == '10') {
                        $currentNoOfApiCalls++;
                        // LangDD - Get and send list applied pupil list in main hall to Ukesuke
                        $callApiStatus3 = $this->sendAppliedPupilInMainHall($pupilList, $preparedData['apiMapped']);
                        $isSendMainHall = 1;
                        $this->saveLogApplyEiken($logMainHall, $logStandardHall, $logData);
                    }
                } else {
                    $callApiStatus3 = $this->sendAppliedPupilInMainHall($pupilList, $preparedData['apiMapped']);
                }
            }elseif($logData['oldStatusRefund'] != $logData['params']['refundStatus']){
                $this->saveLogApplyEiken($logMainHall, $logStandardHall, $logData);
            }
        }
        /** @var ApplyEikenOrg $applyEikenOrgEntity */
        $applyEikenOrgEntity = $em->getRepository('Application\Entity\ApplyEikenOrg')->findOneBy(array(
            'organizationId' => $this->getOrganizationId(),
            'eikenScheduleId' => $this->getEikenScheduleId()
        ));
        $applyEikenOrgEntity->setNoApiCalls($currentNoOfApiCalls);
        if ($isSendStandardHall && empty($applyEikenOrgEntity->getIsSentStandardHall())) {
            $applyEikenOrgEntity->setIsSentStandardHall($isSendStandardHall);
        }
        if ($isSendMainHall && empty($applyEikenOrgEntity->getIsSentMainHall())) {
            $applyEikenOrgEntity->setIsSentMainHall($isSendMainHall);
        }
        if (!empty($callApiStatus1->kekka) && !empty($callApiStatus2->kekka) && !empty($callApiStatus3->kekka) && $callApiStatus1->kekka == '10' && $callApiStatus2->kekka == '10' && !empty($callApiStatus3) && $callApiStatus3->kekka == '10') {
            if (!empty($applyEikenOrgEntity->getStatus()) && $applyEikenOrgEntity->getStatus() == 'SUBMITTED') {
                $applyEikenOrgEntity->setApplyStatus('変更通知');
            } else {
                $applyEikenOrgEntity->setApplyStatus('初回通知');
            }
            $applyEikenOrgEntity->setStatus('SUBMITTED');
            if(empty($applyEikenOrgEntity->getRegistrationDate())) {
                $applyEikenOrgEntity->setRegistrationDate(new \DateTime("now"));
            }
            $applyEikenOrgEntity->setExecutorName($currentUserInfor['firstNameKanji'].$currentUserInfor['lastNameKanji']);
            $applyEikenOrgEntity->setConfirmationDate(new \DateTime('now'));
        } else {
            if (!empty($applyEikenOrgEntity->getStatus()) && $applyEikenOrgEntity->getStatus() == 'DRAFT') {
                $applyEikenOrgEntity->setApplyStatus('変更通知');
            } else {
                $applyEikenOrgEntity->setApplyStatus('初回通知');
            }
            if($applyEikenOrgEntity->getStatus() != 'SUBMITTED'){
                $applyEikenOrgEntity->setStatus('DRAFT');
            }
        }
        // Store manager name if has standard hall data
        if ($managerName != '')
            $applyEikenOrgEntity->setManagerName($managerName);
        $em->persist($applyEikenOrgEntity);
        $em->flush();

        // Change session ApplyEikenOrg status
        $currentStatus = PrivateSession::getData('applyEikenStatus');
        $currentStatus['hasApplyEikenOrg'] = true;
        PrivateSession::setData('applyEikenStatus', $currentStatus);

        // F1GJIEM-2202: Send mail in first time send successfully
        if (!empty($callApiStatus1->kekka) && !empty($callApiStatus2->kekka) && $callApiStatus1->kekka == '10' && $callApiStatus2->kekka == '10')
        {
            $isSentMail = $this->sendMail();
        }

        // add mail error management
        return array(
            'StandardHallError'      => (!empty($callApiStatus1->kekka) && $callApiStatus1->kekka == '10') ? 0 : 1,
            'MainHallError'          => (!empty($callApiStatus2->kekka) && $callApiStatus2->kekka == '10') ? 0 : 1,
            'PupilListMainHallError' => (!empty($callApiStatus3->kekka) && $callApiStatus3->kekka == '10') ? 0 : 1,
            'SendMailError'          => (isset($isSentMail) && !$isSentMail) ? 1 : 0
        );
    }

    private function sendMail()
    {
        $eikenOrg = $this->getApplyEikenOrg();
        $globalConfig = $this->getServiceLocator()->get('config');
        $source = isset($globalConfig['emailSender']) ? $globalConfig['emailSender'] : 'dantai@mail.eiken.or.jp';
        if (!filter_var($eikenOrg['mailAddress'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $to = array($eikenOrg['mailAddress']);
        $data = $this->prepareDataForSendingMail($eikenOrg);
//        old default type template mail
        $semi = isset($data['semi']) ? $data['semi'] : '';
        $beneficiary = isset($data['beneficiary']) ? $data['beneficiary'] : '';
        $paymentType = isset($data['paymentType']) ? $data['paymentType'] : '';     
        $publicFunding = isset($data['publicFunding']) ? $data['publicFunding'] : '';     
        $type = 6;
        if(empty($semi)){
            if($paymentType === EikenConst::PAYMENT_TYPE_IS_COLLECTTIVE){ 
                $type =  EikenConst::NON_SEMI_COLLECTTIVE;
            }else{ 
                $type = EikenConst::NON_SEMI_INDIVIDUAL; 
            }
        }else{
            if($beneficiary === EikenConst::BENEFICIARY_IS_DANTAI){
                if($paymentType === EikenConst::PAYMENT_TYPE_IS_COLLECTTIVE){ 
                    $type = EikenConst::SEMI_DANTAI_COLLECTTIVE;
                }else{ 
                    $type = EikenConst::SEMI_DANTAI_INDIVIDUAL;
                }
            }else if($beneficiary === EikenConst::BENEFICIARY_IS_STUDENT){
                if($paymentType === EikenConst::PAYMENT_TYPE_IS_COLLECTTIVE){ 
                    $type = EikenConst::SEMI_STUDENT_COLLECTTIVE;
                }else{ 
                    $type = EikenConst:: SEMI_STUDENT_INDIVIDUAL;
                }
            }
        }
        if($publicFunding == 1){
            $type = EikenConst:: PUBLIC_TEMPLATE_MAIL;
        }
        
        try {
            \Dantai\Aws\AwsSesClient::getInstance()->deliver($source, $to, $type, $data);
        }
        catch (SesException $e) {
            return false;
        }

        return true;
    }

    private function prepareDataForSendingMail($eikenOrg)
    {
        $date00 = '';
        if (!empty($eikenOrg['typeExamDate'])) {
            //trong JIEM-Portal 1: friday, 2: saturday, 3: sunday, 4: the both friday & saturday
            if ($eikenOrg['typeExamDate'] == 3) {
                $date00 = '日曜日';
            } elseif ($eikenOrg['typeExamDate'] == 2) {
                $date00 = '土曜日';
            } elseif ($eikenOrg['typeExamDate'] == 1) {
                $date00 = '金曜日';
            } else
                $date00 = '金・土の両日にわたり実施';
        }
        // Get Org info
        $org = $this->getOrganizationById ($this->getOrganizationId());
        // Get from API
        $apiOrgInfo = $this->getOrganizationInfoByApi();
        $orgInfo = array(
            'orgNo' => $org->getOrganizationNo(), // Org No
            'orgName' => $org->getOrgNameKanji(), // Org Name
            'orgRepresentatives' => $eikenOrg['firtNameKanji'] . $eikenOrg['lastNameKanji'], // Org officer name
            'orgEmail' => $eikenOrg['mailAddress'], // Org email
            'orgPostalCode' => $apiOrgInfo['orgPostCode'], // Org post code
            'orgAddr1' => $apiOrgInfo['orgAddress1'], // Org addr1
            'orgAdd2' => $apiOrgInfo['orgAddress2'],
            'orgPhoneNo' => $apiOrgInfo['orgPhoneNo'],
            'orgExamDate' => $date00, // Exam date
            'cdSet' => $eikenOrg['cd'],
            'locationType' => $eikenOrg['locationType'],
        );
        if ($eikenOrg['locationType'] == 1)
        {
            $orgInfo['locationType1'] = $eikenOrg['locationType1'];
            if ($eikenOrg['locationType1'] == 2)
            {
                $orgInfo['eikenOrgNo1'] = $eikenOrg['eikenOrgNo1'];
                $orgInfo['eikenOrgNo123'] = $eikenOrg['eikenOrgNo123'];
            }
            else
                $orgInfo['eikenOrgNo2'] = $eikenOrg['eikenOrgNo2'];
        }
        // get detail fee
        $detailEikenOrgDetails = $this->getApplyEikenOrgDetails(- 1);
        $detailFee = $this->getDetailFee($detailEikenOrgDetails);

        if(!empty($eikenOrg['locationType1']) && $eikenOrg['locationType1'] == 2){
            $detailFee['TotlalFeeMa'] = 0;
        }

        $totalLdd = !empty($eikenOrg['cd'])? $detailFee['TotlalFeeMa']:0;
        $totalLdd += $detailFee['TotlalFeeSt'];

        // Get detail price
        $detailPrice = $this->detailPriceForConfirmation();

        $hasMainHall = false;
        if (!empty($detailPrice['mainHall']))
        {
            foreach($detailPrice['mainHall'] as $detail)
            {
                if ($detail['total'] > 0)
                {
                    $hasMainHall = true;
                    break;
                }
            }
        }
        if (!$hasMainHall && !empty($detailPrice['mainHall']))
            unset($detailPrice['mainHall']);
        $hasStandardHall = false;
        if (!empty($detailPrice['standardHall']))
        {
            foreach($detailPrice['standardHall'] as $detail)
            {
                if ($detail['total'] > 0)
                {
                    $hasStandardHall = true;
                    break;
                }
            }
        }
        if (!$hasStandardHall && !empty($detailPrice['standardHall']))
            unset($detailPrice['standardHall']);

        if ($hasStandardHall)
            $orgInfo['hallType'] = '準会場';
        else
            $orgInfo['hallType'] = '本会場';
        
        
        $semi = '';
        $beneficiary = '';
        $paymentType = '';
        $em = $this->getEntityManager();
            
        /* @var $dantaiService \Application\Service\DantaiService */
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');

        /* @var $objInvi \Application\Entity\InvitationSetting */
        $objInvi = $em->getRepository('Application\Entity\InvitationSetting')->findOneBy(array(
            'organizationId'=>$this->getOrganizationId(),
            'eikenScheduleId'=>$this->getEikenScheduleId()
            ));
        $semi = $dantaiService->getSemiMainVenueOrigin($this->getOrganizationId(), $this->getEikenScheduleId());
        if($objInvi){
            $beneficiary = $objInvi->getBeneficiary();
            $paymentType = $objInvi->getPaymentType();
            if($semi === 1 && empty($beneficiary) ){
                $beneficiary = InvitationConst::BENEFICIARY_IS_DANTAI;
            }
        }else{
            $paymentType = EikenConst::PAYMENT_TYPE_IS_COLLECTTIVE;
            if($semi === 1){
                $beneficiary = InvitationConst::BENEFICIARY_IS_DANTAI;
                $paymentType = EikenConst::PAYMENT_TYPE_IS_COLLECTTIVE;
            }
        }
        $publicFunding = $this->isSpecialOrg($this->getOrganizationId());
        

        return array(
            'orgInfo' => $orgInfo,
            'detailPrice' => $detailPrice,
            'detailFee' => array(
                'TotlalFeeSt' => $detailFee['TotlalFeeSt'],
                'TotlalFeeMa' => $detailFee['TotlalFeeMa'],
                'TotalPrice' => $detailPrice['totalPrice'] - $totalLdd
            ),
            'eikenOrgDetail' => $eikenOrg,
            'semi' => $semi,
            'beneficiary' => $beneficiary,
            'paymentType' => $paymentType,
            'publicFunding' => $publicFunding
        );
    }
    /**
     * update current pupil for each level to track change in next submit
     */
    protected function updateCurrentNoExpectation($theData = array()) {
        $em = $this->getEntityManager();
        $ApplyEikenOrgDetail = $em->getRepository('Application\Entity\ApplyEikenOrgDetails')->find($theData['id']);

        $ApplyEikenOrgDetail->setOldLev2($theData['lev2']);
        $ApplyEikenOrgDetail->setOldPreLev2($theData['preLev2']);
        $ApplyEikenOrgDetail->setOldLev3($theData['lev3']);
        $ApplyEikenOrgDetail->setOldLev4($theData['lev4']);
        $ApplyEikenOrgDetail->setOldLev5($theData['lev5']);
        $em->persist($ApplyEikenOrgDetail);
        $em->flush();
    }

    /**
     * @author LangDD
     * Get organization info for policy form
     */
    public function getOrganizationInfoByApi() {
        // Get config
        $config = $this->getServiceLocator()->get('Config')['eiken_config']['api'];
        // Prepare data to send to Ukesuke API
        $em = $this->getEntityManager();
        $currentOrg = $em->getRepository('Application\Entity\Organization')->find($this->getOrganizationId());
        $params = array(
            'dantaino' => $currentOrg->getOrganizationNo()
        );
        try {
            $result = \Dantai\Api\UkestukeClient::getInstance()->callEir2a01($config, $params);
        } catch (Exception $e) {
            // Caught exception regarding to:
            // Connection, Argument, RuntimeException
            // @todo Log error here
        }
        if ($result && $result->kekka == '10') {
            // if has district code
            $district = '';
            if ($result->jukenchino) {
                $district = $em->getRepository('Application\Entity\District')->findOneBy(array(
                    'isDelete' => 0,
                    'code' => trim($result->jukenchino)
                ));
            }
            return array(
                'firtNameKanji' => $result->sekininsha_sei,
                'lastNameKanji' => $result->sekininsha_mei,
                'mailAddress' => $result->email,
                'cityId' => !empty($district) ? $district->getCityId() : '',
                'districtId' => !empty($district) ? $district->getId() : '',
                'orgPostCode' => $result->zipcode,
                'orgAddress1' => $result->addr1,
                'orgAddress2' => $result->addr2,
                'orgPhoneNo' => $result->tel
            );
        }
        return array();
    }

    /**
     * @return array
     * @author LangDD
     * Send policy confirmation to Ukesuke
     */
    public function sendPolicyInfoToApi($params) {
        // Get config
        $config = $this->getServiceLocator()->get('Config')['eiken_config']['api'];
        // Prepare data to send to Ukesuke API
        $em = $this->getEntityManager();
        // check district id
        $district = $em->getRepository('Application\Entity\District')->find($params->fromPost('districtId', 0));
        if (empty($district))
            return false;
        $currentOrg = $em->getRepository('Application\Entity\Organization')->find($this->getOrganizationId());
        $params = array(
            'dantaino' => $currentOrg->getOrganizationNo(),
            'jukenchicd' => $district->getCode(),
            'sekininsha_sei' => $params->fromPost('txtFirstName'),
            'sekininsha_mei' => $params->fromPost('txtLastName'),
            'email' => $params->fromPost('txtEmailAddress')
        );
        try {
            $result = \Dantai\Api\UkestukeClient::getInstance()->callEir2a05($config, $params);
        } catch (\Dantai\Api\Exception\RuntimeException $e) {
            return false;
        } catch (\Dantai\Api\Exception\InvalidArgumentException $e) {
            return false;
        }
        if ($result && $result->kekka == '10') {
            return true;
        }
        return false;
    }

    /**
     * @author LangDD
     * Get and send list applied pupil list in main hall to Ukesuke
     */
    protected function sendAppliedPupilInMainHall($pupilList, $mappedData)
    {
        if (!empty($pupilList)) {
            // Get config
            $config = $this->getServiceLocator()->get('Config')['eiken_config']['api'];
            // Prepare data to send to Ukesuke API
            $pupilUkesukeData['eikenArray'] = $mappedData;

            try {
                $result = \Dantai\Api\UkestukeClient::getInstance()->callEir2a03($config, $pupilUkesukeData);
            }

            catch (Exception $e) {
                // Caught exception regarding to:
                // Connection, Argument, RuntimeException
                // @todo Log error here
            }
            // If successfully update: isSubmit and isCancel
            if (!empty($result->kekka) && $result->kekka == '10') {
                $this->updateSubmitedPupilsStatus($pupilList);
            }

            return $result;
        }

        return (object)array('kekka' => '10');
    }

    protected function prepareDataPupilList($pupilList) {
        $returnData = array(
            'apiMapped' => array(),
            'countByKyu' => array(
                'lev1' => 0,
                'preLev1' => 0,
                'lev2' => 0,
                'preLev2' => 0,
                'lev3' => 0,
                'lev4' => 0,
                'lev5' => 0
            )
        );
        foreach ($pupilList as $pupil) {
            if ($pupil->getFeeFirstTime() == 1){
                $jukenchicd = !empty($pupil->getAreaPersonal3()) ? $pupil->getAreaPersonal3()->getCode() : '';
            }
            else{
                $jukenchicd = !empty($pupil->getAreaPersonal2()) ? $pupil->getAreaPersonal2()->getCode() : '';
            }


            $year = '';
            $kai = '';
            if (!empty($pupil->getFirstPassedTime())) {
                $firstPassedTimeFree = explode('|', $pupil->getFirstPassedTime());
                if (isset($firstPassedTimeFree[0]) && isset($firstPassedTimeFree[1])) {
                    $year = $firstPassedTimeFree[0];
                    $kai = $firstPassedTimeFree[1];
                }
            }
            $pupilData = array(
                "nendo" => date('Y'),
                "kai" => $pupil->getEikenSchedule() ? $pupil->getEikenSchedule()->getKai() : '', // Kai
                "eikenid" => $pupil->getApplyEikenPersonalInfo()->getEikenId(), // EikenId
                "kyucd" => $pupil->getEikenLevel()->getId(), // EikenLevel name
                "jukenchikbn" => "3", // Is main Hall
                "ichimenflg" => $pupil->getFeeFirstTime() == 1 ? "1" : null, // Free at first time
                "ichimennendo" => $pupil->getFeeFirstTime() == 1 ? $year : '', // Year of free at first time
                "ichimenkai" => $pupil->getFeeFirstTime() == 1 ? $kai : '', // Free Kai number
                "ichimenjukenchi" => $pupil->getFeeFirstTime() == 1 ? $pupil->getAreaNumber1() : "", // Area number 1
                "ichimenkojinno" => $pupil->getFeeFirstTime() == 1 ? $pupil->getAreaPersonal1() : "", // Area personal 1
                "dantaino" => $this->getOrganizationNumber(), // Organization No
                "uketsukedt" => date('Y/m/d'),
                "loppitelno" => null,// update function for #GNCCNCJDM-233
                "kessai" => null,// update function for #GNCCNCJDM-233
                "cvscd" => "", // @todo get payment status
                "siteorderno" => "", // @todo get payment status
                "econorderno" => "", // @todo get payment status
                "shiharaidt" => "", // @todo get payment date
                "shiharaikigen" => "", // @todo Payment time limit
                "denpyono" => "", // @todo wait to confirm
                "uketsukeno" => null, // @todo wait to confirm , Uketuke No in API to send student list when applying for Exam recently is 2000000 It has to be updated as NULL. F1GNCJDR4-676
                "torihikino" => "", // @todo Dealings number wait to confirm
                "carrier" => "", // @todo Career wait to confirm
                "gaibulogin" => "0", // External login division
                "shiharaijokyo" => null, // update function for #GNCCNCJDM-233
                "moshikomifunc" => "0", // Former ..application.. function
                "jukenchicd" => empty($jukenchicd) ? null : $jukenchicd,
                "createdt" => $pupil->getInsertAt()->format('Y/m/d'),
                "updatedt" => $pupil->getUpdateAt()->format('Y/m/d'),
                "cancelflg" => $pupil->getIsDelete() ? '1' : null,
            );
            // In case modify EikenId must send to update to Ukesuke
            if ($pupil->getOldEikenId() != '')
            {
                $originalData = $pupilData;
                $originalData['eikenid'] = $pupil->getOldEikenId();
                $originalData['cancelflg'] = '1';
                // If change EikenId and Delete ==> must send to cancel original EikenId
                if ($pupil->getIsDelete() == 1)
                {
                    $returnData['apiMapped'][] = $originalData;
                }
                // Else send original EikenId to cancel and new EikenId to register
                else
                {
                    $returnData['apiMapped'][] = $originalData;
                    $returnData['apiMapped'][] = $pupilData;
                }
            }
            else
            {
                $returnData['apiMapped'][] = $pupilData;
            }

            switch ($pupil->getEikenLevel()->getId()) {
                case 1:
                    if ($pupil->getIsDelete() == 1)
                        $returnData['countByKyu']['lev1'] -= 1;
                    else
                        $returnData['countByKyu']['lev1'] += 1;
                    if ($pupil->getOldEikenId() != '' && $pupil->getIsDelete() != 1)
                        $returnData['countByKyu']['lev1'] -= 1;
                    break;
                case 2:
                    if ($pupil->getIsDelete() == 1)
                        $returnData['countByKyu']['preLev1'] -= 1;
                    else
                        $returnData['countByKyu']['preLev1'] += 1;
                    if ($pupil->getOldEikenId() != '' && $pupil->getIsDelete() != 1)
                        $returnData['countByKyu']['preLev1'] -= 1;
                    break;
                case 3:
                    if ($pupil->getIsDelete() == 1)
                        $returnData['countByKyu']['lev2'] -= 1;
                    else
                        $returnData['countByKyu']['lev2'] += 1;
                    if ($pupil->getOldEikenId() != '' && $pupil->getIsDelete() != 1)
                        $returnData['countByKyu']['lev2'] -= 1;
                    break;
                case 4:
                    if ($pupil->getIsDelete() == 1)
                        $returnData['countByKyu']['preLev2'] -= 1;
                    else
                        $returnData['countByKyu']['preLev2'] += 1;
                    if ($pupil->getOldEikenId() != '' && $pupil->getIsDelete() != 1)
                        $returnData['countByKyu']['preLev2'] -= 1;
                    break;
                case 5:
                    if ($pupil->getIsDelete() == 1)
                        $returnData['countByKyu']['lev3'] -= 1;
                    else
                        $returnData['countByKyu']['lev3'] += 1;
                    if ($pupil->getOldEikenId() != '' && $pupil->getIsDelete() != 1)
                        $returnData['countByKyu']['lev3'] -= 1;
                    break;
                case 6:
                    if ($pupil->getIsDelete() == 1)
                        $returnData['countByKyu']['lev4'] -= 1;
                    else
                        $returnData['countByKyu']['lev4'] += 1;
                    if ($pupil->getOldEikenId() != '' && $pupil->getIsDelete() != 1)
                        $returnData['countByKyu']['lev4'] -= 1;
                    break;
                case 7:
                    if ($pupil->getIsDelete() == 1)
                        $returnData['countByKyu']['lev5'] -= 1;
                    else
                        $returnData['countByKyu']['lev5'] += 1;
                    if ($pupil->getOldEikenId() != '' && $pupil->getIsDelete() != 1)
                        $returnData['countByKyu']['lev5'] -= 1;
                    break;
                default:
                    break;
            }
        }
        return $returnData;
    }

    /**
     * @param array $applyEikenLevelList
     * @author LangDD
     * @uses Update status for pupils after submit Eiken successfully
     */
    private function updateSubmitedPupilsStatus($applyEikenLevelList)
    {
        $em = $this->getEntityManager();
        $listApplyEikenLevelId = array();
        foreach ($applyEikenLevelList as $applyEikenLevel) {
            array_push($listApplyEikenLevelId, $applyEikenLevel->getId());
        }
        $em->getRepository('Application\Entity\ApplyEikenLevel')->changeStatusAfterSubmit($listApplyEikenLevelId);
    }

    public function standardConfirmation() {
        // get organization information
        $em = $this->getEntityManager();
        $currentOrg = $em->getRepository('Application\Entity\Organization')->find($this->getOrganizationId());

        // Get apply eiken Org info
        $eikenOrg = $this->getApplyEikenOrg(false);
        $date00 = '';
        if (!empty($eikenOrg['typeExamDate'])) {
            //trong JIEM-Portal 1: friday, 2: saturday, 3: sunday, 4: the both friday & saturday
            if ($eikenOrg['typeExamDate'] == 3) {
                $date00 = '日曜日';
            } elseif ($eikenOrg['typeExamDate'] == 2) {
                $date00 = '土曜日';
            } elseif ($eikenOrg['typeExamDate'] == 1) {
                $date00 = '金曜日';
            } else
                $date00 = '金・土の両日にわたり実施';
        }
        return array(
            'organizationNo' => $currentOrg->getOrganizationNo(),
            'organizationNameKanji' => $currentOrg->getOrgNameKanji(),
            'nameKanji' => $eikenOrg['firtNameKanji'] . $eikenOrg['lastNameKanji'],
            'location' => $currentOrg->getAddress1(),
            'managerName' => isset($eikenOrg['managerName']) ? $eikenOrg['managerName'] : '',
            'date00' => $date00
        );
    }

    /**
     * validate standard Hall
     */
    protected function validateStandardHall($theData = array()) {
        return $this->checkTotalPupil($theData);
    }

    protected function checkTotalPupil($theData = array()) {
        if ($theData['lev2'] - (!empty($theData['oldLev2']) ? (int) $theData['oldLev2'] : 0) != 0)
            return true;
        if ($theData['preLev2'] - (!empty($theData['oldPreLev2']) ? (int) $theData['oldPreLev2'] : 0) != 0)
            return true;
        if ($theData['lev3'] - (!empty($theData['oldLev3']) ? (int) $theData['oldLev3'] : 0))
            return true;
        if ($theData['lev4'] - (!empty($theData['oldLev4']) ? (int) $theData['oldLev4'] : 0) != 0)
            return true;
        if ($theData['lev5'] - (!empty($theData['oldLev5']) ? (int) $theData['oldLev5'] : 0) != 0)
            return true;
        return false;
    }

    /**
     * @param int $eikenId
     * @param string $eikenPass
     * @return \Zend\Json\mixed
     * @author LangDD
     * @uses Get fake data, must be overwritten when available
     */
    public function callAPIToUkestuke($ukestukeData = array())
    {
        // Get config
        $config = $this->getServiceLocator()->get('Config')['eiken_config']['api'];
        if (empty($ukestukeData)) {
            return false;
        }
        try {
            $result = \Dantai\Api\UkestukeClient::getInstance()->callEir2a02($config, $ukestukeData);
        }
        catch (Exception $e) {
            //System error
            return false;
        }

        return $result;
    }

    /**
     * Get detail apply eiken org detail for confirmation page
     */
    public function getApplyEikenOrgDetails() {
        return $this->getApplyEikenOrgDetail(-1);
    }

    /**
     * Function update price mainHall same as standardHall when semi is checked
     * @param $orgDetailData
     */
    public function convertPriceMainHall($orgDetailData)
    {
        $orgDetailData[1]['pricePreLev2'] = $orgDetailData[0]['pricePreLev2'];
        for($i = 2; $i <= 5; $i++){
            $orgDetailData[1]['priceLev'.$i] = $orgDetailData[0]['priceLev'.$i];
        }
        return $orgDetailData;
    }

    public function getUserId() {
        $currentUserInfor = $this->getCurrentUserInfor();
        return $currentUserInfor['id'];
    }

    /**
     * get org number from current logged user
     */
    public function getOrganizationNumber() {
        $currentUserInfor = $this->getCurrentUserInfor();
        $organization = $this->getOrganizationById($currentUserInfor['organizationId']);
        if (!empty($organization)) {
            return $organization->getOrganizationNo();
        }
        return $currentUserInfor['organizationNo'];
    }

    public function getOrganizationCode() {
        $organizatoin = $this->getOrganizationByNumber($this->getOrganizationNumber());
        if (!empty($organizatoin[0])) {
            return $organizatoin[0]['organizationCode'];
        }
        return 0;
    }

    /**
     * get current orgId from current logged user
     */
    public function getOrganizationId() {
        $currentUserInfor = $this->getCurrentUserInfor();
        return $currentUserInfor['organizationId'];
    }

    /**
     * Check hoi truong chinh/chuan
     * return boolean true/false
     */
    public function isTheMainHall() {
        $invitationSetting = $this->getInvitationSetting();
        return isset($invitationSetting['hallType']) ? $invitationSetting['hallType'] : false;
    }

    /**
     * check payment type
     *
     * @return 1: Organization, 0: Personal
     */
    public function isPaymentOrg() {
        //click link on menu to check apply eiken org or apply eiken personal
        $isPersonal = PrivateSession::getData('ApplyEikenOfPersonal');
        if($isPersonal == Null || $isPersonal == 0){
            return 1;
        }
        $invitationSetting = $this->getInvitationSetting();
        return isset($invitationSetting['paymentType']) ? $invitationSetting['paymentType'] : false;
    }

    /**
     * get current valid Eiken schedule Id
     */
    public function getEikenScheduleId() {
        if (self::$eikenScheduleId) {
            return self::$eikenScheduleId;
        }
        $eikenSchedule = $this->getEikenSchedule();
        if (!empty($eikenSchedule)) {
            return $eikenSchedule['id'];
        }
        return 0;
    }

    private $schedule;

    public function checkEikenSchedule($schedule = NULL) {
        if (is_null($schedule)) { $schedule = $this->getEikenSchedule(true); }
        $this->schedule = $schedule;
    }

    /**
     * check is valid time to apply eiken
     */

    public function isValidTime($isCreate = null) {
        if(!$this->schedule){
            $this->checkEikenSchedule();
        }
        $eikenSchedule = $this->schedule;
        $currentDate = date(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT);
        if (!empty($eikenSchedule) && ($eikenSchedule['deadlineTo']->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) >= $currentDate || PublicSession::isSysAdminOrServiceManagerOrOrgSupervisor())) {
            return true;
        }

        return false;
    }

    /**
     * get Kai number (so lan thi trong nam co valid time)
     */
    public function getKaiNumber($eikenScheduleId = 0) {

        if (!self::$eikenScheduleId) {
            self::$eikenScheduleId = $eikenScheduleId;
        }
        $eikenSchedule = $this->getEikenSchedule();
        if (!empty($eikenSchedule)) {
            return $eikenSchedule['kai'];
        }
        return '';
    }

    /**
     * get year of eikenScheulde
     */
    public function getYearOfEikenSchedule($eikenScheduleId = 0) {

        if (!self::$eikenScheduleId) {
            self::$eikenScheduleId = $eikenScheduleId;
        }
        $eikenSchedule = $this->getEikenSchedule();
        if (!empty($eikenSchedule)) {
            return $eikenSchedule['year'];
        }
        return '';
    }

    /**
     * Get current user infor (org_id, kai, .
     *
     * ...)
     *
     * @return array
     */
    protected function getCurrentUserInfor() {
        if (!self::$currentUserInfo) {
            $privateUser = PrivateSession::getData('userIdentity');
            self::$currentUserInfo = array(
                'id' => $privateUser['id'],
                'organizationNo' => $privateUser['organizationNo'],
                'organizationId' => $privateUser['organizationId'],
                'emailAddress' => $privateUser['emailAddress'],
                'firstNameKanji' => $privateUser['firstName'],
                'lastNameKanji' => $privateUser['lastName']
            );
        }
        return self::$currentUserInfo;
    }

    /**
     * get applyEiken Status if exist
     */
    public function getApplyEikenStatus() {
        $applyEikenOrg = $this->getApplyEikenOrg();
        if (!empty($applyEikenOrg['status'])) {
            return $applyEikenOrg['status'];
        }
        return '';
    }

    public function getIsSentStandardHall() {
        $applyEikenOrg = $this->getApplyEikenOrg();
        if (!empty($applyEikenOrg['isSentStandardHall'])) {
            return $applyEikenOrg['isSentStandardHall'];
        }
        return 0;
    }

    public function getIsSentMainHall() {
        $applyEikenOrg = $this->getApplyEikenOrg();
        if (!empty($applyEikenOrg['isSentMainHall'])) {
            return $applyEikenOrg['isSentMainHall'];
        }
        return 0;
    }

    /**
     * Get Invitation setting
     */
    public function getInvitationSetting() {
        if (!self::$invitationSetting) {
            $em = $this->getEntityManager();
            self::$invitationSetting = $em->getRepository('Application\Entity\InvitationSetting')->getInvitationSetting($this->getOrganizationId(), $this->getEikenScheduleId());
        }
        if (empty(self::$invitationSetting)) {
            // throw exception to prevent issue
            self::$invitationSetting = array();
        }
        return self::$invitationSetting;
    }
    
    public function isInvitationSettingValue()
    {
        $isInvitationSetting = $this->getInvitationSetting();
        if($isInvitationSetting != null){
            return EikenConst::EXIST;
        }
        return EikenConst::NOT_EXIST;
    }

    public function validInvitationSetting() {
        $invitationSetting = $this->getInvitationSetting();
        if (!empty($invitationSetting)) {
            return true;
        }
        return false;
    }

    /**
     * get Schedule Setting
     */
    protected function getEikenSchedule($checkTimeValid = false) {
        //get Eiken schedule
        if (self::$eikenScheduleId) {
            $em = $this->getEntityManager();
            if (!$checkTimeValid) {
                return $em->getRepository('Application\Entity\EikenSchedule')->getEikenScheduleById(self::$eikenScheduleId);
            } else {
                return $em->getRepository('Application\Entity\EikenSchedule')->getEikenScheduleById(self::$eikenScheduleId, date('Y'), date('Y-m-d H:i:s'));
            }
        }
        $em = $this->getEntityManager();
        $eiKenSchedule = $em->getRepository('Application\Entity\EikenSchedule')->getCurrentEikenSchedule();
        if (!empty($eiKenSchedule)) {
            self::$eikenSchedule = $eiKenSchedule;
        }
        return self::$eikenSchedule;
    }

    public function getApplyEikenOrgDetail($hallType = 0)
    {
        return $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenOrg')->getEikenOrgDetailByParams($this->getOrganizationId(), $this->getEikenScheduleId(), $hallType);
    }

    /**
     * Get Eiken Apply Pupil
     */
    public function getApplyEikenPersonal() {
        $em = $this->getEntityManager();
        return $em->getRepository('Application\Entity\ApplyEikenLevel')->getApplyEikenPersonal($this->getOrganizationId(), $this->getEikenScheduleId());
    }

    public function getTotalKyuPaymentInfo($hallType = null) {
        return $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenLevel')->getTotalKyuPaymentInfo($this->getOrganizationId(), $this->getEikenScheduleId(), $hallType);
    }

    /**
     * get Current Apply Eiken Org by orgId & eikenScheduleId
     */
    public function getApplyEikenOrg($isCheckStatus = true) {
        return $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenOrg')->getEikenOrgByParams($this->getOrganizationId(), $this->getEikenScheduleId(), $isCheckStatus);
    }

    public function getEikenLevel() {
        $em = $this->getEntityManager();
        return $em->getRepository('Application\Entity\EikenLevel')->getPriceForAllLevel();
    }

    public function getOrganizationByNumber($orgNumber = '') {
        if (!self::$organization) {
            $em = $this->getEntityManager();
            self::$organization = $em->getRepository('Application\Entity\Organization')->getOrganizationByNo($orgNumber);
        }
        return self::$organization;
    }

    public function getOrganizationById($orgId = 0) {
        $em = $this->getEntityManager();
        return $em->getRepository('Application\Entity\Organization')->find($orgId);
    }

    /**
     * @return array
     * @author LangDD
     */
    public function getCityList() {
        $em = $this->getEntityManager();
        return $cities = \Eiken\Helper\EikenCommon::generateSelectOptions($em->getRepository('Application\Entity\City')->getApplyEikCitiesList(true,false,false,true), 'getCityName');
    }

    /**
     * @param int $cityId
     * @return array
     * @author LangDD
     */
    public function getExamLocationList($cityId, $hallType) {
        if (!$cityId)
            return array();
        $em = $this->getEntityManager();
        return \Eiken\Helper\EikenCommon::generateSelectOptions($em->getRepository('Application\Entity\District')->findBy(array(
            'isDelete' => 0,
            'cityId' => (int) $cityId
        ), array(
            'code' => 'ASC'
        )), 'getName');
    }

    /**
     * @param int $eikenScheduleId
     * @return \Application\Entity\InvitationSetting
     * @author MinhTN6
     */
    public function getInviSettingByEikenScheduleIdAndOrg($eikenScheduleId, $organizationId) {
        $em = $this->getEntityManager();
        return $em->getRepository('Application\Entity\InvitationSetting')->findOneBy(array(
            'eikenScheduleId' => $eikenScheduleId
        , 'organizationId' => $organizationId
        ));
    }


    public function genCurrentYearJapan() {
        $currentDate = '';
        $year = date('Y');
        if ($year >= 1911 && $year <= 1925) {
            $no = $year - 1911;
            $currentDate .= '大正' . $no;
        }

        // tinh nam shouwa ex: value - 1925
        if ($year > 1925 && $year <= 1988) {
            $no = $year - 1925;
            $currentDate .= '昭和' . $no;
        }
        // tinh nam heisei
        if ($year >= 1989) {
            $no = $year - 1988;
            $currentDate .= '平成' . $no;
        }
        $currentDate .= '年'. date('n') .'月'. date('j') .'日';
        return $currentDate;
    }
    
    public function getDefinition($detailPrice)
    {
        $mainHall = 0;
        if (isset($detailPrice)) {
            foreach ($detailPrice as $eikenLevelId => $val) {
                if ($eikenLevelId > 1 && (int)$val['total'] > 0) {
                    $mainHall = 1;
                    break;
                }
            }
        }
        $user = $this->getCurrentUserInfor();
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        if ($mainHall && $dantaiService->getDefinitionSpecial($user['organizationNo']) > 0) {
            $this->definitionSpecial = 1;
        }

        return $this->definitionSpecial;
    }

    public function setDefinition($definitionSpecial)
    {
        $this->definitionSpecial = $definitionSpecial;
    }
    /**
     *
     * @return array|object
     */
    protected function getEntityManager() {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
    
    public function checkExistApplyEiken($orgId , $isUpdatePolicy = false)
    {
        $em = $this->getEntityManager();
        $param = array(
            'organizationId' => $orgId,
            'eikenScheduleId' => $this->getEikenScheduleId(),
            'status' => 'SUBMITTED'
        );
        if($isUpdatePolicy){
            $param = array(
                'organizationId' => $orgId,
                'eikenScheduleId' => $this->getEikenScheduleId()
            );
        }
        $applyEiken = $em->getRepository('Application\Entity\ApplyEikenOrg')->findOneBy($param);
        if(!$applyEiken){
            return EikenConst::NOT_EXIST;
        }
        return EikenConst::EXIST;
    }
    
    public function checkExistSubmittedApplyEiken($orgId)
    {
        $em = $this->getEntityManager();
        $applyEiken = $em->getRepository('Application\Entity\ApplyEikenOrg')->findOneBy(array(
            'organizationId' => $orgId,
            'eikenScheduleId' => $this->getEikenScheduleId(),
            'status' => 'SUBMITTED'
        ));
        if(!$applyEiken){
            return EikenConst::NOT_EXIST;
        }
        return EikenConst::EXIST;
    }
    
    private $orgRepo;
    public function setOrgRepo($orgRep = null){
        $this->orgRepo = $orgRep ? $orgRep : $this->getEntityManager()->getRepository('Application\Entity\Organization');
    }
    
    private $eikenScheduleRepo;
    public function setEikenScheduleRepo($eikenScheduleRep = null)
    {
        $this->eikenScheduleRepository = $eikenScheduleRep ? $eikenScheduleRep : $this->getEntityManager()->getRepository('Application\Entity\EikenSchedule');
    }
    
    public function saveLogApplyEiken($logMainHall, $logStandardHall, $logData, $em = false)
    {
        $em = $em ? $em : $this->getEntityManager();
        
        $config = $this->serviceLocator->get('config');
        $listRefund = $config['refundStatusOption'];
        
        if($logData['action'] == 'create'){
            $refundStatus = $listRefund[$logData['params']['refundStatus']];
            list($mainDetail, $standardDetail) = $this->dataWhenActionIsCreate($logMainHall, $logStandardHall); 
        }else{
            list($mainDetail, $standardDetail, $refundStatus) = $this->dataWhenActionIsUpdate($logMainHall, $logStandardHall, $logData['params'], $logData['oldStatusRefund']); 
        }

        $applyEiken = new \Application\Entity\ApplyEikenLog();
        if(!$this->orgRepo){
            $this->setOrgRepo();
        }
        $applyEiken->setOrganization($this->orgRepo->findOneBy(array('id' => $this->getOrganizationId())));
        $applyEiken->setOrganizationNo($this->orgRepo->findOneBy(array('id' => $this->getOrganizationId()))->getOrganizationNo());
        $applyEiken->setOrganizationName($this->orgRepo->findOneBy(array('id' => $this->getOrganizationId()))->getOrgNameKanji());
        if(!$this->eikenScheduleRepo){
            $this->setEikenScheduleRepo();
        }
        $applyEiken->setEikenSchedule($this->eikenScheduleRepository->findOneBy(array('id' => $this->getEikenScheduleId())));
        $applyEiken->setAction($logData['action'] == 'create' ? 'create' : 'update');
        $applyEiken->setUserId($logData['userId']);
        $applyEiken->setMainDetail(Json::encode($mainDetail));
        $applyEiken->setStandardDetail(Json::encode($standardDetail));
        $applyEiken->setRefundDetail($refundStatus);
        try{
            $em->persist($applyEiken);
            $em->flush();
            return EikenConst::SAVE_DATA_INTO_DATABASE_SUCCESS;
        }catch(\Exception $ex){
            return EikenConst::SAVE_DATA_INTO_DATABASE_FAIL;
        }
    }
    
    public function getPreviousLog()
    {
        $em = $this->getEntityManager();
        $previousLogs = $em->getRepository('Application\Entity\ApplyEikenLog')->getPreviousLogs($this->getOrganizationId(), $this->getEikenScheduleId());
        $dataLastestLog = array();
        if($previousLogs){
            $previewLog = reset($previousLogs);
            $dataLastestLog = array(
                'id' => $previewLog['id'],
                'insertAt' => $previewLog['insertAt'],
                'mainDetail' => $previewLog['mainDetail'],
                'standardDetail' => $previewLog['standardDetail'],
                'refundDetail' => $previewLog['refundDetail']
            );
        }
        return $dataLastestLog;
    }
    
    public function dataWhenActionIsCreate($logMainHall, $logStandardHall)
    {
        $standardDetail = array();
        $mainDetail = array(
            '1級' => isset($logMainHall['lev1']) ? $logMainHall['lev1'] : 0,
            '準1級' => isset($logMainHall['preLev1']) ? $logMainHall['preLev1'] : 0,
            '2級' => isset($logMainHall['lev2']) ? $logMainHall['lev2'] : 0,
            '準2級' => isset($logMainHall['preLev2']) ? $logMainHall['preLev2'] : 0,
            '3級' => isset($logMainHall['lev3']) ? $logMainHall['lev3'] : 0,
            '4級' => isset($logMainHall['lev4']) ? $logMainHall['lev4'] : 0,
            '5級' => isset($logMainHall['lev5']) ? $logMainHall['lev5'] : 0,
        );
        
        if(!empty($logStandardHall)){
            $standardDetail = array(
                '2級' => $logStandardHall['lev2'] ? $logStandardHall['lev2'] : 0,
                '準2級' => $logStandardHall['preLev2'] ? $logStandardHall['preLev2'] : 0,
                '3級' => $logStandardHall['lev3'] ? $logStandardHall['lev3'] : 0,
                '4級' => $logStandardHall['lev4'] ? $logStandardHall['lev4'] : 0,
                '5級' => $logStandardHall['lev5'] ? $logStandardHall['lev5'] : 0,
            );
        }
        
        return array($mainDetail, $standardDetail);
    }
    
    public function dataWhenActionIsUpdate($logMainHall, $logStandardHall, $data, $oldStatusRefund)
    {
        $mainDetail = array();
        if(!empty($logMainHall)){
            $mainDetail = array(
                '1級' => empty($logMainHall['lev1'] - $logMainHall['oldLev1']) ? '' : $logMainHall['oldLev1'] . '→' . $logMainHall['lev1'],
                '準1級' => empty($logMainHall['preLev1'] - $logMainHall['oldPreLev1']) ? '' : $logMainHall['oldPreLev1'] . '→' . $logMainHall['preLev1'],
                '2級' => empty($logMainHall['lev2'] - $logMainHall['oldLev2']) ? '' : $logMainHall['oldLev2'] . '→' . $logMainHall['lev2'],
                '準2級' => empty($logMainHall['preLev2'] - $logMainHall['oldPreLev2']) ? '' : $logMainHall['oldPreLev2'] . '→' . $logMainHall['preLev2'],
                '3級' => empty($logMainHall['lev3'] - $logMainHall['oldLev3']) ? '' : $logMainHall['oldLev3'] . '→' . $logMainHall['lev3'],
                '4級' => empty($logMainHall['lev4'] - $logMainHall['oldLev4']) ? '' : $logMainHall['oldLev4'] . '→' . $logMainHall['lev4'],
                '5級' => empty($logMainHall['lev5'] - $logMainHall['oldLev5']) ? '' : $logMainHall['oldLev5'] . '→' . $logMainHall['lev5']
            );
        }
        
        $standardDetail = array();
        if(!empty($logStandardHall)){
            $standardDetail = array(
                '2級' => empty($logStandardHall['lev2'] - $logStandardHall['oldLev2']) ? '' : $logStandardHall['oldLev2'] . '→' . $logStandardHall['lev2'],
                '準2級' => empty($logStandardHall['preLev2'] - $logStandardHall['oldPreLev2']) ? '' : $logStandardHall['oldPreLev2'] . '→' . $logStandardHall['preLev2'],
                '3級' => empty($logStandardHall['lev3'] - $logStandardHall['oldLev3']) ? '' : $logStandardHall['oldLev3'] . '→' . $logStandardHall['lev3'],
                '4級' => empty($logStandardHall['lev4'] - $logStandardHall['oldLev4']) ? '' : $logStandardHall['oldLev4'] . '→' . $logStandardHall['lev4'],
                '5級' => empty($logStandardHall['lev5'] - $logStandardHall['oldLev5']) ? '' : $logStandardHall['oldLev5'] . '→' . $logStandardHall['lev5']
            );
        }
        
        $statusRefund = '';
        
        $config = $this->serviceLocator->get('config');
        $listRefund = $config['refundStatusOption'];
        
        if($listRefund[$data['refundStatus']] === $listRefund[$oldStatusRefund]){
            $statusRefund = '';
        }else{
            $statusRefund = $listRefund[$oldStatusRefund] . '→' . $listRefund[$data['refundStatus']];
        }
        return array($mainDetail, $standardDetail, $statusRefund);
    }
    
    public function getPastRefundStatus()
    {
        $em = $this->getEntityManager();
        $applyEiken = $em->getRepository('Application\Entity\ApplyEikenOrg')->findOneBy(array(
            'organizationId' => $this->getOrganizationId(),
            'eikenScheduleId' => $this->getEikenScheduleId()
        ));
        $statusRefund = $applyEiken->getStatusRefund();
        return $statusRefund;
    }
    public function updateInformationPolicy($theParams) {
        $currentUserInfor = $this->getCurrentUserInfor();
        $orgId = $currentUserInfor['organizationId'];
        $em = $this->getEntityManager();
        if (!$this->getEikenScheduleId()) {
            return false;
        }
        $applyEikenOrg = $em->getRepository('Application\Entity\ApplyEikenOrg')->findOneBy(array(
            'organizationId' => $this->getOrganizationId(),
            'eikenScheduleId' => $this->getEikenScheduleId()
        ));
        if (!empty($theParams) && !empty($applyEikenOrg)) {
            if (!empty($theParams->firstName)) {
                $applyEikenOrg->setFirtNameKanji($theParams->firstName);
            }
            if (!empty($theParams->lastName)) {
                $applyEikenOrg->setLastNameKanji($theParams->lastName);
            }
            if (!empty($theParams->emailAddress)) {
                $applyEikenOrg->setMailAddress($theParams->emailAddress);
            }
            // LangDD - Implement update Apply Eiken
            if (!empty($theParams->confirmMailAddress)) {
                $applyEikenOrg->setConfirmEmail($theParams->confirmMailAddress);
            }
            if (!empty($theParams->cityId)) {
                $applyEikenOrg->setCity($em->getReference('Application\Entity\City', array(
                    'id' => $theParams->cityId
                )));
            }
            if (!empty($theParams->districtId)) {
                $applyEikenOrg->setDistrict($em->getReference('Application\Entity\District', array(
                    'id' => $theParams->districtId
                )));
            }
        }
        $applyEikenOrg->setOrganization($em->getReference('Application\Entity\Organization', array(
            'id' => $orgId
        )));
        
        $applyEikenOrg->setEikenSchedule($em->getReference('Application\Entity\EikenSchedule', array(
            'id' => $this->getEikenScheduleId()
        )));
        
        try {
            $em->persist($applyEikenOrg);
            $em->flush();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getSemiMainVenue($orgId, $eikenScheduleId = null){
        $eikenScheduleId = empty($eikenScheduleId) ? $this->getEikenScheduleId() : $eikenScheduleId;
        /** @var DantaiService $dantaiService */
        $dantaiService = $this->getServiceLocator()->get('Application\Service\DantaiServiceInterface');
        return $dantaiService->getSemiMainVenueOrigin($orgId, $eikenScheduleId);
    }
    
    public function getSemiMainVenueAndSettingCard($orgId){
        $em = $this->getEntityManager();

        $semiVenue = $this->getSemiMainVenue($orgId);
        $result = array(
            'semi'        => empty($semiVenue) ? 0 : $semiVenue,
            'beneficiary' => null,
            'paymentType' => null,
            'statusGen'   => '0',
        );

        $inv = $em->getRepository('Application\Entity\InvitationSetting')
            ->findOneBy(array(
                            'organizationId'  => $orgId,
                            'eikenScheduleId' => $this->getEikenScheduleId(),
                        ));
        if ($inv) {
            $result['beneficiary'] = $semiVenue == 1 ? $inv->getBeneficiary() : null;
            $result['paymentType'] = $inv->getPaymentType();
            $result['statusGen'] = intval($inv->getStatus());
        }

        if ($result['semi'] == 0 && ($result['paymentType'] === '' || $result['paymentType'] === null)) {
            $result['paymentType'] = EikenConst::PAYMENT_TYPE_IS_COLLECTTIVE;
        } else if ($result['semi'] == 1) {
            if ($result['paymentType'] === '' || $result['paymentType'] === null) {
                $result['paymentType'] = EikenConst::PAYMENT_TYPE_IS_COLLECTTIVE;
            }
            if ($result['beneficiary'] === '' || $result['beneficiary'] === null) {
                $result['beneficiary'] = EikenConst::BENEFICIARY_IS_DANTAI;
            }
        }

        return $result;
    }
    
    public function getDataSpecial($orgId,$scheduleId){
        
        $em = $this->getEntityManager();
        $allGradeDiscount = $this->getSpecialPriceAllGrade($orgId, $scheduleId);
        $isOrgDiscount = $allGradeDiscount ? 1 : 0;
        list($arrayHeader,$dataDiscountKyu) = $this->getArrayHeader($allGradeDiscount);
        
        /* @var $grade \Application\Entity\OrgSchoolYear */
        $grade = $em->getRepository('Application\Entity\OrgSchoolYear')
                    ->findBy(array(
                        'organizationId' => $orgId,
                        'isDelete' => 0
                        ), 
                        array('schoolYearId' => 'ASC')
                        );
        
        $gradeDiscount = array();
        /* @var $applEikenOrg \Application\Entity\ApplyEikenOrg */
        $applEikenOrg = $em->getRepository('Application\Entity\ApplyEikenOrg')
                        ->findOneBy(array(
                            'organizationId' => $orgId ,
                            'eikenScheduleId' => $scheduleId,
                            'isDelete' => 0
                ));
        
        if($applEikenOrg){
            /* @var $gradeDiscount \Application\Entity\ApplyEikenStudent */
            $gradeDiscount = $em->getRepository('Application\Entity\ApplyEikenStudent')
                    ->findBy(array(
                        'applyEikenOrgId' => $applEikenOrg->getId(),
                        'isDelete' => 0
                    ));
        }

        $standPupilOfKyu = array_fill_keys($arrayHeader,'');
        $standPupilOfKyuTotal = array_fill_keys($arrayHeader,'');

        $mappingKyu = $this->getServiceLocator()->get('Config')['MappingLevel'];
        $arrayRender = array();
        $flgGetPupil = 0;
        $flg = 0;
        $flgHadGradeDiscount = 0;
        if (!empty($grade) && !empty($arrayHeader)) {
            foreach ($grade as $row) {
                $listKyus = array();
                foreach ($arrayHeader as $value) {
                    if(empty($flgHadGradeDiscount)){
                        $flgHadGradeDiscount = isset($dataDiscountKyu[$row->getSchoolYearId() . '_' . $value]) ? 1 : 0;
                    }
                    $listKyus[$value] = array(
                        'kyuName' => $mappingKyu[$value],
                        'totalPupil' => 0,
                        'isDiscountKyu' => isset($dataDiscountKyu[$row->getSchoolYearId() . '_' . $value]) ? 1 : 0,
                        'orgSchoolYear' =>$row->getId()
                    );
                    if ($flg == 0) {
                        $standPupilOfKyu[$value] = 0;
                        $standPupilOfKyuTotal[$value] = 0;
                    }
                }
                $flg = 1;
                if ($gradeDiscount) {
                    foreach ($gradeDiscount as $numberPupil) {
                        if (isset($listKyus[$numberPupil->getEikenLevelId()]) && !empty($numberPupil->getEikenLevelId()) && $numberPupil->getOrgSchoolYearId() == $row->getId()) {
                            $listKyus[$numberPupil->getEikenLevelId()]['totalPupil'] = intval($numberPupil->getTotalStudent());
                        }
                        if ($flgGetPupil == 0) {
                            if (isset($standPupilOfKyu[$numberPupil->getEikenLevelId()])) {
                                
                                $standPupilOfKyuTotal[$numberPupil->getEikenLevelId()] = 
                                        intval($standPupilOfKyuTotal[$numberPupil->getEikenLevelId()]) 
                                        + intval($numberPupil->getTotalStudent());
                                
                                if ($numberPupil->getIsDiscount() == 1) {
                                    
                                    $standPupilOfKyu[$numberPupil->getEikenLevelId()] = 
                                            intval($standPupilOfKyu[$numberPupil->getEikenLevelId()]) 
                                            + intval($numberPupil->getTotalStudent());
                                    
                                }
                            }
                            if (empty($standPupilOfKyu[$numberPupil->getEikenLevelId()])) {
                                $standPupilOfKyu[$numberPupil->getEikenLevelId()] = 0;
                            }
                            if (empty($standPupilOfKyuTotal[$numberPupil->getEikenLevelId()])) {
                                $standPupilOfKyuTotal[$numberPupil->getEikenLevelId()] = 0;
                            }
                        }
                    }
                    $flgGetPupil = 1;
                }
                
                $arrayRender[$row->getSchoolYearId()] = array(
                    'gradeName' => $row->getDisplayName(),
                    'kyu' => $listKyus
                );
            }
        }
        //  $arrayRender : data of popup
        //  $arrayHeader :data of kyu disable 
        //  $standPupilOfKyu : data pupil stand of kyu
        return array($arrayRender,$arrayHeader,$standPupilOfKyu,$isOrgDiscount,$standPupilOfKyuTotal,$flgHadGradeDiscount);
    }
    
    public function getSpecialPriceAllGrade($orgId,$scheduleId){
        $em = $this->getEntityManager();
        $year = '';
        $kai = '';
        $specialPrice = array();
        
        /* @var $objEikenSchedule \Application\Entity\EikenSchedule */
        $objEikenSchedule = $em->getRepository('Application\Entity\EikenSchedule')
                ->find($scheduleId);
        if($objEikenSchedule){
           $year = $objEikenSchedule->getYear();
           $kai = $objEikenSchedule->getKai();
           /* @var $specialPrice \Application\Entity\SpecialPrice */
           $specialPrice = $em->getRepository('Application\Entity\SpecialPrice')->getSpecialPriceAllGrade($orgId,$year,$kai);
        }
        return $specialPrice;
    }
    
    public function getArrayHeader($allGradeDiscount){
        $arrayHeader = array();
        $dataDiscountKyu = array();
        if($allGradeDiscount){
            foreach ($allGradeDiscount as $row){
                if($row['special']['hallType'] == 0){
                    $value = $row['special'];
                    $discountKyu = json_decode($value['discountKyu'],true);
                    if($discountKyu){
                        foreach ($discountKyu as $kyuId){
                            $dataDiscountKyu[$row['schoolYearId'].'_'.$kyuId] = $kyuId;
                        }
                        $arrayHeader = array_merge($arrayHeader,$discountKyu);   
                    }
                }
            }
        }
        $arrayHeader = array_unique($arrayHeader);
        sort($arrayHeader);
        return array($arrayHeader,$dataDiscountKyu);
    }
    
    public function getTotalPupilMainHall($orgId,$scheduleId){
        $em = $this->getEntityManager();
        $totalPupilMainHall = $em->getRepository('Application\Entity\ApplyEikenLevel')->getTotalPupilDiscountOfKyu($orgId,$scheduleId);
        $data = array();
    if($totalPupilMainHall){
            $data[1] = isset($totalPupilMainHall[0]['level1']) ? intval($totalPupilMainHall[0]['level1']) : 0;
            $data[2] = isset($totalPupilMainHall[0]['preLevel1']) ? intval($totalPupilMainHall[0]['preLevel1']) : 0;
            $data[3] = isset($totalPupilMainHall[0]['level1']) ? intval($totalPupilMainHall[0]['level2']) : 0;
            $data[4] = isset($totalPupilMainHall[0]['level1']) ? intval($totalPupilMainHall[0]['preLevel2']) : 0;
            $data[5] = isset($totalPupilMainHall[0]['level1']) ? intval($totalPupilMainHall[0]['level3']) : 0;
            $data[6] = isset($totalPupilMainHall[0]['level1']) ? intval($totalPupilMainHall[0]['level4']) : 0;
            $data[7] = isset($totalPupilMainHall[0]['level1']) ? intval($totalPupilMainHall[0]['level5']) : 0;
    }
    
        return $data;
    }
    
    public function saveDataToApplyEikenStudent($applyEikenOrgId,$orgId,$data) {
        if(empty($data) || empty($applyEikenOrgId) || empty($orgId)){
            return false;
        }
        $em = $this->getEntityManager();
        /* @var $objOrg \Application\Entity\Organization */
        $objOrg = $em->getRepository('Application\Entity\Organization')->find($orgId);
        if(empty($objOrg)){ return false; }
        
        $data = json_decode($data,true);
        
        $em->getConnection()->beginTransaction();
        try {
            foreach ($data as $uniGradeId => $recoder){
                
               $kyu = $recoder['kyu'];
               foreach ($kyu as $kyuId => $row){
                    /* @var $applyEikenStudent \Application\Entity\ApplyEikenStudent */
                    $applyEikenStudent = $em->getRepository('Application\Entity\ApplyEikenStudent')
                               ->findOneBy(array(
                                   'applyEikenOrgId' => $applyEikenOrgId,
                                   'orgSchoolYearId' => $row['orgSchoolYear'],
                                   'eikenLevelId' => $kyuId,
                                   'isDelete' => 0
                               ));
                    if(empty($applyEikenStudent)){
                        $applyEikenStudent = new ApplyEikenStudent();
                    }
                    
                    $applyEikenStudent->setApplyEikenOrgId($applyEikenOrgId);
                    $applyEikenStudent->setSchoolYearId($uniGradeId);
                    $applyEikenStudent->setOrgSchoolYearId($row['orgSchoolYear']);
                    $applyEikenStudent->setOrgNo($objOrg->getOrganizationNo());
                    $applyEikenStudent->setEikenLevelId($kyuId);
                    $applyEikenStudent->setTotalStudent(intval($row['totalPupil']) > 0 ? intval($row['totalPupil']) : 0);
                    $applyEikenStudent->setIsDiscount($row['isDiscountKyu']);
                    $em->persist($applyEikenStudent);
               }
            }
            
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $e) {
            $em->getConnection()->rollback();
        }
    }
    
    public function isSpecialOrg($orgId) {
        $em = $this->getEntityManager();
        $scheduleId = $this->getEikenScheduleId();
        if (empty($scheduleId) || empty($orgId)) {
            return 0;
        }

        /* @var $objSchedule \Application\Entity\SpecialPrice */
        $objSchedule = $em->getRepository('Application\Entity\EikenSchedule')->find($scheduleId);
        if (empty($objSchedule)) {
            return 0;
        }

        /* @var $objSpecialPrice \Application\Entity\SpecialPrice */
        $objSpecialPrice = $em->getRepository('Application\Entity\SpecialPrice')
            ->findOneBy(array(
                            'organizationId' => $orgId,
                            'year'           => $objSchedule->getYear(),
                            'kai'            => $objSchedule->getKai(),
                            'isDelete'       => 0,
                        ));

        return $objSpecialPrice ? 1 : 0;
    }

    public function countNumberApplyMainHallEmptyExemption($orgId, $eikenScheduleId){
        $em = $this->getEntityManager();
        return $em->getRepository('Application\Entity\ApplyEikenLevel')
            ->countNumberApplyMainHallEmptyExemption($orgId, $eikenScheduleId);
    }
    public function getCheckCurrentKaibyScheId($eikenschedule)
    {
        if(!empty($eikenschedule)){
            foreach ($eikenschedule as $key => $value)
            {
                $eikenschedule[$key]['isCurrentKai'] = $this->checkScheduleByExamDate($value['scheId']);
            }
        }
        return $eikenschedule;
    }

    //    update for GNCCNCJDR5-778
    public function checkScheduleByExamDate($scheId)
    {
        $em = $this->getEntityManager();
        $schedule = $em->getRepository('Application\Entity\EikenSchedule');
        $scheduleOneKai = $schedule->getEikenScheduleById($scheId);
        if (!empty($scheduleOneKai)) {
            $deadlineFromByKai = !empty($scheduleOneKai['deadlineFrom']) ? $scheduleOneKai['deadlineFrom'] : date(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT);
        }
        $deadlineFromByKai = !empty($deadlineFromByKai) ? $deadlineFromByKai->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) : date(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT);
        $currentDate = date(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT);
        $eikenSchedule = $schedule->getCurrentKaiByYear(date('Y'));
        $currentDeadlineFrom='';
        foreach ($eikenSchedule as $key => $value) {
            if (!empty($value['deadlineFrom']) && $value['deadlineFrom']->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT) <= $currentDate) {
                $currentDeadlineFrom = $value['deadlineFrom']->format(\Dantai\Utility\DateHelper::DATETIME_FORMAT_MYSQL_DEFAULT);
                break;
            }
        }
        
        if ($currentDeadlineFrom === $deadlineFromByKai)
            return TRUE;
        return FALSE;
    }

}
