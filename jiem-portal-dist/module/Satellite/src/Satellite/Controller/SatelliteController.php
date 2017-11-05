<?php
namespace Satellite\Controller;

use Application\Entity\AuthenticationKey;
use Application\Entity\EikenSchedule;
use Satellite\Constants;
use Satellite\Form\LoginForm;
use Zend\View\Model\ViewModel;
use Dantai\PrivateSession;
use Dantai;
use Doctrine\ORM\Query;
use Satellite\Service\PaymentEikenExamService;
use Application\Service\DantaiService;

class SatelliteController extends BaseController
{

    private $identity;

    private $translator;

    protected $em;

    protected $errorMsg;

    protected $paymentEikenExam;
    
    protected $userIdentity;
    
    protected $privateSession;
    
    protected $dantaiService;
    
    protected $satelliteService;


    public function onDispatch(\Zend\Mvc\MvcEvent $e)
    {
        $this->translator = $this->getServiceLocator()->get('MVCTranslator');
        $this->identity = $this->getEntityAuthenticateManager()->getIdentity();
        $this->paymentEikenExam = new PaymentEikenExamService($this->getServiceLocator());
        $this->em = $this->getEntityManager();
        $this->privateSession = new PrivateSession();
        $this->dantaiService = new DantaiService();
        $this->dantaiService->setServiceLocator($this->getServiceLocator());
        $routeMatch = $e->getRouteMatch();
        $action = $routeMatch->getParam('action');
        $this->userIdentity = $this->privateSession->getData(Constants::SESSION_SATELLITE);
        $this->satelliteService = new \Satellite\Service\SatelliteService();
        $this->satelliteService->setServiceLocator($this->getServiceLocator());

        if ($this->identity != null && $action == 'index') {
            $this->redirectByRole($this->identity);
        }
        return parent::onDispatch($e);
    }

    public function accessDeniedAction()
    {
        $viewModel = new ViewModel();

        return $viewModel;
    }

    public function inactivatedAction()
    {
        $viewModel = new ViewModel();

        return $viewModel;
    }

    public function indexmobileAction()
    {
        $viewModel = new ViewModel();
        $this->layout('layout/mobile');
        $viewModel->setTemplate("/satellite/index-mobile.phtml");

        return $viewModel;
    }

    public function indexAction()
    {
        $config = $this->getServiceLocator()->get('config');
        //clear session credit card
        $paymentByCreditFlag = $this->layout()->getVariable('paymentByCreditFlag');
        $paymentInfomationStatus = $this->layout()->getVariable('paymentInfomationStatus');
        if ($paymentByCreditFlag) {
            $this->privateSession->clear(Constants::LIST_KYU_PRICE);
            $this->flashMessenger()->clearCurrentMessages();
        }
        $paymentMessage = '';
        $user = PrivateSession::getData(Constants::SESSION_SATELLITE);
        PrivateSession::clear(Constants::SESSION_APPLYEIKEN);
        PrivateSession::clear(Constants::DATA_TEST_SITE_EXEMPTION);

        $kyuIdApplied = array_merge($user['kyuIdAppliedNonDS'], $user['kyuIdAppliedDS']);
        sort($kyuIdApplied);
        if($user['kyuAppliedStatus'] !== Constants::EIKEN_APPLIED_SUCCESS){
            // convert error code '0' -> '00'
            $user['kyuAppliedStatus'] = $user['kyuAppliedStatus'] === Constants::EIKEN_APPLIED_ERROR_CRYPTKEY ? '00' : $user['kyuAppliedStatus'];
            $paymentMessage .= sprintf($this->translator->translate('geKyuPaymentMSG58'),$user['kyuAppliedStatus']);
            $paymentMessage ='<span style="border-radius: 5px;background-color: red; padding: 15px; color: #fff;display: inline-block;width:100%">'.$paymentMessage.'</span>';
        }else if($user['doubleEiken'] == Constants::DOUBLE_EIKEN && count($kyuIdApplied) >= 1){
            $paymentMessage .= $this->translator->translate('geKyuPaymentMSG56');
            if(count($user['kyuIdAppliedNonDS']) > 0){
              $paymentMessage .= '<br /><span style="border-radius: 5px;background-color: rgb(0, 194, 244);padding: 15px; color: #fff;display: inline-block;width:100%">';
              foreach ($user['kyuIdAppliedNonDS'] as $kId){
                $paymentMessage .= sprintf($this->translator->translate('geKyuPaymentMSG57'),$config['MappingLevel'][$kId]) . '<br />';
              }
              $paymentMessage .= '<span>';
            }
        }
        else if (count($kyuIdApplied) >=2) {
          $paymentMessage .= $this->translator->translate('geKyuPaymentMSG5');
            if(count($user['kyuIdAppliedNonDS']) > 0){
              $paymentMessage .= '<br /><span style="border-radius: 5px;background-color: rgb(0, 194, 244);padding: 15px; color: #fff;display: inline-block;width:100%">';
              foreach ($user['kyuIdAppliedNonDS'] as $kId){
                $paymentMessage .= sprintf($this->translator->translate('geKyuPaymentMSG57'),$config['MappingLevel'][$kId]) . '<br />';
              }
              $paymentMessage .= '<span>';
            }
        }

        //check kyuIdAppliedNonDS not adjacent with kyus which teacher has selected
        $isAdjacent = count($user['kyuIdAppliedNonDS']) > 0 ? false : true;
        foreach ($user['kyuIdAppliedNonDS'] as $nonDSKyu){
            foreach ($user['availableKyus'] as $kyuId){
                if(abs(intval($nonDSKyu) - intval($kyuId)) === 1) $isAdjacent = true;
            }
            if ($isAdjacent) break;
        }
        if(!$isAdjacent){
            $paymentMessage = $this->translator->translate('geKyuPaymentMSG59');
            if(count($user['kyuIdAppliedNonDS']) > 0){
                $paymentMessage .= '<br /><span style="border-radius: 5px;background-color: rgb(0, 194, 244);padding: 15px; color: #fff;display: inline-block;width:100%">';
                foreach ($user['kyuIdAppliedNonDS'] as $kId){
                    $paymentMessage .= sprintf($this->translator->translate('geKyuPaymentMSG57'),$config['MappingLevel'][$kId]) . '<br />';
                }
                $paymentMessage .= '<span>';
            }
        }

        if($this->paymentEikenExam->checkCurrentDate($user['deadline'])){
            $paymentMessage = sprintf($this->translator->translate('eikenApplicationEndDateLt8'),$user['deadline']['year'],$user['deadline']['kai']);
        }
        $viewModel = new ViewModel();
        $this->layout('layout/' . ($this->isMobile ? 'mobile' : 'layout'));
        $viewModel->setTemplate('/satellite/' . ($this->isMobile ? 'mindex' : 'pindex') . '.phtml');
        $viewModel->setVariables(array(
            'paymentByCreditFlag' => $paymentByCreditFlag, 
            'paymentInfomationStatus' => $paymentInfomationStatus, 
            'paymentMessage' => $paymentMessage,
            'eikenApplied' => count($kyuIdApplied))
        );

        return $viewModel;
    }
    
    public function loginAction()
    {
        $viewModel = new ViewModel();
        $form = new LoginForm();
        $request = $this->getRequest();
        $error = array();
        if ($request->isPost()) {
            $parameters = $request->getPost();
            $parameters["organizationNo"] = trim($parameters["organizationNo"]);
            $parameters["authenKey"] = trim($parameters["authenKey"]);
            /*@var $user \Application\Entity\AuthenticationKey */
            $user = $this->authenLogin($parameters);
            if(!empty($user)){
                $pupilId = $user->getPupilId();
                $pupilObje = $this->em->getRepository('Application\Entity\Pupil')->findOneBy(
                                array(
                                    'id'      => $pupilId,
                                    'isDelete'       => 0
                                )
                            );
                if(empty($pupilObje)){
                     $errorMsg = 'MSG23';
                }
            }
            if (empty($this->errorMsg) && !isset($errorMsg)) {
                $errorMsg = 'MSG23';
                if ($user && !$this->userIdentity($user)) {
                    $errorMsg = 'MSG69';
                }
                // update last sessionId
                $this->satelliteService->updateSessionId($parameters["authenKey"], $parameters["organizationNo"], session_id());
            }
            $error = $this->errorMsg;
        }
        if (isset($errorMsg)) {
            $error['error'] = isset($errorMsg) ? $errorMsg : '';
        }

        $viewModel->setVariables(array(
            'form'   => $form,
            'errors' => $error
        ));
        $this->layout("layout/" . ($this->isMobile ? 'mlayoutlogin' : 'layoutlogin'));
        $viewModel->setTemplate("/satellite/" . ($this->isMobile ? 'mlogin.phtml' : 'plogin.phtml'));

        return $viewModel;
    }

    private function userIdentity($user)
    {
        if (!$user) {
            return false;
        }
        /*@var $invSetting \Application\Entity\InvitationSetting */
        $invSetting = $this->em->getRepository('Application\Entity\InvitationSetting')->findOneBy(array(
            'eikenScheduleId' => $user->getEikenScheduleId(),
            'organizationId'  => $user->getPupil()->getOrganizationId(),
            'isDelete'        => 0));       
        if ($invSetting) {
            $deadline = $this->getDeadline($invSetting->getEikenScheduleId());
            $dt = !empty($user->getExpireDate()) ? $user->getExpireDate()->format('Y-m-d') : $this->getTimeDeadlineByEikenSchedule($deadline);
            if ($dt <= date('Y-m-d')) {
                return false;
            }
            $semiMainVenue = $this->dantaiService->getSemiMainVenue(
                $invSetting->getOrganizationId(),
                $invSetting->getEikenScheduleId()
            );
            
            $eikenSchedule = $this->em->getRepository('Application\Entity\EikenSchedule')->find($invSetting->getEikenScheduleId());
            if(!$eikenSchedule){
                return false;
            }
                        
            $applyEikenPersonalInfo = $this->em->getRepository('Application\Entity\ApplyEikenPersonalInfo')->findOneBy(array(
                  'pupilId' => $user->getPupilId(),
                  'isDelete'        => 0
                ), array('eikenScheduleId' => 'DESC'));
            if(!$applyEikenPersonalInfo || empty($applyEikenPersonalInfo->getEikenId())){              
              $result = 'noEiken';
            }else {
              $eikenid = $applyEikenPersonalInfo->getEikenId();
              $nendo = $eikenSchedule->getYear();
              $kai = $eikenSchedule->getKai();
              $result = $this->satelliteService->getEikenAppliedFromUketuke($eikenid,$nendo,$kai);
            }
            
            
            
            // list kyuId that create from NON Dantai Support
            $kyuIdAppliedNonDS = array();
            $kyuAppliedStatus = Constants::EIKEN_APPLIED_SUCCESS;
            
            if($result == 'noEiken'){
                $kyuAppliedStatus = Constants::EIKEN_APPLIED_SUCCESS;
            }else if(property_exists($result, 'eikenArray') && is_array($result->eikenArray)){
                foreach ($result->eikenArray as $v){
                    if(isset($v->kekka) && intval($v->kekka) == Constants::EIKEN_APPLIED_SUCCESS && isset($v->kyucd) && isset($v->siteflg)){
                        if(intval($v->siteflg) !== 3){
                            array_push($kyuIdAppliedNonDS, intval($v->kyucd));
                        }
                    }else if(isset($v->kekka) && intval($v->kekka) == Constants::EIKEN_APPLIED_SUCCESS){
                          continue;
                    }else if(isset($v->kekka) && intval($v->kekka) === Constants::EIKEN_APPLIED_ERROR_CRYPTKEY){
                        $kyuAppliedStatus = Constants::EIKEN_APPLIED_ERROR_CRYPTKEY;
                        break;
                    }else{
                        $kyuAppliedStatus = Constants::EIKEN_APPLIED_ERROR;
                        break;
                    }
                }
            }else{
                $kyuAppliedStatus = Constants::EIKEN_APPLIED_ERROR; 
            }
            
            // list kyuId that create from Dantai Support
            $kyuIdAppliedDS = array();
            $listIsRegister = $this->getEntityManager()->getRepository('Application\Entity\ApplyEikenLevel')->findBy(array('pupilId' => $user->getPupilId(), 'eikenScheduleId' => $user->getEikenScheduleId(), 'isDelete' => 0), array('eikenLevelId' => 'ASC'));
            if (!empty($listIsRegister)) {
                foreach ($listIsRegister as $v) {
                    array_push($kyuIdAppliedDS, $v->getEikenLevelId());
                }
            }
            $userIdentity = array(
                'organizationNo'      => $user->getOrganizationNo(),
                'organizationId'      => $user->getPupil()->getOrganizationId(),
                'paymentType'         => $invSetting->getTempPaymentType(),
                'personalPayment'     => $invSetting->getTempPersonalPayment(),
                'hallType'            => $invSetting->getTempHallType(),
                'organizationPayment' => $invSetting->getTempOrganizationPayment(),
                'beneficiary'         => $semiMainVenue ? $invSetting->getTempBeneficiary() : null,
                'listEikenLevel'      => $invSetting->getListEikenLevel(),
                'eikenScheduleId'     => $invSetting->getEikenScheduleId(),
                'hallTypeExamDay'     => $invSetting->getExamDay(),
                'pupilId'             => $user->getPupilId(),
                'deadline'            => $deadline,
                'doubleEiken'         => $invSetting->getDoubleEikenMessagesId(),
                'paymentInformation'  => $this->paymentEikenExam->paymentInformationStatus($user->getPupilId(), $invSetting->getEikenScheduleId()),
                'firstNameKanji'      => $user->getPupil()->getFirstNameKanji(),
                'lastNameKanji'      => $user->getPupil()->getLastNameKanji(),
                'kyuIdAppliedDS'          => $kyuIdAppliedDS,
                'kyuAppliedStatus'     => $kyuAppliedStatus,
                'kyuIdAppliedNonDS'     => $kyuIdAppliedNonDS
                
            );
            
            $kyuIdApplied = array_merge($kyuIdAppliedNonDS,$kyuIdAppliedDS);            
            $userIdentity['availableKyus'] = $this->satelliteService->setAvailableKyu($kyuIdApplied, json_decode($userIdentity['listEikenLevel']),$userIdentity['doubleEiken']);;
            PrivateSession::setData(Constants::SESSION_SATELLITE, $userIdentity);

            return $this->redirect()->toRoute('satellite');
        }

        return false;
    }

    function getDeadline($eikenScheduleId)
    {
        /** @var EikenSchedule $eikenScheduleId */
        $eikenScheduleId = $this->em->getRepository('Application\Entity\EikenSchedule')->find($eikenScheduleId);

        if ($eikenScheduleId) {
            return array(
                'id' => $eikenScheduleId->getId(),
                'kai' => $eikenScheduleId->getKai(),
                'year' => $eikenScheduleId->getYear(),
                'deadlineForm' => $eikenScheduleId->getDeadlineFrom(),
                'deadlineTo' => $eikenScheduleId->getSatelliteSiteDeadline(),
                'combiniDeadline' => $eikenScheduleId->getCombiniDeadline(),
                'creditDeadline' => $eikenScheduleId->getCreditCardDeadline(),
            );
        }

        return false;
    }
    
    public function getTimeDeadlineByEikenSchedule($deadline){
        /* @var $eikenSchedule \Application\Entity\EikenSchedule */
        if(!$deadline){
            return Null;
        }
        $kai = $deadline['kai'] + 1;
        $year = $deadline['year'];
        
        if($deadline['kai'] == 3){
            $kai = 1;
            $year = $year + 1;
        }
        /* @var $eikenScheduleNext \Application\Entity\EikenSchedule */
        $eikenScheduleNext = $this->em->getRepository('Application\Entity\EikenSchedule')->findOneBy(array(
            'year' => $year,
            'kai' => $kai
        ));
        if(!$eikenScheduleNext){
            return Null;
        }

        return $eikenScheduleNext->getDeadlineFrom() !== Null ? $eikenScheduleNext->getDeadlineFrom()->format('Y-m-d') : Null;
    }

    private function authenLogin($parameters)
    {
        if (empty($parameters['organizationNo'])) {
            $this->errorMsg['organizationNo'] = 'MSG1';
        }
        if (empty($parameters['authenKey'])) {
            $this->errorMsg['authenKey'] = 'MSG1';
        }

        return $this->em->getRepository('Application\Entity\AuthenticationKey')->findOneBy(
            array(
                'authenKey'      => $parameters["authenKey"],
                'organizationNo' => hexdec($parameters["organizationNo"]),
                'isDelete'       => 0
            )
        );
    }

    public function commercialLawAction()
    {
        $viewModel = new ViewModel();
        $this->layout('layout/' . ($this->isMobile ? 'mobile' : 'layout'));
        $viewModel->setTemplate('/satellite/commercial-law.phtml');
        $viewModel->setVariable('isMobile',$this->isMobile);

        return $viewModel;
    }

    public function logoutAction()
    {
        PrivateSession::clear();

        return $this->redirect()->toRoute('login');
    }

    /**
     * @return ViewModel
     */
    public function userManualAction()
    {
        $viewModel = new ViewModel();
        $this->layout('layout/' . ($this->isMobile ? 'mobile' : 'layout'));
//        $viewModel->setTemplate('/satellite/commercial-law.phtml');
        $viewModel->setVariable('isMobile',$this->isMobile);
        return $viewModel;
    }

    public function getEntityManager()
    {
        return $this->getServiceLocator()->get('doctrine.entitymanager.orm_default');
    }

    public function getEntityAuthenticateManager()
    {
        return $this->getServiceLocator()->get('doctrine.authenticationservice.orm_default');
    }
}