<?php
namespace Satellite\Controller;

use Application\Entity\ApplyEikenLevel;
use InvitationMnt\InvitationConst;
use Satellite\Constants;
use Satellite\Form\TestSideExemptionForm;
use Zend\View\Model\ViewModel;
use Dantai\PrivateSession;
use Dantai;
use Doctrine\ORM\Query;
use Satellite\Service\EikenService;
use Satellite\Service\PaymentEikenExamService;
use Satellite\Service\ServiceInterface\EikenServiceInterface;
use Application\Service\ServiceInterface\DantaiServiceInterface;
use Application\Service\DantaiService;
use Doctrine\ORM\EntityManager;
use Satellite\View\Helper\SatelliteCommon;

class EikenController extends BaseController
{
    protected $em;

    /**
     * @var DantaiService
     */
    protected $dantaiService;

    /**
     * @var EikenService
     */
    protected $eikenService;
    
    protected $identity;

    protected $routeMatch;
    
    protected $privateSession;
    
    protected $userIdentity;

    /**
     * @var PaymentEikenExamService $paymentEikenExam
     */
    protected $paymentEikenExam;


    public function __construct(DantaiServiceInterface $dantaiService, EikenServiceInterface $eikenService, EntityManager $entityManager) {
        parent::__construct();
        $this->dantaiService = $dantaiService;
        $this->eikenService = $eikenService;
        $this->em = $entityManager;
        $this->satelliteService = new \Satellite\Service\SatelliteService();
    }

    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->translator = $this->getServiceLocator()->get('MVCTranslator');
        $this->privateSession = new PrivateSession();
        $this->userIdentity = $this->privateSession->getData(Constants::SESSION_SATELLITE);
        $this->paymentEikenExam = new PaymentEikenExamService($this->getServiceLocator());

        return parent::onDispatch($e);
    }

    public function showAction(){
        $em = $this->getEntityManager();
        if ($this->paymentEikenExam->checkCurrentDate($this->userIdentity['deadline'])) {
            $translator = $this->getServiceLocator()->get('MVCTranslator');
            $messageDeadline = sprintf($translator->translate('eikenApplicationEndDateLt8'), $this->userIdentity['deadline']['year'], $this->userIdentity['deadline']['kai']);
        }
        $data = $this->userIdentity;
        $applyEikenId = $this->params('id', 0);
        /** @var ApplyEikenLevel $applyEikenLevel */
        $applyEikenLevel = $this->eikenService->getApplyEikenLevel($applyEikenId, $data['pupilId']);
        if (!isset($applyEikenLevel)) {
            return $this->redirect()->toUrl('/');
        }

        $kyu = $this->paymentEikenExam->getPaymentInformationMuntilKyu($this->userIdentity);

        $applyEikenPersonalInfo = $this->eikenService->getApplyEikenPersonalInfo($data['pupilId'], $data['eikenScheduleId']);
        $form = new TestSideExemptionForm($this->getServiceLocator());
        if ($applyEikenLevel->getPaymentStatus() != 1 || $applyEikenLevel->getPaymentBy() != 1) {
            $receiptNoTelNo = $this->eikenService->getReceiptNoTelNo($data['pupilId'], $data['eikenScheduleId'], $applyEikenLevel->getEikenLevelId());
        }
        // get list examGrade (level).
        $config = $this->getServiceLocator()->get('config');
        $listLevel = $config['MappingLevel'];
        $listJob = $config['Job_Code'];
        $listHallType = $config['HallType'];
        $listDistrictByCode = $this->eikenService->getListDistrictByCode();
        $listCity = $em->getRepository('Application\Entity\City')->getListCity();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $passKai = null;
        if (isset($applyEikenLevel) && !empty($applyEikenLevel->getFirstPassedTime())) {
            list($year, $kai) = split('[|]', $applyEikenLevel->getFirstPassedTime());
            $passKai = $year . $translator->translate('PassedKai1') . $kai . $translator->translate('PassedKai2');
        }
        $isSupportCombini = (strpos($data['personalPayment'], '1') !== false);
        $schoolCodeConfig = $config['School_Code'];
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
                                     'form'                   => $form,
                                     'isMobile'               => $this->isMobile,
                                     'listLevel'              => $listLevel,
                                     'applyEiken'             => $applyEikenLevel,
                                     'applyEikenPersonalInfo' => $applyEikenPersonalInfo,
                                     'listJob'                => $listJob,
                                     'kyu'                    => $kyu,
                                     'listHallType'           => $listHallType,
                                     'passKai'                => $passKai,
                                     'receiptNoTelNo'         => isset($receiptNoTelNo) ? $receiptNoTelNo : '',
                                     'isSupportCombini'       => $isSupportCombini,
                                     'messageDeadline'        => isset($messageDeadline) ? $messageDeadline : '',
                                     'listDistrictByCode'     => $listDistrictByCode,
                                     'listCity'               => $listCity,
                                     'listSchoolCode'               => $schoolCodeConfig
                                 ));

        $this->layout('layout/' . ($this->isMobile ? 'mobile' : 'layout'));

        return $viewModel;
    }

    public function confirmationAction(){
        $data = PrivateSession::getData(Constants::SESSION_APPLYEIKEN);
        $exemptionData = PrivateSession::getData(Constants::DATA_TEST_SITE_EXEMPTION);
        
        $partFolder = $this->userIdentity['deadline']['year'] . $this->userIdentity['deadline']['kai'] . "/" . $this->userIdentity['organizationNo'];                
        $logData = array_merge((array)$data,(array)$exemptionData);
        $this->dantaiService->writeLog(\Satellite\Constants::LOG_APPLY_EIKEN_JUKENSHA, $partFolder, $logData, 'confirmationAction', 'DATA WHENE GO TO CONFIRMATION PAGE FOR DANTAINO : ' . $this->userIdentity['organizationNo'] . ' PUPILID : ' . $this->userIdentity['pupilId']);
        
        $em = $this->getEntityManager();
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        if (empty($data)) {
            return $this->redirect()->toUrl('/');
        }
        $kyu = $data['kyu'];
        $form = new \Satellite\Form\TestSideExemptionForm($this->getServiceLocator());
        // get list examGrade (level).
        $config = $this->getServiceLocator()->get('config');
        $listLevel = $config['MappingLevel'];
        $listJob = $config['Job_Code'];
        $listHallType = $config['HallType'];
        $listCity = $em->getRepository('Application\Entity\City')->getListCity();
        $listDistrict = $em->getRepository('Application\Entity\District')->getListDistrict();
        $listDistrictByCode = $em->getRepository('Application\Entity\District')->getListDistrictByCode();
        $data['cityName'] = empty($data['ddlCity']) ? '' : $this->eikenService->getCity($data['ddlCity'])->getCityName();
        $data['txtPhoneNo'] = $data['txtPhoneNo1'] . '-' . $data['txtPhoneNo2'] . '-' . $data['txtPhoneNo3'];

        if (!empty($exemptionData['passedKai1'])) {
            list($year, $kai) = split('[|]', $exemptionData['passedKai1']);
            $passKaiText['passedKaiText1'] = $year . $translator->translate('PassedKai1') . $kai . $translator->translate('PassedKai2');
        }

        if (!empty($exemptionData['passedKai2'])) {
            list($year, $kai) = split('[|]', $exemptionData['passedKai2']);
            $passKaiText['passedKaiText2'] = $year . $translator->translate('PassedKai1') . $kai . $translator->translate('PassedKai2');
        }
        $config = $this->getServiceLocator()->get('Config');
        $listSchoolCode= $config['School_Code'];
        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
                                     'form'               => $form,
                                     'data'               => $data,
                                     'isMobile'           => $this->isMobile,
                                     'listLevel'          => $listLevel,
                                     'listJob'            => $listJob,
                                     'listSchoolCode'     => $listSchoolCode,
                                     'kyu'                => $kyu,
                                     'listHallType'       => $listHallType,
                                     'translate'          => $this->getTranslation(),
                                     'exemptionData'      => $exemptionData,
                                     'listCity'           => $listCity,
                                     'listDistrict'       => $listDistrict,
                                     'passKaiText'        => (empty($passKaiText)) ? '' : $passKaiText,
                                     'listDistrictByCode' => (empty($listDistrictByCode)) ? '' : $listDistrictByCode,
                                 ));

        $this->layout('layout/' . ($this->isMobile ? 'mobile' : 'layout'));

        return $viewModel;
    }

    public function testSiteExemptionAction()
    {
        $viewModel = new ViewModel();
        $form = new \Satellite\Form\TestSideExemptionForm($this->getServiceLocator());
        $request = $this->getRequest();
        $dataApply = PrivateSession::getData(Constants::SESSION_APPLYEIKEN);
        if(empty($dataApply)){
            return $this->redirect()->toUrl('/');
        }
        $dataExemption = PrivateSession::getData(Constants::DATA_TEST_SITE_EXEMPTION);       
        $em = $this->getEntityManager();
        // if user click submit button
        if($request->isPost()){
            $data = $request->getPost();
            $form->setData($data);
            $result = $this->eikenService->validateTestSideExemption($data);
            if(empty($result)){
                $partFolder = $this->userIdentity['deadline']['year'] . $this->userIdentity['deadline']['kai'] . "/" . $this->userIdentity['organizationNo'];                
                $logData = array_merge((array)$dataApply,(array)$data);
                $this->dantaiService->writeLog(\Satellite\Constants::LOG_APPLY_EIKEN_JUKENSHA, $partFolder, $logData, 'testSiteExemptionAction', 'DATA APPTER VALIDATE EXAM LOCALTION FOR DANTAINO : ' . $this->userIdentity['organizationNo'] . ' PUPILID : ' . $this->userIdentity['pupilId']);
                PrivateSession::setData(Constants::DATA_TEST_SITE_EXEMPTION, $data);
                return $this->redirect()->toUrl('/eiken/confirmation');
            }
        }
        $i = 0;
        $listKyuMainHall = array();
        foreach ($dataApply['kyu'] as $key => $kyuInfo) {
            if($kyuInfo['hallType'] == 0) continue;
            array_push($listKyuMainHall, $key);
            $i++;

            $conditionField = $this->eikenService->getExamLocationCondition($key);
            // update code for :#F1GNCJIEMDPR6-495
            $cities1 = SatelliteCommon::generateSelectOptions($em->getRepository('Application\Entity\City')->getApplyEikCitiesList(false, $conditionField,true), 'getCityName');
            $form->get("firstTestCity".$i)->setValueOptions($cities1);

            $conditionField2 = $this->eikenService->getExamLocationCondition($key, 0);
            // update code for :#F1GNCJIEMDPR6-495
            $cities2 = SatelliteCommon::generateSelectOptions($em->getRepository('Application\Entity\City')->getApplyEikCitiesList(false, $conditionField2,true), 'getCityName');
            $form->get("secondTestCity".$i)->setValueOptions($cities2);
            
            $cities3 = SatelliteCommon::generateSelectOptions($em->getRepository('Application\Entity\City')->getListCityWithDistrict(), 'getCityName');
            $form->get("secondTestCity".$i)->setValueOptions($cities3);
            $form->get("passedCity".$i)->setValueOptions($cities3);

            // get list examGrade (level).
            $kaiOptions = $this->eikenService->getKaiOptions($this->userIdentity['eikenScheduleId']);
            $form->get("passedKai".$i)->setValueOptions($kaiOptions);
            $form->get("exemption".$i)->setValue(0);
        }
        $isNeedTestSiteExemption = $i > 1;

        $config = $this->getServiceLocator()->get('config');
        
        $listLevel = $config['MappingLevel'];
        $listDistrict = $em->getRepository('Application\Entity\District')->getListDistrictByCode();   
        
        if(!empty($dataExemption)){
            PrivateSession::clear(Constants::DATA_TEST_SITE_EXEMPTION);
            foreach ($dataExemption as $key => $value){
                if($key == 'firstExamPlace1' || $key == 'secondExamPlace1' || $key == 'firstExamPlace2' || $key == 'secondExamPlace2'){
                    $cityId = '';
                    $chooseKyuChecked = 0;
                    if($key == 'firstExamPlace1'){ $cityId = $dataExemption['firstTestCity1']; };
                    if($key == 'secondExamPlace1'){ $cityId = $dataExemption['secondTestCity1']; };
                    if($key == 'firstExamPlace2'){ $cityId = $dataExemption['firstTestCity2']; $chooseKyuChecked= 1; };
                    if($key == 'secondExamPlace2'){ $cityId = $dataExemption['secondTestCity2']; $chooseKyuChecked= 1; };
                    $dataDisTrict = $this->eikenService->loadMainHall((int)$cityId, $dataApply['chooseKyu'][$chooseKyuChecked], 1);
                    $form->get($key)->setValueOptions($dataDisTrict);
                }
                if ( $key == 'passedPlace1' || $key == 'passedPlace2'){
                    if($key == 'passedPlace1'){ $cityId = $dataExemption['passedCity1']; };
                    if($key == 'passedPlace2'){ $cityId = $dataExemption['passedCity2']; };
                    $dataDisTrict = SatelliteCommon::generateSelectOptionsWithCustomValue($em->getRepository('Application\Entity\District')
                            ->findBy(array('cityId' => $cityId,'isDelete' => 0)), 'getCode', 'getName');
                    
                    $form->get($key)->setValueOptions($dataDisTrict);
                }
                if($key != 'examGrade1' && $key != 'examGrade2'){                         
                    if (($key == 'passedPlace1' && !empty($value)) || ($key == 'passedPlace2' && !empty($value)) )                    
                        $form->get($key)->setValue($listDistrict[$value]['code']); 
                     else 
                         $form->get($key)->setValue($value);
                }               
            }
        }

        $viewModel->setVariables(array(
            'form'   => $form,
            'isNeedTestSiteExemption' => $isNeedTestSiteExemption,
            'isMobile' => $this->isMobile,
            'listLevel' => $listLevel,
            'dataApply' => $dataApply,
            'listKyuMainHall' => $listKyuMainHall
        ));

        $this->layout('layout/'. ($this->isMobile ? 'mobile' : 'layout'));
        
        return $viewModel;
    }
    
    public function getEntityAuthenticateManager()
    {
        return $this->getServiceLocator()->get('doctrine.authenticationservice.orm_default');
    }
    
    public function applyEikenAction()
    {
        $data = $this->userIdentity;
        $form = new \Satellite\Form\ApplyEikenForm($this->getServiceLocator());
        $doubleEikenNotSupport = 0;
        if (isset($data['doubleEiken']) && $data['doubleEiken'] == Constants::DOUBLE_EIKEN) {
            $doubleEikenNotSupport = 1;
        }
        $translator = $this->getServiceLocator()->get('MVCTranslator');

        $beneficiaryValue = $data['beneficiary'];
        // if $isSemiDiscount = 1 then price is discount (price of standard hall).
        $isSemiDiscount = ($beneficiaryValue == 2) ? 1 : 0;
        // get orgShooYearID
        $orgSchoolYearId = $this->paymentEikenExam->getOrgSchoolYearIDbyPupilId($data['pupilId']);
        $pramPrice = array(
            'orgNo' => $data['organizationNo'],
            'orgSchoolYearId'=>$orgSchoolYearId,
            'year'=>$data['deadline']['year'],
            'kai'=>$data['deadline']['kai']);
        
        $eikenLevelPrice = $this->dantaiService->getListPriceOfOrganization($data['organizationNo'], array(1, 2, 3, 4, 5, 6, 7),$pramPrice);
        $examDate = $this->em->getRepository('Application\Entity\EikenSchedule')->find($data['eikenScheduleId']);
        $hallTypePrice = $isSemiDiscount ? 0 : $data['hallType'];
        $kyu = $this->paymentEikenExam->mappingKyu($eikenLevelPrice[$hallTypePrice], $examDate, $data['listEikenLevel'], $data['hallTypeExamDay']);
        $kyu2 = $this->paymentEikenExam->mappingKyu($eikenLevelPrice[$isSemiDiscount ? 0 : 1], $examDate, $data['listEikenLevel'], $data['hallTypeExamDay'], array(), true);
        $applyEikenLevel = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenLevel')->findOneBy(array('pupilId' => $data['pupilId'], 'eikenScheduleId' => $data['eikenScheduleId'], 'isDelete' => 0, 'isRegister' => 1), array('eikenLevelId' => 'ASC'));
        $applyEikenLevelId = empty($applyEikenLevel) ? '' : $applyEikenLevel->getEikenLevelId();
        $listIsPayment = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenLevel')->findBy(array('pupilId' => $data['pupilId'], 'eikenScheduleId' => $data['eikenScheduleId'], 'isDelete' => 0, 'paymentStatus' => 1), array('eikenLevelId' => 'ASC'));
        $listIsRegister = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenLevel')->findBy(array('pupilId' => $data['pupilId'], 'eikenScheduleId' => $data['eikenScheduleId'], 'isDelete' => 0), array('eikenLevelId' => 'ASC'));
        $isPayment = array();
        $isRegister = array();
        if (!empty($listIsPayment)) {
            foreach ($listIsPayment as $value) {
                array_push($isPayment, $value->getEikenLevelId());
            }
        }
        if (!empty($listIsRegister)) {
            foreach ($listIsRegister as $value) {
                array_push($isRegister, $value->getEikenLevelId());
            }
        }
        if ($this->getRequest()->isPost()) {
            $dataPost = $this->getRequest()->getPost();
            // create kyu price data.
            $kyuMainInfo = $this->paymentEikenExam->mappingKyu($eikenLevelPrice[$isSemiDiscount ? 0 : 1], $examDate, $data['listEikenLevel'], $data['hallTypeExamDay'], array(), true);
            $kyuStandardInfo = $this->paymentEikenExam->mappingKyu($eikenLevelPrice[0], $examDate, $data['listEikenLevel'], $data['hallTypeExamDay']);
            $dataPost['kyu'] = $this->getListKyuPriceFromData($dataPost, array($kyuStandardInfo, $kyuMainInfo));
            
            /*@var $pupilInfo \Application\Entity\Pupi */
            $pupilInfo = $this->em->getRepository('Application\Entity\Pupil')->findOneBy(array('id' => $data['pupilId'], 'isDelete' => 0));
            if($pupilInfo){
                
                $dataPost['txtFirstNameKanji'] = $pupilInfo->getFirstNameKanji();
                $dataPost['txtLastNameKanji']  = $pupilInfo->getLastNameKanji();
                
                /*@var $applyPersional \Application\Entity\ApplyEikenPersonalInfo */
                $applyPersional = $this->em->getRepository('Application\Entity\ApplyEikenPersonalInfo')->getInforStudent($data['pupilId']);
                if($applyPersional && !empty($applyPersional->getEikenId())){
                    $year = !empty($applyPersional->getBirthday())? $applyPersional->getBirthday()->format('Y'):'';
                    $month = !empty($applyPersional->getBirthday())? $applyPersional->getBirthday()->format('m'):'';
                    $day = !empty($applyPersional->getBirthday())? $applyPersional->getBirthday()->format('d'):'';
                    
                    $postalCode = $applyPersional->getPostalCode() ? explode('-',$applyPersional->getPostalCode()) : '';
                    $postalCode1 = isset($postalCode[0]) ? $postalCode[0] : '';
                    $postalCode2 = isset($postalCode[1]) ? $postalCode[1] : '';
                    $phoneNumber = $applyPersional->getPhoneNo();
                    $phoneNumber = $phoneNumber ? explode('-',$phoneNumber) : '';
                    $phoneNo1 = isset($phoneNumber[0]) ? $phoneNumber[0] : '';
                    $phoneNo2 = isset($phoneNumber[1]) ? $phoneNumber[1] : '';
                    $phoneNo3 = isset($phoneNumber[2]) ? $phoneNumber[2] : '';
                    
                    $dataPost['txtFirstNameKana']  = $applyPersional->getFirstNameKana();
                    $dataPost['txtLastNameKana']   = $applyPersional->getLastNameKana();
                    $dataPost['rdSex']             = $applyPersional->getGender();
                    $dataPost['ddlYear']           = $year;
                    $dataPost['ddlMonth']          = $month;
                    $dataPost['ddlDay']            = $day;
                    $dataPost['txtPostalCode1']    = $postalCode1;
                    $dataPost['txtPostalCode2']    = $postalCode2;
                    $dataPost['ddlCity']           = $applyPersional->getCity()->getId();
                    $dataPost['txtDistrict']       = $applyPersional->getDistrict();
                    $dataPost['txtTown']           = $applyPersional->getTown();
                    $dataPost['txtPhoneNo1']       = $phoneNo1;
                    $dataPost['txtPhoneNo2']       = $phoneNo2;
                    $dataPost['txtPhoneNo3']       = $phoneNo3;
                    $dataPost['txtEmail']          = $applyPersional->getEmail();
                    $dataPost['ddlJobName']        = $applyPersional->getJobCode();
                    if($applyPersional->getJobCode() == 1){
                        $dataPost['ddlSchoolCode'] = $applyPersional->getSchoolType();
                    }
                    
                }
            }
                
            $this->flashMessenger()->clearMessages('SystemError'); 
            if (empty($this->eikenService->validateApplyEiken($dataPost, $doubleEikenNotSupport))) {
                $this->privateSession->setData(Constants::SESSION_APPLYEIKEN, $dataPost);
                if ($dataPost['exemption'] == 1) {
                    PrivateSession::clear(Constants::DATA_TEST_SITE_EXEMPTION);
                    return $this->redirect()->toUrl('/eiken/test-site-exemption');
                }
                return $this->redirect()->toUrl('/eiken/confirmation');
            }
            $this->flashMessenger()->addMessage($translator->translate('SystemError'), 'SystemError');
            return $this->redirect()->toUrl('/eiken/apply-eiken');
        }
        $error = $this->flashMessenger()->getMessages('SystemError');
        $curYear = date("Y");
        $listyear = array('' => '');
        for ($i = $curYear; $i >= $curYear - 30; $i--) {
            $listyear [$i] = $this->gengo($i);
        }
        $form->get("ddlYear")->setValueOptions($listyear);
        $city = $this->em->getRepository('Application\Entity\City')->findAll();
        $listCity[''] = '';
        foreach ($city as $key => $value) {
            $listCity[$value->getId()] = $value->getCityName();
        }
        $form->get("ddlCity")->setValueOptions($listCity);
        $listSchoolCode[''] = '';
        $config = $this->getServiceLocator()->get('Config');
        $schoolCodeConfig = $config['School_Code'];
        if($schoolCodeConfig){
            foreach ($schoolCodeConfig as $key => $value) {
                $listSchoolCode[$key] = $value;
            }
        }
        $form->get("ddlSchoolCode")->setValueOptions($listSchoolCode);
        if ($this->privateSession->getData(Constants::SESSION_APPLYEIKEN)) {
            $dataSession = $this->privateSession->getData(Constants::SESSION_APPLYEIKEN);
            $this->privateSession->clear(Constants::SESSION_APPLYEIKEN);
            $this->privateSession->clear(Constants::DATA_TEST_SITE_EXEMPTION);
            $form->setHydrator(new \Zend\Stdlib\Hydrator\ObjectProperty());
            $form->setListBirthDay();
            $form->bind($dataSession);
            $dataKyu = $dataSession['chooseKyu'];
            
            /*@var $pupilInfo \Application\Entity\Pupil */
            $form->get("txtFirstNameKanji")->setAttribute('disabled', 'disabled');
            $form->get("txtLastNameKanji")->setAttribute('disabled', 'disabled');
            /*@var $applyPersional \Application\Entity\ApplyEikenPersonalInfo*/
            $applyPersional = $this->em->getRepository('Application\Entity\ApplyEikenPersonalInfo')->getInforStudent($data['pupilId']);
            if($applyPersional && !empty($applyPersional->getEikenId())){
                $form->get("txtFirstNameKana")->setAttribute('disabled', 'disabled');
                $form->get("txtLastNameKana")->setAttribute('disabled', 'disabled');
                $form->get("rdSex")->setAttribute('disabled', 'disabled');
                $form->get("ddlYear")->setAttribute('disabled', 'disabled');
                $form->get("ddlMonth")->setAttribute('disabled', 'disabled');
                $form->get("ddlDay")->setAttribute('disabled', 'disabled');
                $form->get("txtPostalCode1")->setAttribute('disabled', 'disabled');
                $form->get("txtPostalCode2")->setAttribute('disabled', 'disabled');
                $form->get("ddlCity")->setAttribute('disabled', 'disabled');
                $form->get("txtDistrict")->setAttribute('disabled', 'disabled');
                $form->get("txtTown")->setAttribute('disabled', 'disabled');
                $form->get("txtPhoneNo1")->setAttribute('disabled', 'disabled');
                $form->get("txtPhoneNo2")->setAttribute('disabled', 'disabled');
                $form->get("txtPhoneNo3")->setAttribute('disabled', 'disabled');
                $form->get("txtEmail")->setAttribute('disabled', 'disabled');
                $form->get("ddlJobName")->setAttribute('disabled', 'disabled');
                $form->get("ddlSchoolCode")->setAttribute('disabled', 'disabled');
            }
            
        }
        else {
            $pupilInfo = $this->em->getRepository('Application\Entity\Pupil')->findOneBy(array('id' => $data['pupilId'], 'isDelete' => 0));
            if ($pupilInfo) {
                /*@var $pupilInfo \Application\Entity\Pupil */
                $form->get("txtFirstNameKanji")->setValue($pupilInfo->getFirstNameKanji())->setAttribute('disabled', 'disabled');
                $form->get("txtLastNameKanji")->setValue($pupilInfo->getLastNameKanji())->setAttribute('disabled', 'disabled');
                $form->get("txtFirstNameKana")->setValue($pupilInfo->getFirstNameKana());
                $form->get("txtLastNameKana")->setValue($pupilInfo->getLastNameKana());
                $form->get("rdSex")->setValue($pupilInfo->getGender());
                $birthDay = $pupilInfo->getBirthday();
                $form->setListBirthDay($birthDay);
                /*@var $applyPersional \Application\Entity\ApplyEikenPersonalInfo*/
                $applyPersional = $this->em->getRepository('Application\Entity\ApplyEikenPersonalInfo')->getInforStudent($data['pupilId']);
                if($applyPersional && !empty($applyPersional->getEikenId())){
                    $postalCode = $applyPersional->getPostalCode() ? explode('-',$applyPersional->getPostalCode()) : '';
                    $postalCode1 = isset($postalCode[0]) ? $postalCode[0] : '';
                    $postalCode2 = isset($postalCode[1]) ? $postalCode[1] : '';
                    
                    $phoneNumber = $applyPersional->getPhoneNo();
                    $phoneNumber = $phoneNumber ? explode('-',$phoneNumber) : '';
                    $phoneNo1 = isset($phoneNumber[0]) ? $phoneNumber[0] : '';
                    $phoneNo2 = isset($phoneNumber[1]) ? $phoneNumber[1] : '';
                    $phoneNo3 = isset($phoneNumber[2]) ? $phoneNumber[2] : '';
                    
                    $form->get("txtFirstNameKana")->setValue($applyPersional->getFirstNameKana())->setAttribute('disabled', 'disabled');
                    $form->get("txtLastNameKana")->setValue($applyPersional->getLastNameKana())->setAttribute('disabled', 'disabled');
                    $form->get("rdSex")->setValue($applyPersional->getGender())->setAttribute('disabled', 'disabled');
                    $form->get("ddlYear")->setAttribute('disabled', 'disabled');
                    $form->get("ddlMonth")->setAttribute('disabled', 'disabled');
                    $form->get("ddlDay")->setAttribute('disabled', 'disabled');
                    $birthDay = $applyPersional->getBirthday();
                    $form->setListBirthDay($birthDay);
                    
                    $form->get("txtPostalCode1")->setValue($postalCode1)->setAttribute('disabled', 'disabled');
                    $form->get("txtPostalCode2")->setValue($postalCode2)->setAttribute('disabled', 'disabled');
                    $form->get("ddlCity")->setValue($applyPersional->getCity()->getId())->setAttribute('disabled', 'disabled');
                    $form->get("txtDistrict")->setValue($applyPersional->getDistrict())->setAttribute('disabled', 'disabled');
                    $form->get("txtTown")->setValue($applyPersional->getTown())->setAttribute('disabled', 'disabled');
                    $form->get("txtPhoneNo1")->setValue($phoneNo1)->setAttribute('disabled', 'disabled');
                    $form->get("txtPhoneNo2")->setValue($phoneNo2)->setAttribute('disabled', 'disabled');
                    $form->get("txtPhoneNo3")->setValue($phoneNo3)->setAttribute('disabled', 'disabled');
                    $form->get("txtEmail")->setValue($applyPersional->getEmail())->setAttribute('disabled', 'disabled');
                    $form->get("ddlJobName")->setValue($applyPersional->getJobCode())->setAttribute('disabled', 'disabled');
                    $form->get("ddlSchoolCode")->setValue($applyPersional->getSchoolType())->setAttribute('disabled', 'disabled');
                    
                }
                
            }
            for ($i = 3; $i <= 7; $i++) {
                if ($data['hallType'] == 1) {
                    $form->get("hallType" . $i)->setValueOptions(array(
                        '0' => '準会場',
                        '1' => '本会場'
                    ));
                }
                $form->get("hallType" . $i)->setValue($data['hallType']);
            }
        }
        $this->layout('layout/' . ($this->isMobile ? 'mobile' : 'layout'));
        $paymentInfomationStatus = $this->layout()->getVariable('paymentInfomationStatus');
        $messagePaymentKyu = '';
        
        $kyuIdApplied = array_merge($data['kyuIdAppliedNonDS'], $data['kyuIdAppliedDS']);
        sort($kyuIdApplied);
        if($data['kyuAppliedStatus'] !== Constants::EIKEN_APPLIED_SUCCESS){
            // convert error code '0' -> '00'
            $data['kyuAppliedStatus'] = $data['kyuAppliedStatus'] === Constants::EIKEN_APPLIED_ERROR_CRYPTKEY ? '00' : $data['kyuAppliedStatus'];
            $messagePaymentKyu.= sprintf($this->translator->translate('geKyuPaymentMSG58'),$data['kyuAppliedStatus']);
            $messagePaymentKyu='<span style="border-radius: 5px;background-color: red; padding: 15px; color: #fff;display: inline-block;width:100%">'.$messagePaymentKyu.'</span>';
        }else if($data['doubleEiken'] == Constants::DOUBLE_EIKEN && count($kyuIdApplied) >= 1){
            $messagePaymentKyu.= $this->translator->translate('geKyuPaymentMSG56');
            if(count($data['kyuIdAppliedNonDS']) > 0){
              $messagePaymentKyu.= '<br /><span style="border-radius: 5px;background-color: rgb(0, 194, 244);padding: 15px; color: #fff;display: inline-block;width:100%">';
              foreach ($data['kyuIdAppliedNonDS'] as $kId){
                $messagePaymentKyu.= sprintf($this->translator->translate('geKyuPaymentMSG57'),$config['MappingLevel'][$kId]) . '<br />';
              }
              $messagePaymentKyu.= '<span>';
            }
        }
        else if (count($kyuIdApplied) >=2) {
          $messagePaymentKyu.= $this->translator->translate('geKyuPaymentMSG5');
            if(count($data['kyuIdAppliedNonDS']) > 0){
              $messagePaymentKyu.= '<br /><span style="border-radius: 5px;background-color: rgb(0, 194, 244);padding: 15px; color: #fff;display: inline-block;width:100%">';
              foreach ($data['kyuIdAppliedNonDS'] as $kId){
                $messagePaymentKyu.= sprintf($this->translator->translate('geKyuPaymentMSG57'),$config['MappingLevel'][$kId]) . '<br />';
              }
              $messagePaymentKyu.= '<span>';
            }
        }

        //check kyuIdAppliedNonDS not adjacent with kyus which teacher has selected
        $isAdjacent = count($data['kyuIdAppliedNonDS']) > 0 ? false : true;
        foreach ($data['kyuIdAppliedNonDS'] as $nonDSKyu){
            foreach ($data['availableKyus'] as $kyuId){
                if(abs(intval($nonDSKyu) - intval($kyuId)) === 1) $isAdjacent = true;
            }
            if ($isAdjacent) break;
        }
        if(!$isAdjacent){
            $messagePaymentKyu .= $this->translator->translate('geKyuPaymentMSG59');
            if(count($data['kyuIdAppliedNonDS']) > 0){
                $messagePaymentKyu .= '<br /><span style="border-radius: 5px;background-color: rgb(0, 194, 244);padding: 15px; color: #fff;display: inline-block;width:100%">';
                foreach ($data['kyuIdAppliedNonDS'] as $kId){
                    $messagePaymentKyu .= sprintf($this->translator->translate('geKyuPaymentMSG57'),$config['MappingLevel'][$kId]) . '<br />';
                }
                $messagePaymentKyu .= '<span>';
            }
        }
        
        if ($this->paymentEikenExam->checkCurrentDate($this->userIdentity['deadline'])) {
            $messagePaymentKyu = sprintf($translator->translate('eikenApplicationEndDateLt8'), $this->userIdentity['deadline']['year'], $this->userIdentity['deadline']['kai']);
        }

        $hadRegister = 0;
        if($applyPersional && !empty($applyPersional->getEikenId())){
            $hadRegister = 1;
        }
        return new ViewModel(array(
                                 'form'                  => $form,
                                 'data'                  => $data,
                                 'kyu'                   => $kyu,
                                 'kyu2'                  => $kyu2,
                                 'discountPriceValue'    => $isSemiDiscount,
                                 'applyEikenLevelId'     => $applyEikenLevelId,
                                 'messagePaymentKyu'     => $messagePaymentKyu,
                                 'doubleEikenNotSupport' => $doubleEikenNotSupport,
                                 'translate'             => $this->getTranslation(),
                                 'dataKyu'               => isset($dataKyu) ? $dataKyu : '',
                                 'isMobile'              => $this->isMobile,
                                 'error'                 => empty($error) ? '' : current($error),
                                 'halltypeInv'           => $data['hallType'],
                                 'isPayment'             => $isPayment,
                                 'isRegister'            => $isRegister,
                                 'hadRegister'           => $hadRegister,
                                 'availableKyus'           => $data['availableKyus'],
                                'kyuIdAppliedNonDS'     => $data['kyuIdAppliedNonDS'],
                                'kyuIdAppliedDS'     => $data['kyuIdAppliedDS'],
                                'mappingLevel'      => $config['MappingLevel']
                             ));
    }
    
    public function saveAction()
    {
        $data = array();
        $app = $this->getServiceLocator()->get('Application');
        $request = $app->getRequest();
        $em = $this->getEntityManager();
        $objOrg = $em->getRepository('Application\Entity\Organization')->findOneBy(array(
            'organizationNo' => $this->userIdentity['organizationNo']
        ));
        if(empty($objOrg)){
            $data = array('status'=>0);
        }else{
            $orgId = $objOrg->getId();
            if($request->isPost()){
                $data = $this->eikenService->savePersonalInfo($orgId, $this->userIdentity['eikenScheduleId'], $this->userIdentity);
            }
        }
        $user = $this->userIdentity;
        $user['paymentInformation'] = $this->paymentEikenExam->paymentInformationStatus($this->userIdentity['pupilId'], $this->userIdentity['eikenScheduleId']);
        $this->privateSession->setData(Constants::SESSION_SATELLITE, $user);
        if ($data['status'] == false){ 
            return $this->getResponse()->setContent(json_encode($data));
        }
        
        $data = $this->eikenService->createResponseMessage($data);
        
        $user['kyuIdAppliedDS'] = array_merge($user['kyuIdAppliedDS'],$data['chooseKyu']);
        $user['availableKyus'] = $this->satelliteService->setAvailableKyu(array_merge($user['kyuIdAppliedDS'],$user['kyuIdAppliedNonDS']), json_decode($user['listEikenLevel']),$user['doubleEiken']);
        $this->privateSession->setData(Constants::SESSION_SATELLITE, $user);
        return $this->getResponse()->setContent(json_encode($data));
    }

    private static function gengo($value)
    {
        // tinh nam shouwa ex: value - 1925
        if ($value >= 1925 && $value <= 1989) {
            $no = $value - 1925;

            return $value . "(昭和 " . $no . ")";
        }
        // tinh nam heisei
        if ($value >= 1989) {
            $no = $value - 1988;

            return $value . "(平成 " . $no . ")";
        }

        return $value;
    }

    private function getListKyuPriceFromData($data, $kyuData){
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        foreach($data['chooseKyu'] as $kyu){
            $returnData[$kyu]['price'] = $kyuData[$data['hallType'.$kyu]][$kyu]['price'];
            $returnData[$kyu]['priceName'] = sprintf($translator->translate('priceName'),number_format($kyuData[$data['hallType'.$kyu]][$kyu]['price']));
            $returnData[$kyu]['examDate'] = $kyuData[$data['hallType'.$kyu]][$kyu]['examDate'];
            $returnData[$kyu]['examDate2Round'] = $kyuData[$data['hallType'.$kyu]][$kyu]['examDate2Round'];
            $returnData[$kyu]['name'] = $kyuData[$data['hallType'.$kyu]][$kyu]['name'];
            $returnData[$kyu]['hallType'] = $data['hallType'.$kyu];
        }
        return $returnData;
    }
    
    public function getTranslation()
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $messages = array(
            'MSG1'  => $translator->translate('MSG1'),
            'MSG2'  => $translator->translate('MSG2'),
            'MSG3'  => $translator->translate('MSG3'),
            'MSG4'  => $translator->translate('MSG4'),
            'MSG5'  => $translator->translate('MSG5'),
            'MSG6'  => $translator->translate('MSG6'),
            'MSG22' => $translator->translate('MSG22'),
            'MSG23b' => $translator->translate('MSG23b'),
            'MSG27' => $translator->translate('MSG27'),
            'MSG42' => $translator->translate('MSG42'),
            'SGHMSG48' => $translator->translate('SGHMSG48'),
            'geKyuPaymentMSG5' => $translator->translate('geKyuPaymentMSG5'),
            'geKyuPaymentMSG56' => $translator->translate('geKyuPaymentMSG56'),
            'MsgRequired3' => $translator->translate('MsgRequired3'),
            'MsgRequired4' => $translator->translate('MsgRequired4'),
            'InvalidBirthday' => $translator->translate('InvalidBirthday'),
            'MsgInvalidEmail' => $translator->translate('MsgInvalidEmail'),
            'MsgRequiredNumber' => $translator->translate('MsgRequiredNumber'),
            'studentHadChangeHallType' => $translator->translate('studentHadChangeHallType'),
            'input20FullSizeNameKanji' => $translator->translate('input20FullSizeNameKanji'),
            'R4_MSG28' => $translator->translate('R4_MSG28'),
            'R4_MSG32' => $translator->translate('R4_MSG32'),
            'MSG_PAYMENT' => $translator->translate('MSG_PAYMENT'),                
            'MSG_APPLY_SUCCESS' => $translator->translate('MSG_APPLY_SUCCESS'),                
            'PAY_NOW' => $translator->translate('PAY_NOW'),                
            'payByCombini' => $translator->translate('payByCombini'),                
            'PAY_LATER' => $translator->translate('PAY_LATER'), 
            'msgCheckStringKana' => $translator->translate('msgCheckStringKana'), 
            'msgInput11DigitOfPhoneNumber' => $translator->translate('msgInput11DigitOfPhoneNumber'),
            'ZipCode_Not_Found' => $translator->translate('ZipCode_Not_Found'),
            'msgDisplayReceiptNo' => $translator->translate('msgDisplayReceiptNo'),
            'msgWaitReceiptNo' => $translator->translate('msgWaitReceiptNo'),
            'msgShowPupilInfo' => $translator->translate('msgShowPupilInfo'),
            'msgShowPupilInfoLv1' => $translator->translate('msgShowPupilInfoLv1')
        );
        return json_encode($messages);
    }
    
    public function zipcodeAction(){
        $contentData = json_decode($this->request->getContent());
        $addressInfo = $this->dantaiService->zipcode2Address($contentData->zipcode);
        $jsonModel = \Dantai\Utility\JsonModelHelper::getInstance();
        if(empty($addressInfo)){
            $jsonModel->setFail();
            $jsonModel->addMessage($this->getServiceLocator()->get('MvcTranslator')->translate('ZipCode_Not_Found'));
        }else{
            $jsonModel->setSuccess();
            $jsonModel->setData($addressInfo);
        }
        return new \Zend\View\Model\JsonModel($jsonModel->toArray());
    }

    public function getHallTypeAction() {
        $orgNo = $this->userIdentity['organizationNo'];
        $eikenScheduleId = $this->userIdentity['eikenScheduleId'];
        $em = $this->getEntityManager();
        $objOrg = $em->getRepository('Application\Entity\Organization')->findOneBy(array(
            'organizationNo' => $orgNo
        ));
        if(empty($objOrg)){
            return array('status'=>0);
        }
        $orgId = $objOrg->getId();
        $objHallType = $em->getRepository('Application\Entity\InvitationSetting')->findOneBy(array(
            'organizationId' => $orgId,
            'eikenScheduleId' => $eikenScheduleId
        ));
        if(empty($objHallType)){
            return array('status'=>0);
        }
        $hallType = $objHallType->getTempHallType();
        return $this->response->setContent(json_encode(array(
            'status'=>1,
            'data'=>$hallType
        )));
    }
    
    public function loadMainHallAction()
    {
        $cityId = $this->params()->fromPost('cityId', 0);
        $eikenLevelId = $this->params()->fromPost('eikenLevelId', 0);
        $isFirstTime = $this->params()->fromPost('isFirstTime', 0) == 1? true : false;
        $view = new ViewModel();
        $view->setTerminal(true);
        $view->setVariable('mainHallAddresses', $this->eikenService->loadMainHall($cityId, $eikenLevelId, $isFirstTime));
        return $view;
    }
    
    public function loadDistrictInCityAction()
    {
        $contentData = json_decode($this->request->getContent());
        $cityId = !empty($contentData->cityId) ? $contentData->cityId : 0;
        $data = $this->getEntityManager()->getRepository('Application\Entity\District')->getListDistrictInCity($cityId);
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $response->setContent(json_encode($data));

        return $response;    
    }

    public function cancelApplyEikenAction()
    {
        $translator = $this->getServiceLocator()->get('MVCTranslator');
        $jsonModel = \Dantai\Utility\JsonModelHelper::getInstance();
        if ($this->paymentEikenExam->checkCurrentDate($this->userIdentity['deadline'])) {
            $jsonModel->setFail();
            $jsonModel->setData(array(
                'status' => Constants::RESPONSE_NOT_ALLOWED_DELETE,
                'message' => sprintf($translator->translate('eikenApplicationEndDateLt8'), $this->userIdentity['deadline']['year'], $this->userIdentity['deadline']['kai'])
            ));
            return new \Zend\View\Model\JsonModel($jsonModel->toArray());
        }
        
        $contentData = json_decode($this->request->getContent());
        $applyEikenLevelId = $contentData->applyEikenLevelId;
        $applyEikenLevel = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenLevel')->findOneBy(array('id' => $applyEikenLevelId));
        $data = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenLevel')->deleteApplyEiken($applyEikenLevelId);
        if(!$data){
            $jsonModel->setFail();
            $jsonModel->setData(array(
                'status' => Constants::RESPONSE_DELETE_FALSE,
                'message' => $translator->translate('SystemError')
            ));
        }else{
            $jsonModel->setSuccess();
            $jsonModel->setData(array(
                'status' => Constants::RESPONSE_DELETE_SUCCESS,
                'message' => $translator->translate('msgAlertAfterDeleteSuccessfull')
            ));
            $user = $this->userIdentity;
            
            $kyuRemoved = array();
            if(!empty($applyEikenLevel->getEikenLevelId())){
              array_push($kyuRemoved, $applyEikenLevel->getEikenLevelId());
            }
            $user['kyuIdAppliedDS'] = array_diff($user['kyuIdAppliedDS'],$kyuRemoved);
            $user['availableKyus'] = $this->satelliteService->setAvailableKyu(array_merge($user['kyuIdAppliedDS'],$user['kyuIdAppliedNonDS']), json_decode($user['listEikenLevel']),$user['doubleEiken']);
            $user['paymentInformation'] = $this->paymentEikenExam->paymentInformationStatus($this->userIdentity['pupilId'], $this->userIdentity['eikenScheduleId']);
            $this->privateSession->setData(Constants::SESSION_SATELLITE, $user);
        }
        return new \Zend\View\Model\JsonModel($jsonModel->toArray());
    }

    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }
}