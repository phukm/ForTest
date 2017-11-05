<?php
namespace DantaiApi\Service;

use Application\Entity\InvitationSetting;
use DantaiApi\Service\ServiceInterface\DantaiApiServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Json\Json;
use Application\Entity\RetrieveBillingInfo;
use Application\Entity\ApplyEikenPersonalInfo;
use Application\Entity\Pupil;
use Application\Entity\ApplyEikenLevel;

class DantaiApiService implements DantaiApiServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     *
     * @var EntityManager
     */
    protected $em;

    protected $validData;

    protected $errorData;

    protected $updatePaymentSuccess;

    protected $errors;

    /**
     * Getting payment status
     *
     * return kekka Result flag if any
     * kekka: 00 - system error
     * kekka: 01 - application error - wrong encrypt key
     * kekka: 02 - orderID does not exist in dP system
     * kekka: 03 - empty payment data
     * kekka: 10 - application valid
     */
    public function gettingPaymentStatus($theData = array())
    {
        if (!empty($theData)) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
            $this->processPaymentData($theData);

            if (!empty($this->errorData)) {
                return '-2 api/payment/update-status 入金通知異常終了' . "\r\n";
            } else {
                return '1 api/payment/update-status 入金通知正常終了' . "\r\n";
            }
        } else {
            return '-2 api/payment/update-status 入金通知異常終了' . "\r\n";
        }
    }
    
    public function callProcessPayment($data) {
        if ($data) {
            $this->em = $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
            $this->processPaymentData($data);
        } else {
            return false;
        }
        if (!empty($this->errorData)) {
            return false;
        }
        return true;
    }

    /**
     * validate payment Data from E-context
     * return kekka Result flag if any
     * kekka: 00 - system error
     * kekka: 01 - application error - wrong encrypt key
     * kekka: 02 - orderID does not exist in dP system
     * kekka: 03 - empty payment data
     * kekka: 10 - application valid
     */
    public function validatePaymentData($paymentData = array())
    {
        foreach ($paymentData as $detail) {
            // orderID - Trong 47 kí tự tiếng anh (half-width characters).
            if (! \Dantai\Utility\CharsetConverter::checkHalfSize($detail['orderID']) || strlen($detail['orderID']) > 47) {
                // return true;
            }
            // 6 ký tự tiếng anh (half-width Character)
            if (! \Dantai\Utility\CharsetConverter::checkHalfSize($detail['shopID']) || strlen($detail['shopID']) > 6) {
                // return true;
            }
            $this->validData[] = $detail;
        }
        return true;
    }
    
    public function saveApplyEikenPersonal(\Application\Entity\Pupil $pupil, $eikenScheduleId, $em){
        $personal = $em->getRepository('Application\Entity\ApplyEikenPersonalInfo')->findOneBy(array(
            'pupilId' => $pupil->getId(),
            'eikenScheduleId' => $eikenScheduleId,
            'isDelete' => 0
        ));
        if ($personal === NULL) {
            $personal = new ApplyEikenPersonalInfo();
            $personal->setBirthday($pupil->getBirthday());
            $personal->setClass($pupil->getClass());
            $personal->setEikenId($pupil->getEikenId());
            $personal->setEikenPassword($pupil->getEikenPassword());
            $personal->setEikenSchedule($em->getReference('Application\Entity\EikenSchedule', $eikenScheduleId));
            $personal->setEmail($pupil->getEmail());
            $personal->setFirstNameAlpha($pupil->getFirstNameAlpha());
            $personal->setFirstNameKana($pupil->getFirstNameKana());
            $personal->setFirstNameKanji($pupil->getFirstNameKanji());
            $personal->setGender($pupil->getGender());
            $personal->setIsSateline(1);
            $personal->setPupil($pupil);
            $personal->setSerialId($pupil->getSerialId());
            $personal->setLastNameAlpha('');
            $personal->setLastNameKana($pupil->getLastNameKana());
            $personal->setLastNameKanji($pupil->getLastNameKanji());
            $personal->setNumber($pupil->getNumber());
            $personal->setOrganization($pupil->getOrganization());
            $personal->setOrgSchoolYear($pupil->getOrgSchoolYear());
            $personal->setPhoneNo($pupil->getPhoneNo());
            $em->persist($personal);
            $em->flush();
        }

        return $personal;
    }
    
    public function saveApplyEikenLevel(\Application\Entity\ApplyEikenPersonalInfo $personal, \Application\Entity\Pupil $pupil, $eikenScheduleId, $eikenLevelId, $price, $dataPayment, $em){
        $applyLevel = $em->getRepository('Application\Entity\ApplyEikenLevel')->findOneBy(array(
            'pupilId' => $pupil->getId(),
            'eikenScheduleId' => $eikenScheduleId,
            'eikenLevelId' => $eikenLevelId,
            'isDelete' => 0
        ));
        if ($applyLevel === NULL) {
            $applyLevel = new ApplyEikenLevel();
        }
        
        $applyLevel->setApplyEikenPersonalInfo($personal);
        $applyLevel->setEikenLevel($em->getReference('Application\Entity\EikenLevel', $eikenLevelId));
        $applyLevel->setRegisterStatus(0);
        $applyLevel->setPupil($pupil);
        $applyLevel->setPaymentStatus(1);
        $applyLevel->setIsSateline(1);
        $applyLevel->setPaymentBy($dataPayment['payBy']);
        $applyLevel->setTuitionFee($price); // get
        //current date time from econtext is 15/08/06 need to convert to 2015/08/06
        $applyLevel->setPaymentDate(new \DateTime('20' . $dataPayment['payDate'])); // get from econtect
        $applyLevel->setEikenSchedule($em->getReference('Application\Entity\EikenLevel', $eikenScheduleId));
        $applyLevel->setIsRegister(1);
        $em->persist($applyLevel);
        $em->flush();
    }
    
    public function processPaymentCredit($pupilId, $paymentInfoId, array $eikenLevels, $data){
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        $pupil = $em->getRepository('Application\Entity\Pupil')->find($pupilId);
        /* #var $paymentInfo \Application\Entity\PaymentInfo */
        $paymentInfo = $em->getRepository('Application\Entity\PaymentInfo')->find($paymentInfoId);
        //singgle record
        $orderID = $data['orderID'];
        try {
            $objRetrieveBillingInfo = $em->getRepository('Application\Entity\RetrieveBillingInfo')->findOneBy(array(
                'orderId' => $orderID,
                'isDelete' => 0
            ));
            if (empty($objRetrieveBillingInfo)) {
                $objRetrieveBillingInfo = new RetrieveBillingInfo();
            }
            $eikenScheduleId = $paymentInfo->getEikenSchedule()->getId();
            $personal = $this->saveApplyEikenPersonal($pupil, $eikenScheduleId, $em);
            foreach ($eikenLevels as $eikenLevelId => $eikenLevel) {
                $this->saveApplyEikenLevel($personal, $pupil, $eikenScheduleId, $eikenLevelId, $eikenLevel['price'], $data, $em);
            }
            $paymentInfo->setPaymentStatus(1);
            $objRetrieveBillingInfo->setPaymentInfo($paymentInfo);
            $objRetrieveBillingInfo->setOrderId($orderID);
            $objRetrieveBillingInfo->setShopId($data['shopID']);
            $objRetrieveBillingInfo->setBillId($data['id']);
            $objRetrieveBillingInfo->setCvsCode($data['cvsCode']);
            $objRetrieveBillingInfo->setKssspCode($data['kssspCode']);
            $objRetrieveBillingInfo->setInputId($data['inputID']);
            $objRetrieveBillingInfo->setOrdAmount($data['ordAmount']);
            $objRetrieveBillingInfo->setPaymentBy($data['payBy']);
            //current date time from econtext is 15/08/06 need to convert to 2015/08/06
            $objRetrieveBillingInfo->setPaymentDate(new \DateTime('20' . $data['payDate']));
            $em->persist($objRetrieveBillingInfo);
            $em->flush();
            $em->getConnection()->commit();
            return true;
        } catch (\Exception $ex) {
            $em->getConnection()->rollback();
            return false;
        }
    }
    
    public function processPaymentData($data = array())
    {
        //singgle record
        $orderID = $data['orderID'];
        $objRetrieveBillingInfo = $this->em->getRepository('Application\Entity\RetrieveBillingInfo')->findOneBy(array(
            'orderId' => $orderID,
            'isDelete' => 0
        ));
        if (empty($objRetrieveBillingInfo)) {
            $objRetrieveBillingInfo = new RetrieveBillingInfo();
        }
        $payment = $this->em->getRepository('Application\Entity\IssuingPayment');
        $objIssuingPayment = $payment->findOneBy(array(
            'orderId' => $orderID,
            'isDelete' => 0
        ));
        
        if ($objIssuingPayment != NULL) {
            $objRetrieveBillingInfo->setPaymentInfoId($objIssuingPayment->getPaymentInfoId());
            $paymentInfo = $objIssuingPayment->getPaymentInfo();
            if ($paymentInfo != NULL) {
                $eikenLevelId = $objIssuingPayment->getEikenLevelId();
                $eikenScheduleId = $paymentInfo->getEikenScheduleId();
                // Update payment status
                $paymentInfo->setPaymentStatus(1);
                $objRetrieveBillingInfo->setPaymentInfo($paymentInfo);
                // Get object pupil
                $objPupil = $paymentInfo->getPupil();
                if ($objPupil != NULL) {
                    // Personal information.
                    $personal = $this->em->getRepository('Application\Entity\ApplyEikenPersonalInfo')->findOneBy(array(
                        'pupilId' => $objPupil->getId(),
                        'eikenScheduleId' => $eikenScheduleId,
                        'isDelete' => 0
                    ));
                    if ($personal === NULL) {
                        $personal = new ApplyEikenPersonalInfo();
                        $personal->setIsSateline(0);
                        $personal->setBirthday($objPupil->getBirthday());
                        $personal->setClass($objPupil->getClass());
                        $personal->setEikenId($objPupil->getEikenId());
                        $personal->setEikenPassword($objPupil->getEikenPassword());
                        $personal->setEikenSchedule($paymentInfo->getEikenSchedule());
                        $personal->setEmail($objPupil->getEmail());
                        $personal->setFirstNameAlpha($objPupil->getFirstNameAlpha());
                        $personal->setFirstNameKana($objPupil->getFirstNameKana());
                        $personal->setFirstNameKanji($objPupil->getFirstNameKanji());
                        $personal->setGender($objPupil->getGender());
                        $personal->setPupil($objPupil);
                        // $personal->setPupilId($objPupil->getId());
                        $personal->setSerialId($objPupil->getSerialId());
                        $personal->setLastNameAlpha('');
                        $personal->setLastNameKana($objPupil->getLastNameKana());
                        $personal->setLastNameKanji($objPupil->getLastNameKanji());
                        $personal->setNumber($objPupil->getNumber());
                        $personal->setOrganization($objPupil->getOrganization());
                        $personal->setOrgSchoolYear($objPupil->getOrgSchoolYear());
                        $personal->setPhoneNo($objPupil->getPhoneNo());
                    }
                    // $personal->setRecommendedLevel($objPupil->get);
                    // $personal->setRegisterLevel();
                    $this->em->persist($personal);
                    // Apply eiken level information.
                    $applyLevel = $this->em->getRepository('Application\Entity\ApplyEikenLevel')->findOneBy(array(
                        'pupilId' => $objPupil->getId(),
                        'eikenScheduleId' => $eikenScheduleId,
                        'eikenLevelId' => $eikenLevelId,
                        'isDelete' => 0,
                        'tuitionFee' => $objIssuingPayment->getPrice()
                    ));
                    //get hall type from invitation setting
                    /** @var InvitationSetting $invitation */
                    $invitation = $this->em->getRepository('Application\Entity\InvitationSetting')
                        ->findOneBy(array(
                                        'organizationId'  => $objPupil->getOrganizationId(),
                                        'eikenScheduleId' => $eikenScheduleId,
                                    ));
                    if ($applyLevel === NULL) {
                        $applyLevel = new ApplyEikenLevel();
                        $applyLevel->setIsRegister(0);
                        $applyLevel->setApplyEikenPersonalInfo($personal);
                        $applyLevel->setEikenLevel($objIssuingPayment->getEikenLevel());
                        $applyLevel->setRegisterStatus(0);
                        $applyLevel->setPupil($objPupil);
                        $applyLevel->setIsSateline(0);
                        $applyLevel->setTuitionFee($objIssuingPayment->getPrice());
                        $applyLevel->setEikenSchedule($paymentInfo->getEikenSchedule());

                        if ($objIssuingPayment->getEikenLevel()->getId() == 1 || $objIssuingPayment->getEikenLevel()->getId() == 2) {
                            $applyLevel->setHallType(1);
                        }
                        else if ($invitation->getTempHallType() == 0) {
                            // auto register apply eiken when hallType is standard.
                            $applyLevel->setHallType(0);
                            $applyLevel->setIsRegister(1);
                            $applyLevel->setRegisterDate(new \DateTime());
                            $applyLevel->setRegDateOnSatellite(new \DateTime());
                            $applyLevel->setIsSubmit(0);
                        }
                        else {
                            $applyLevel->setHallType($invitation->getTempHallType());
                        }
                    }
                    $applyLevel->setPaymentStatus(1);
                    $applyLevel->setPaymentBy($data['payBy']);
                    //current date time from econtext is 15/08/06 need to convert to 2015/08/06
                    $applyLevel->setPaymentDate(new \DateTime('20' . $data['payDate'])); // get from econtect
                    $this->em->persist($applyLevel);
                }
            }
            $objRetrieveBillingInfo->setOrderId($orderID);
            $objRetrieveBillingInfo->setShopId($data['shopID']);
            $objRetrieveBillingInfo->setBillId($data['id']);
            $objRetrieveBillingInfo->setCvsCode($data['cvsCode']);
            $objRetrieveBillingInfo->setKssspCode($data['kssspCode']);
            $objRetrieveBillingInfo->setInputId($data['inputID']);
            $objRetrieveBillingInfo->setOrdAmount($data['ordAmount']);
            $objRetrieveBillingInfo->setPaymentBy($data['payBy']);
            //current date time from econtext is 15/08/06 need to convert to 2015/08/06
            $objRetrieveBillingInfo->setPaymentDate(new \DateTime('20' . $data['payDate']));
            $this->em->persist($objRetrieveBillingInfo);
            $this->em->flush();
            $this->em->clear();
        } else {
            $this->errorData = $data;
        }
        return true;
    }

    /**
     * get Schedule Setting
     */
    protected function getDantaiApiSchedule()
    {
        if (! self::$DantaiApiSchedule) {
            $em = $this->getEntityManager();
            $DantaiApiSchedule = $em->getRepository('Application\Entity\DantaiApiSchedule')->getAvailableDantaiApiScheduleByDate(date('Y'), date('Y-m-d H:i:s'));
            if (! empty($DantaiApiSchedule)) {
                self::$DantaiApiSchedule = $DantaiApiSchedule;
            }
        }
        return self::$DantaiApiSchedule;
    }

    public function getDantaiApiDetail($hallType = 0)
    {
        $em = $this->getEntityManager();
        return $em->getRepository('Application\Entity\DantaiApi')->getDantaiApiOrgDetailByParams($this->getOrganizationId(), $this->getDantaiApiScheduleId(), $hallType);
    }

    /**
     * Get DantaiApi Apply Pupil
     */
    public function getApplyDantaiApiPersonal($DantaiApiSchedule = 0)
    {
        if (empty($DantaiApiSchedule)) {
            $DantaiApiSchedule = $this->getDantaiApiScheduleId();
        }
        $em = $this->getEntityManager();
        return $em->getRepository('Application\Entity\ApplyDantaiApiLevel')->getApplyDantaiApiPersonal($this->getOrganizationId(), $DantaiApiSchedule);
    }

    /**
     * get Current Apply DantaiApi Org by orgId & DantaiApiScheduleId
     */
    public function getDantaiApi()
    {
        if (! self::$DantaiApi) {
            $em = $this->getEntityManager();
            self::$DantaiApi = $em->getRepository('Application\Entity\DantaiApi')->getDantaiApiOrgByParams($this->getOrganizationId(), $this->getDantaiApiScheduleId());
        }
        return self::$DantaiApi;
    }

    public function getDantaiApiLevel()
    {
        $em = $this->getEntityManager();
        return $em->getRepository('Application\Entity\DantaiApiLevel')->getPriceForAllLevel();
    }

    public function getOrganizationByNumber($orgNumber = '')
    {
        if (! self::$organization) {
            $em = $this->getEntityManager();
            self::$organization = $em->getRepository('Application\Entity\Organization')->getOrganizationByNo($orgNumber);
        }
        return self::$organization;
    }

    /**
     *
     * @return array|object
     */
    protected function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
}